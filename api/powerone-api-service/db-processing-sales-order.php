<?php
require_once "db-config.php";


// --- Ambil parameter brand dari request ---
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'emba_jeans';

// Validasi brand agar aman
$allowedBrands = ['emba_jeans', 'bbg_twist'];
if (!in_array($brand, $allowedBrands)) {
    echo json_encode(["error" => "Invalid brand"]);
    exit;
}

// --- Koneksi ke SQL Anywhere ---
$db = new db_odbc($brand);
$conn = $db->getConnection();

// --- Parameter dari DataTables ---
$draw        = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start       = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length      = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';


// --- Filter utama ---
// tampilkan hanya data dengan flag_print_sj = 0
// --- Range tanggal default ---
$startDate = '2025-09-01';
$endDate   = date('Y-m-d'); // hari ini otomatis

// --- Filter utama ---
$where = "WHERE flag_print_sj = 0 
          AND tgl BETWEEN '$startDate' AND '$endDate'
          AND status <> 'C' 
          AND bukti_id NOT LIKE '%SDO%'
          ";

// --- Tambahkan filter pencarian global ---
if ($searchValue !== '') {
    $sv = str_replace("'", "''", $searchValue); // sanitasi
    $where .= " AND (
        bukti_id LIKE '%$sv%' OR
        kd_cust LIKE '%$sv%' OR
        kd_gudang LIKE '%$sv%' OR
        no_faktur LIKE '%$sv%'
    )";
}

// --- Hitung total data (dengan filter flag_print_sj = 0) ---
$sqlCount = "SELECT COUNT(*) AS cnt FROM tjual1_so $where";
$rs = odbc_exec($conn, $sqlCount);
$totalRecords = 0;
if ($rs && odbc_fetch_row($rs)) {
    $totalRecords = odbc_result($rs, "cnt");
}

// --- Hitung total setelah filter pencarian ---
$sqlCountFiltered = "SELECT COUNT(*) AS cnt FROM tjual1_so $where";
$rs2 = odbc_exec($conn, $sqlCountFiltered);
$totalFiltered = 0;
if ($rs2 && odbc_fetch_row($rs2)) {
    $totalFiltered = odbc_result($rs2, "cnt");
} else {
    $totalFiltered = $totalRecords;
}

// --- Paging manual SQL Anywhere 9 ---
$startAt = $start + 1; // START AT dimulai dari 1

$sql = "
    SELECT TOP $length START AT $startAt
        bukti_id,
        tgl,
        kd_cust,
        tipe_trans,
        flag_print_sj,
        bukti_rekap
    FROM tjual1_so
    $where
    ORDER BY tgl DESC
";

$rs = odbc_exec($conn, $sql);

// --- Proses hasil query ---
$data = [];
if ($rs) {
    while ($row = odbc_fetch_array($rs)) {
        $row = array_map("utf8_encode", $row);
        if (!empty($row['tgl'])) {
            $row['tgl'] = date('Y-m-d', strtotime($row['tgl']));
        }
        $data[] = $row;
    }
}

// --- Response JSON ---
$response = [
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
