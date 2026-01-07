<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "db_powerone_mcp");
if ($mysqli->connect_errno) {
    echo json_encode(["error" => $mysqli->connect_error]);
    exit;
}

/* =======================
   AMBIL PARAMETER FILTER
======================= */
$merk       = $_GET['merk'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

/* =======================
   MAP MERK → DEPT
======================= */
$merkMap = [
    'A' => 'EMBA JEANS',
    'B' => 'EMBA CLASSIC',
    'C' => 'EMBA LADIES',
    'D' => 'USED JEANS',
    'E' => 'MORPHIDAE'
];

/* =======================
   BUILD WHERE CONDITION
======================= */
$where = [];

// filter merk
if ($merk !== '' && isset($merkMap[$merk])) {
    $dept = $mysqli->real_escape_string($merkMap[$merk]);
    $where[] = "DEPT = '$dept'";
}

// filter periode
if ($start_date !== '' && $end_date !== '') {
    $where[] = "DATE(TRANSACT_DATE) BETWEEN '$start_date' AND '$end_date'";
}

$whereSQL = '';
if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

/* =======================
   QUERY
======================= */
$sql = "
    SELECT
        id,
        DEPT,
        PROCESS_DATE,
        TRANSACT_DATE,
        STORE_NO,
        STORE_NAME,
        POS_NO,
        TRANSACT_NO,
        TOTAL_QTY,
        TOTAL_GROSS,
        TOTAL_DISCOUNT,
        TOTAL_NET,
        REFERENSI_FILE,
        USER_UPLOAD,
        TGL_UPLOAD
    FROM sales_transaction_header
    $whereSQL
    ORDER BY TGL_UPLOAD DESC
";

$result = $mysqli->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "data" => $data
]);
