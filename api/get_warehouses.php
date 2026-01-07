<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php');

if (!$conn) {
    echo json_encode(["error" => "Koneksi gagal: " . odbc_errormsg()]);
    exit;
}

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

// === Base SQL ===
$sql = "SELECT KodeGudang, NamaGudang FROM Warehouses WHERE 1=1 ";

// === Filter gudang dari cookie (jika ada) ===
$params = [];
if (!empty($aksesGudangList)) {
    $placeholders = implode(",", array_fill(0, count($aksesGudangList), "?"));
    $sql .= " AND KodeGudang IN ($placeholders)";
    $params = array_merge($params, $aksesGudangList);
}

// === Filter pencarian ===
if ($searchTerm !== '') {
    $sql .= " AND (KodeGudang LIKE ? OR NamaGudang LIKE ?)";
    $like = "%$searchTerm%";
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY NamaGudang ASC";

$stmt = odbc_prepare($conn, $sql);
$result = odbc_execute($stmt, $params);

$data = [];

// Tambahkan opsi "SEMUA GUDANG/TOKO" di awal
$data[] = [
    "id" => "ALL",
    "text" => "SEMUA GUDANG / TOKO"
];

if ($result) {
    while ($row = odbc_fetch_array($stmt)) {
        $data[] = [
            "id" => $row['KodeGudang'],
            "text" => $row['KodeGudang'] . ' - ' . $row['NamaGudang']
        ];
    }
}

odbc_close($conn);
echo json_encode($data);
