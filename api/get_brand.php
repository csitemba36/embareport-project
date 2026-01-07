<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php');

if (!$conn) {
    echo json_encode(["error" => "Koneksi gagal: " . odbc_errormsg()]);
    exit;
}

// Ambil parameter pencarian dari Select2
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

// Ambil nilai cookie allkodemerk
$allowedMerk = isset($_COOKIE['aksesmerk']) ? trim($_COOKIE['aksesmerk']) : '';

// Inisialisasi query dan parameter
$baseSql = "SELECT Kode, Nama FROM DepartmentBrands";
$conditions = [];
$params = [];

// Filter berdasarkan allkodemerk jika bukan "ALL MERK"
if (!empty($allowedMerk) && strtoupper($allowedMerk) !== 'ALL MERK') {
    $merkList = array_filter(array_map('trim', explode(';', $allowedMerk)));

    if (!empty($merkList)) {
        $placeholders = implode(',', array_fill(0, count($merkList), '?'));
        $conditions[] = "Kode IN ($placeholders)";
        $params = array_merge($params, $merkList);
    }
}

// Tambahkan filter pencarian (LIKE)
if ($searchTerm !== '') {
    $conditions[] = "Nama LIKE ?";
    $params[] = "%$searchTerm%";
}

// Gabungkan kondisi jika ada
if (!empty($conditions)) {
    $baseSql .= " WHERE " . implode(" AND ", $conditions);
}

$baseSql .= " ORDER BY Nama ASC";

// Jalankan query dengan ODBC
$stmt = odbc_prepare($conn, $baseSql);
$result = odbc_execute($stmt, $params);

// Ambil dan susun hasil data
$data = [];


if ($result) {
    while ($row = odbc_fetch_array($stmt)) {
        $data[] = [
            "id" => $row['Kode'],
            "text" => $row['Kode'] . ' - ' . $row['Nama']
        ];
    }
}

odbc_close($conn);

// Keluarkan dalam format JSON
echo json_encode($data);

file_put_contents("debug.txt", "Cookie allkodemerk: " . $allowedMerk . PHP_EOL);
 