<?php
// ======================================
// KONEKSI SQL SERVER VIA ODBC
// ======================================
$dsn      = "dbmaserp";   // DSN ODBC (tanpa lock database)
$username = "db";
$password = "3mb4Sejati";

// Buka koneksi ODBC
$conn = odbc_connect($dsn, $username, $password);

if (!$conn) {
    die("Koneksi ODBC gagal: " . odbc_errormsg());
}

// ======================================
// PILIH DATABASE BERDASARKAN COOKIE TAHUN
// ======================================

// Prioritas:
// 1. db_active (hasil mapping login)
// 2. tahun_data
// 3. default database terbaru

if (!empty($_COOKIE['db_active'])) {

    $database = $_COOKIE['db_active'];

} elseif (!empty($_COOKIE['tahun_data'])) {

    switch ($_COOKIE['tahun_data']) {
        case '2024':
            $database = 'EMBdb001';
            break;
        case '2025':
            $database = 'EMB0db02';
            break;
        case '2026':
            $database = 'EMBdb003';
            break;
        default:
            $database = 'EMBdb003';
    }

} else {

    // Default jika user belum login
    $database = 'EMBdb003';
}

// ======================================
// AKTIFKAN DATABASE
// ======================================
$result = odbc_exec($conn, "USE {$database}");

if (!$result) {
    die("Gagal memilih database {$database}: " . odbc_errormsg($conn));
}

if (!$conn) {
    die("Koneksi gagal: " . odbc_errormsg());
}
//============================================================================================================================
//konek ke db mysql
$mysqli = new mysqli("localhost", "root", "", "db_retail_unity");
if ($mysqli->connect_error) {
    die("Koneksi MySQL gagal: " . $mysqli->connect_error);
}

// Koneksi ke database MySQL
$mysqli = new mysqli("localhost", "root", "", "db_powerone");

// Cek koneksi
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit;
}