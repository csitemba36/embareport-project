<?php
require_once('config.php');

// Ambil data dari POST (array details)
$data = $_POST['details'] ?? [];

if (empty($data)) {
    echo json_encode(["error" => true, "message" => "Tidak ada data dikirim"]);
    exit;
}

// JSON encode payload sesuai API CIMS
$payload = json_encode([
    "details" => $data
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/pim/store";
echo $endpoint;
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "source: $source",
        "cims-api-key: $apikey",
        "origin: $origin",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(["error" => true, "message" => $err]);
} else {
    echo $response; // langsung balikin response dari API CIMS
}
