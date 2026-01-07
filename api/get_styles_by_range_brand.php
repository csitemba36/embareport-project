<?php
require_once('../config/connect_db.php'); // koneksi MySQLi kamu

$brand = $_POST['brand'] ?? '';
$kodeRanges = $_POST['kode_ranges'] ?? [];

if (!$brand || empty($kodeRanges)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($kodeRanges), '?'));

$sql = "SELECT DISTINCT kode, style FROM m_style WHERE brand = ? AND kode_range IN ($placeholders)";
$stmt = $mysqli->prepare($sql);

$params = array_merge([$brand], $kodeRanges);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['kode'],
        'text' => $row['kode'] . ' - ' .$row ['style'] 
    ];
}

echo json_encode($data);
