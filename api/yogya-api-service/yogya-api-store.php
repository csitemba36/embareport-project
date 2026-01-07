<?php
require_once('config.php');

$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/store/stores";

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

// === Insert/Update ke tabel yo_store ===
foreach ($data['data'] as $store) {
    $name        = $store['name'] ?? '';
    $initial     = $store['initial'] ?? '';
    $address     = $store['address'] ?? '';
    $update_time = date("Y-m-d H:i:s");

    if (!$initial) continue; // pakai initial sebagai key unik

    // Cek apakah initial sudah ada
    $check = $mysqli->prepare("SELECT id FROM yo_store WHERE initial = ?");
    $check->bind_param("s", $initial);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update data
        $update = $mysqli->prepare("UPDATE yo_store 
                                    SET name = ?, address = ?, update_time = ? 
                                    WHERE initial = ?");
        $update->bind_param("ssss", $name, $address, $update_time, $initial);
        $update->execute();
        $update->close();
    } else {
        // Insert baru
        $insert = $mysqli->prepare("INSERT INTO yo_store (name, initial, address, update_time) 
                                    VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $name, $initial, $address, $update_time);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

echo "Sinkronisasi store selesai untuk status: $status";
