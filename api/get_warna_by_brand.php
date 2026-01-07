<?php
require_once('../config/connect_db.php'); // koneksi MySQLi kamu

$brand = $_POST['brand'] ?? '';

if (!$brand) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT kode, warna FROM m_warna WHERE brand = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $brand);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['kode'],   // atau gabungan 'kode - bahan' jika mau
        'text' => $row['kode'] . ' - ' .$row['warna']
    ];
}

echo json_encode($data);
