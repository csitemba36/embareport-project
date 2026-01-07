<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php');

if (!$conn) {
    echo json_encode(["error" => "Koneksi gagal: " . odbc_errormsg()]);
    exit;
}

// Ambil parameter pencarian
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

// Ambil nilai cookie allkodemerk
$allowedMerk = isset($_COOKIE['allkodemerk']) ? $_COOKIE['allkodemerk'] : '';
$allowedMerk = trim($allowedMerk);

// Siapkan base SQL dan parameter
$baseSql = "SELECT KodeDept, NamaDept FROM Departments";
$conditions = [];
$params = [];

// Tambahkan filter berdasarkan allkodemerk
if (!empty($allowedMerk) && strtoupper($allowedMerk) !== 'ALL') {
    $merkList = array_filter(array_map('trim', explode(';', $allowedMerk)));

    // Buat placeholders (?, ?, ?, ...)
    $placeholders = implode(',', array_fill(0, count($merkList), '?'));
    $conditions[] = "KodeDept IN ($placeholders)";
    $params = array_merge($params, $merkList);
}

// Tambahkan filter search jika ada
if ($searchTerm !== '') {
    $conditions[] = "NamaDept LIKE ?";
    $params[] = "%$searchTerm%";
}

// Gabungkan query akhir
if (!empty($conditions)) {
    $baseSql .= " WHERE " . implode(" AND ", $conditions);
}

$baseSql .= " ORDER BY NamaDept ASC";

// Jalankan query
$stmt = odbc_prepare($conn, $baseSql);
$result = odbc_execute($stmt, $params);

// Ambil data
$data = [];
if ($result) {
    while ($row = odbc_fetch_array($stmt)) {
        $data[] = [
            "id" => $row['KodeDept'],
            "text" => $row['KodeDept'] . ' - ' . $row['NamaDept']
        ];
    }
}

// Tutup koneksi
odbc_close($conn);

// Output JSON
echo json_encode($data);
