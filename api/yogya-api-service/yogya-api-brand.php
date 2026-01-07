<?php
require_once('config.php');

$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/brand/list";

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

// === Insert/Update ke tabel yo_brand ===
foreach ($data['data'] as $brand) {
    $id          = $brand['id'] ?? null;
    $brand_name  = $brand['name_name'] ?? '';
    $brand_code  = $brand['brand_code'] ?? '';
    $update_time = date("Y-m-d H:i:s");

    if (!$brand_code) continue;

    // Cek apakah brand_code sudah ada
    $check = $mysqli->prepare("SELECT id FROM yo_brand WHERE brand_code = ?");
    $check->bind_param("s", $brand_code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update data
        $update = $mysqli->prepare("UPDATE yo_brand 
                                    SET brand_name = ?, update_time = ? 
                                    WHERE brand_code = ?");
        $update->bind_param("sss", $brand_name, $update_time, $brand_code);
        $update->execute();
        $update->close();
    } else {
        // Insert baru
        $insert = $mysqli->prepare("INSERT INTO yo_brand (brand_name, brand_code, update_time) 
                                    VALUES (?, ?, ?)");
        $insert->bind_param("sss", $brand_name, $brand_code, $update_time);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

echo "Sinkronisasi brand selesai untuk status: $status";
