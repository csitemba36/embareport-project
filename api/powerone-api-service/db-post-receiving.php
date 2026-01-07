<?php
$data = json_decode(file_get_contents("php://input"), true);

$initial_store = $data['initial_store'];
$details       = $data['details'];

$payload = json_encode([
    "initial_store" => $initial_store,
    "details" => $details
]);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://cims-api-staging.yogyagroup.com/api/v1/yogya-cims/receivings/store",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "source: K088",
        "cims-api-key: b4b489fd6e561a208baa07ba29adceeceb7ce37ca58fcaf63e9aef6e154c",
        "origin: 203.173.88.185",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($curl);
if (curl_errno($curl)) {
    echo json_encode(["error" => curl_error($curl)]);
} else {
    echo $response;
}
curl_close($curl);
