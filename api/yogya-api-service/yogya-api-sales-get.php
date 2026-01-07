<?php 
require_once('config.php');

// === Set header JSON output ===
header('Content-Type: application/json; charset=utf-8');

// === Endpoint khusus untuk SALES ===
$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/sales/sales"
    . "?initial_store=ALL"
    . "&type=ALL"
    . "&status=ALL"
    . "&orderby=initial_store"
    . "&orderDir=ASC"
    . "&start_date=2025-12-01"
    . "&end_date=2025-12-17"
    . "&page=1"
    . "&per_page=2000";

// === cURL request ke API ===
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        "source: $source",
        "cims-api-key: $apikey",
        "origin: $origin"
    ],
]);

$response = curl_exec($curl);
if (curl_errno($curl)) {
    die(json_encode(["error" => "cURL error: " . curl_error($curl)]));
}
curl_close($curl);

// Decode JSON response
$data = json_decode($response, true);

if (!isset($data['data']) || !is_array($data['data'])) {
    echo json_encode(["status" => "error", "message" => "Format JSON tidak valid"]);
    exit;
}

// Loop header
foreach ($data['data'] as $row) {
    $nomor_bon = $row['nomor_bon'];

    // Upsert ke header
    $sql_header = "
        INSERT INTO yo_sales_header (
            supp_code, initial_store, store_name, nomor_bon, created_by,
            type, status, sales_date, cancel_date, cancel_desc
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            supp_code = VALUES(supp_code),
            initial_store = VALUES(initial_store),
            store_name = VALUES(store_name),
            created_by = VALUES(created_by),
            type = VALUES(type),
            status = VALUES(status),
            sales_date = VALUES(sales_date),
            cancel_date = VALUES(cancel_date),
            cancel_desc = VALUES(cancel_desc),
            updated_at = CURRENT_TIMESTAMP
    ";
    $stmt = $mysqli->prepare($sql_header);
    $stmt->bind_param(
        "ssssssssss",
        $row['supp_code'],
        $row['initial_store'],
        $row['store_name'],
        $nomor_bon,
        $row['created_by'],
        $row['type'],
        $row['status'],
        $row['sales_date'],
        $row['cancel_date'],
        $row['cancel_desc']
    );
    $stmt->execute();

    // Hapus detail lama untuk nomor_bon ini
    $del = $mysqli->prepare("DELETE FROM yo_sales_details WHERE nomor_bon = ?");
    $del->bind_param("s", $nomor_bon);
    $del->execute();

    // Insert detail baru
    $sql_detail = "
        INSERT INTO yo_sales_details (
            nomor_bon, plu_yogya, promo_name, style_code, supplier_barcode,
            art_description, brand_code, brand_name, model, color, size,
            sales_qty, sales_bruto, disc_event, disc_member, sales_netto, is_free
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt_detail = $mysqli->prepare($sql_detail);

    foreach ($row['details'] as $d) {
        $stmt_detail->bind_param(
            "ssssssssssssddddd",
            $nomor_bon,
            $d['plu_yogya'],
            $d['promo_name'],
            $d['style_code'],
            $d['supplier_barcode'],
            $d['art_description'],
            $d['brand_code'],
            $d['brand_name'],
            $d['model'],
            $d['color'],
            $d['size'],
            $d['sales_qty'],
            $d['sales_bruto'],
            $d['disc_event'],
            $d['disc_member'],
            $d['sales_netto'],
            $d['is_free']
        );
        $stmt_detail->execute();
    }
}

echo json_encode([
    "status" => "success",
    "code"   => 200,
    "message"=> "Import sukses!"
]);
