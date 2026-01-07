<?php
require_once __DIR__ . '\Connect.php';

// Ambil parameter dari GET
$startdate = $_GET['startdate'] ?? null;
$enddate = $_GET['enddate'] ?? null;

// Siapkan query
$query = "SELECT * FROM mds_sales_report";
$conditions = [];

if ($startdate && $enddate) {
    $conditions[] = "transactionDate BETWEEN '$startdate' AND '$enddate'";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY transactionDate DESC";

// Eksekusi query
$result = $mysqli->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
