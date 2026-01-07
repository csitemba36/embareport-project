<?php
require_once('config.php');

// === Endpoint khusus untuk PIM ===
$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/pim/list?page=ALL&limit=ALL&orderBy=DESC";

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

// === Insert/Update ke tabel yo_pim ===
foreach ($data['data'] as $item) {
    $supp_code       = $item['supp_code'] ?? '';
    $brand_code      = $item['brand_code'] ?? '';
    $brand_desc      = $item['brand_desc'] ?? '';
    $style_code      = $item['style_code'] ?? '';
    $supplier_barcode= $item['supplier_barcode'] ?? '';
    $art_desc        = $item['art_desc'] ?? '';
    $price           = $item['price'] ?? 0;
    $color           = $item['color'] ?? '';
    $size            = $item['size'] ?? '';
    $model           = $item['model'] ?? '';
    $group           = $item['group'] ?? '';
    $deleted_by      = $item['deleted_by'] ?? null;
    $deleted_date    = $item['deleted_date'] ?? null;
    $updated_date    = $item['updated_date'] ?? date("Y-m-d H:i:s");

    if (!$style_code) continue; // wajib ada style_code

    // Cek apakah style_code sudah ada
    $check = $mysqli->prepare("SELECT id FROM yo_pim WHERE style_code = ?");
    $check->bind_param("s", $style_code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update
        $update = $mysqli->prepare("UPDATE yo_pim SET 
            supp_code=?, brand_code=?, brand_desc=?, supplier_barcode=?, 
            art_desc=?, price=?, color=?, size=?, model=?, `group`=?, 
            deleted_by=?, deleted_date=?, updated_date=? 
            WHERE style_code=?");
        $update->bind_param(
            "sssssdssssssss",
            $supp_code, $brand_code, $brand_desc, $supplier_barcode,
            $art_desc, $price, $color, $size, $model, $group,
            $deleted_by, $deleted_date, $updated_date, $style_code
        );
        $update->execute();
        $update->close();
    } else {
        // Insert
        $insert = $mysqli->prepare("INSERT INTO yo_pim 
            (supp_code, brand_code, brand_desc, style_code, supplier_barcode, 
             art_desc, price, color, size, model, `group`, 
             deleted_by, deleted_date, updated_date) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param(
            "ssssssdsssssss",
            $supp_code, $brand_code, $brand_desc, $style_code, $supplier_barcode,
            $art_desc, $price, $color, $size, $model, $group,
            $deleted_by, $deleted_date, $updated_date
        );
        $insert->execute();
        $insert->close();
    }

    $check->close();
}

echo "Sinkronisasi PIM selesai untuk status: $status";
