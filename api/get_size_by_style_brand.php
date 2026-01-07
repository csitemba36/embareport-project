<?php
require_once('../config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

// Ambil parameter POST
$brand = $_POST['brand'] ?? '';
$style = $_POST['kode_style'] ?? [];

// Pastikan $style jadi array
if (!is_array($style)) {
    $style = [$style];
}

// Bersihkan array style
$style = array_filter(array_map('trim', $style));

// Validasi minimal ada brand dan style
if (empty($brand) || empty($style)) {
    echo json_encode([]);
    exit;
}

// Buat placeholder ? sesuai jumlah style
$placeholders = implode(',', array_fill(0, count($style), '?'));

// Query dinamis
$sql = "SELECT DISTINCT size 
        FROM m_size 
        WHERE brand = ? 
        AND kd_style IN ($placeholders)
        ORDER BY size ASC";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode([
        'error'  => 'Query prepare gagal',
        'detail' => $mysqli->error
    ]);
    exit;
}

// Binding parameter
$types = str_repeat('s', count($style) + 1); // +1 untuk brand
$params = array_merge([$types, $brand], $style);
$stmt->bind_param(...$params);

$stmt->execute();
$result = $stmt->get_result();

// Ambil data & hilangkan duplikat
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'   => $row['size'],
        'text' => $row['size']
    ];
}

// Output JSON
echo json_encode($data);
