<?php
header('Content-Type: application/json');
require_once("../config/connect_db.php");

// Ambil parameter DataTables
$draw = $_POST['draw'] ?? 1;
$row = $_POST['start'] ?? 0;
$rowperpage = $_POST['length'] ?? 20;  
$searchValue = $_POST['search']['value'] ?? ''; 

// Kolom untuk ordering
$columns = [
    "nomor_bon",
    "status_code",
    "error_message",
    "response_json",
    "created_at"
];

$orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
$orderColumn = $columns[$orderColumnIndex] ?? "id";
$orderDir = $_POST['order'][0]['dir'] ?? "desc";

// Total records
$totalRecordsQuery = $mysqli->query("SELECT COUNT(*) AS total FROM yo_logs_sales");
$totalRecords = $totalRecordsQuery->fetch_assoc()['total'];

// Filter jika ada pencarian
$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = $mysqli->real_escape_string($searchValue);
    $searchQuery = " AND (
        nomor_bon LIKE '%$searchValue%' OR
        status_code LIKE '%$searchValue%' OR
        error_message LIKE '%$searchValue%' OR
        response_json LIKE '%$searchValue%'
    )";
}

// Total filtered
$totalFilteredQuery = $mysqli->query("
    SELECT COUNT(*) AS total 
    FROM yo_logs_sales 
    WHERE 1=1 $searchQuery
");
$totalFiltered = $totalFilteredQuery->fetch_assoc()['total'];

// Fetch data
$query = "
    SELECT nomor_bon, status_code, error_message, response_json, created_at 
    FROM yo_logs_sales
    WHERE 1=1 $searchQuery
    ORDER BY $orderColumn $orderDir
    LIMIT $row, $rowperpage
";

$result = $mysqli->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Output JSON
$response = [
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
];

echo json_encode($response);
