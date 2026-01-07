<?php
session_start();
require_once('../config/connect_db.php'); // koneksi MySQL

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$tahun    = $_POST['tahun'] ?? '';

if (empty($username) || empty($password) || empty($tahun)) {
    echo json_encode([
        "status" => "error",
        "message" => "Username, password, dan tahun wajib diisi"
    ]);
    exit;
}

// ==========================
// Validasi tahun (aman)
// ==========================
$allowedYears = ['2024', '2025', '2026'];
if (!in_array($tahun, $allowedYears)) {
    echo json_encode([
        "status" => "invalid_year"
    ]);
    exit;
}

// Ambil data user berdasarkan username
$query = "SELECT * FROM `users` WHERE username = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Query error: " . $mysqli->error
    ]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "not_found"]);
    exit;
}

$user = $result->fetch_assoc();

// Verifikasi password (tetap SHA1, tidak diubah)
if ($user['password'] !== sha1($password)) {
    echo json_encode(["status" => "wrong_password"]);
    exit;
}

// ==========================
// SESSION
// ==========================
$_SESSION['user'] = $user['username'];

// ==========================
// COOKIES (1 jam)
// ==========================
$expire = time() + 3600;

setcookie("id", $user['id'], $expire, "/");
setcookie("username", $user['username'], $expire, "/");
setcookie("email", $user['email'], $expire, "/");
setcookie("fullname", $user['fullname'], $expire, "/");
setcookie("aksesgudang", $user['akses_gudang'], $expire, "/");
setcookie("userrolecode", $user['user_role_code'], $expire, "/");

// ==========================
// COOKIE TAHUN (BARU)
// ==========================
setcookie("tahun_data", $tahun, $expire, "/");

// (Opsional tapi sangat berguna untuk ODBC)
switch ($tahun) {
    case '2024': $db_active = 'EMBdb001'; break;
    case '2025': $db_active = 'EMBdb002'; break;
    case '2026': $db_active = 'EMBdb003'; break;
}
setcookie("db_active", $db_active, $expire, "/");

// ==========================
// AKSES MERK
// ==========================
$aksesmerk_db = trim($user['akses_merk']);
$aksesmerk = ($aksesmerk_db === "" || $aksesmerk_db === null)
    ? "ALL MERK"
    : $aksesmerk_db;

setcookie("aksesmerk", $aksesmerk, $expire, "/");

// ==========================
// LOG LOGIN
// ==========================
$logQuery = "
    INSERT INTO sys_login_emba_report
    (user_login, fullname, status, logintime)
    VALUES (?, ?, 1, NOW())
";
$logStmt = $mysqli->prepare($logQuery);

if ($logStmt) {
    $logStmt->bind_param("ss", $user['username'], $user['fullname']);
    $logStmt->execute();
    $logStmt->close();
}

$mysqli->close();

// ==========================
// RESPONSE
// ==========================
echo json_encode(["status" => "success"]);
