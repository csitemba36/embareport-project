<?php
header('Content-Type: application/json');

// Koneksi ke database MySQL
$mysqli = new mysqli("localhost", "root", "", "db_powerone_mcp");

// Cek koneksi
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit;
}

$dept        = $_GET['dept'] ?? '';
$store_no    = $_GET['store_no'] ?? '';
$transact_no = $_GET['transact_no'] ?? '';
$pos_no      = $_GET['pos_no'] ?? '';

$sql = "
    SELECT
        ITEM,
        BARCODE,
        BRAND,
        COLOUR,
        SIZE,
        QTY,
        ITEM_PRICE,
        GROSS_AMT,
        DISC_AUTO,
        DISC_PROMO,
        DISC_EMPLOYEE,
        DISC_FREE_ITEM,
        DISC_STRUK,
        NET_AMT
    FROM sales_transaction_details
    WHERE DEPT = ?
      AND STORE_NO = ?
      AND TRANSACT_NO = ?
      AND POS_NO = ?
    ORDER BY TRANSACT_LINE_NO
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "ssss",
    $dept,
    $store_no,
    $transact_no,
    $pos_no
);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "data" => $data
]);
