<?php
require_once('../config/connect_db.php'); // koneksi $mysqli

$nomor_bon = $_GET['nomor_bon'] ?? '';

if (empty($nomor_bon)) {
    echo json_encode(["data" => []]);
    exit;
}

$nomor_bon = $mysqli->real_escape_string($nomor_bon);

$sql = "SELECT id, nomor_bon, plu_yogya, promo_name, style_code, supplier_barcode,
               art_description, brand_code, brand_name, model, color, size,
               sales_qty, sales_bruto, disc_event, disc_member, sales_netto,
               is_free, created_at, updated_at
        FROM yo_sales_details
        WHERE nomor_bon = '$nomor_bon'";

$result = $mysqli->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "data" => $data
]);
