<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php'); // koneksi MySQLi kamu

$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';

if (empty($brand)) {
    echo json_encode([]);
    exit;
}

$query = "SELECT kode, `range` FROM m_range WHERE brand = ? AND TRIM(`range`) <> '' ORDER BY `kode` ASC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $brand);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'kode' => $row['kode'],
        'range' => $row['kode'] . ' - ' . $row['range']
    ];
}

echo json_encode($data);
?>
