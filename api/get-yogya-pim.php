<?php
require_once('../config/connect_db.php'); // koneksi mysqli

// Ambil parameter dari DataTables
$draw = $_POST['draw'] ?? 1;
$row = $_POST['start'] ?? 0;
$rowperpage = $_POST['length'] ?? 10;
$searchValue = $_POST['search']['value'] ?? '';

// Query count total records
$totalRecordsQuery = $mysqli->query("SELECT COUNT(*) AS total FROM yo_pim");
$totalRecords = $totalRecordsQuery->fetch_assoc()['total'];

// Query with search
$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = " WHERE 
        supp_code LIKE '%$searchValue%' OR 
        brand_code LIKE '%$searchValue%' OR 
        brand_desc LIKE '%$searchValue%' OR 
        style_code LIKE '%$searchValue%' OR 
        supplier_barcode LIKE '%$searchValue%' OR 
        art_desc LIKE '%$searchValue%' OR 
        color LIKE '%$searchValue%' OR 
        size LIKE '%$searchValue%' OR 
        model LIKE '%$searchValue%' OR 
        `group` LIKE '%$searchValue%'";
}

// Total filtered
$totalFilteredQuery = $mysqli->query("SELECT COUNT(*) AS total FROM yo_pim $searchQuery");
$totalFiltered = $totalFilteredQuery->fetch_assoc()['total'];

// Ambil data
$sql = "SELECT * FROM yo_pim $searchQuery ORDER BY id DESC LIMIT $row, $rowperpage";
$result = $mysqli->query($sql);

$data = [];
while ($r = $result->fetch_assoc()) {
    $data[] = $r;
}

// Return JSON
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
];

echo json_encode($response);
