<?php
require_once('../config/connect_db.php'); // koneksi $mysqli

// Ambil parameter dari DataTables
$draw   = intval($_GET['draw'] ?? 0);
$start  = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$search = $_GET['search']['value'] ?? "";

// Daftar kolom valid untuk order
$validColumns = [
    "id","supp_code","initial_store","store_name","nomor_bon",
    "created_by","type","status","sales_date","cancel_date",
    "cancel_desc","created_at","updated_at","brand_code"
];

// Total record
$totalQuery = $mysqli->query("SELECT COUNT(*) as total FROM yo_sales_header");
$totalData  = $totalQuery->fetch_assoc();
$totalRecords = intval($totalData['total']);

// Query dasar dengan join brand_code
$sql = "SELECT h.id, h.supp_code, h.initial_store, h.store_name, h.nomor_bon, h.created_by,
               h.type, h.status, h.sales_date, h.cancel_date, h.cancel_desc, 
               h.created_at, h.updated_at,
               d.brand_code
        FROM yo_sales_header h
        LEFT JOIN (
            SELECT nomor_bon, MIN(brand_code) AS brand_code
            FROM yo_sales_details
            GROUP BY nomor_bon
        ) d ON h.nomor_bon = d.nomor_bon
        WHERE 1=1 
          AND h.status = 'finish' 
		  and d.brand_code IN ('E060','E395')
          -- AND h.sales_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
          -- AND h.sales_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
          AND h.sales_date >= '2025-12-01'
          AND h.sales_date <= CURDATE()
          AND NOT EXISTS (
                SELECT 1 
                FROM yo_logs_sales l
                WHERE l.nomor_bon = h.nomor_bon
                AND l.status_code = 200
            )
          AND h.nomor_bon NOT IN (SELECT nomor_bon FROM blacklist_no_bon_yogya)
          ";

// Search global
if (!empty($search)) {
    $searchEsc = $mysqli->real_escape_string($search);
    $sql .= " AND (
                h.nomor_bon LIKE '%$searchEsc%' 
                OR h.store_name LIKE '%$searchEsc%' 
                OR h.supp_code LIKE '%$searchEsc%' 
                OR d.brand_code LIKE '%$searchEsc%'
              )";
}

// Hitung filtered
$filterQuery = $mysqli->query($sql);
if (!$filterQuery) {
    die(json_encode(["error" => $mysqli->error, "query" => $sql]));
}
$recordsFiltered = $filterQuery->num_rows;

// Order + Paging
$orderColumnIndex = intval($_GET['order'][0]['column'] ?? 0);
$orderDir = ($_GET['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

// Validasi nama kolom (hindari injection)
$orderColumnName = $validColumns[$orderColumnIndex - 1] ?? "sales_date"; 

// Tambahkan ORDER BY + LIMIT
$sql .= " ORDER BY h.sales_date DESC LIMIT $start, $length";

// Ambil data
$dataQuery = $mysqli->query($sql);
if (!$dataQuery) {
    die(json_encode(["error" => $mysqli->error, "query" => $sql]));
}

$data = [];
while ($row = $dataQuery->fetch_assoc()) {
    $data[] = $row;
}

// Output JSON
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => '-',
    "recordsFiltered" => $recordsFiltered,
    "data" => $data
]);
?>
