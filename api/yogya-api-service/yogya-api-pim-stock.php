<?php
require_once('config.php');

$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/pim/stocks?page=ALL&limit=ALL&orderBy=DESC&initial_store=ALL";

// === cURL request ke API ===
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => array(
        "source: $source",
        "cims-api-key: $apikey",
        "origin: $origin"
    ),
));
$response = curl_exec($curl);

if (curl_errno($curl)) {
    die("cURL error: " . curl_error($curl));
}
curl_close($curl);

// Decode JSON response
$data = json_decode($response, true);

if (!isset($data['data'])) {
    die("Response API tidak sesuai format: " . $response);
}

// === Insert/Update ke tabel yo_stock ===
foreach ($data['data'] as $stock) {
    $supp_code       = $stock['supp_code'] ?? '';
    $initial_store   = $stock['initial_store'] ?? '';
    $name            = $stock['name'] ?? '';
    $supplier_barcode= $stock['supplier_barcode'] ?? '';
    $qty_stock       = $stock['stock'] ?? 0;
    $update_time     = date("Y-m-d H:i:s");

    if (!$supplier_barcode || !$initial_store) continue; // wajib ada key unik

    // Cek apakah data sudah ada
    $check = $mysqli->prepare("SELECT id FROM yo_stock WHERE supplier_barcode = ? AND initial_store = ?");
    $check->bind_param("ss", $supplier_barcode, $initial_store);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update data
        $update = $mysqli->prepare("UPDATE yo_stock 
                                    SET supp_code = ?, name = ?, stock = ?, update_time = ? 
                                    WHERE supplier_barcode = ? AND initial_store = ?");
        $update->bind_param("ssisss", $supp_code, $name, $qty_stock, $update_time, $supplier_barcode, $initial_store);
        $update->execute();
        $update->close();
    } else {
        // Insert baru
        $insert = $mysqli->prepare("INSERT INTO yo_stock (supp_code, initial_store, name, supplier_barcode, stock, update_time) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssis", $supp_code, $initial_store, $name, $supplier_barcode, $qty_stock, $update_time);
        $insert->execute();
        $insert->close();
    }

    $check->close();
}

echo "Sinkronisasi stock selesai. Jumlah data: " . count($data['data']);
