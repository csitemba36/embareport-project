<?php
require_once('config.php');

$data = json_decode(file_get_contents("php://input"), true);
$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/receivings/store";


$initial_store = $data['initial_store'];
$details       = $data['details'];

$payload = json_encode([
    "initial_store" => $initial_store,
    "details" => $details
]);

// === CEK DUPLIKAT INVOICE DI yo_logs ===
$invoice_number = $details[0]['invoice_number'] ?? '';

if (!$mysqli->connect_errno && $invoice_number) {
    $sql  = "SELECT COUNT(*) as cnt 
             FROM yo_logs 
             WHERE payload LIKE ? and response_code = 200";
    $stmt = $mysqli->prepare($sql);
    $search = '%"invoice_number":"'.$invoice_number.'"%';
    $stmt->bind_param("s", $search); 
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res['cnt'] > 0) {
        echo json_encode([
            "id" => 409,
            "message" => "Data dengan nomor invoice $invoice_number sudah masuk di CIMS"
        ]);
        $mysqli->close();
        exit;
    }
}

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

if (curl_errno($curl)) {
    $error = curl_error($curl);

    // ==== LOGGING GAGAL ====
    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare("INSERT INTO yo_logs 
            (initial_store, payload, response_code, response_message, response_errors) 
            VALUES (?, ?, ?, ?, ?)");
        $code = 500;
        $msg  = "cURL Error";
        $stmt->bind_param("ssiss", $initial_store, $payload, $code, $msg, $error);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    }

    echo json_encode(["id" => 500, "message" => $msg, "errors" => [$error]]);
} else {
    // ==== LOGGING BERHASIL / GAGAL API ====
    $decoded = json_decode($response, true);

    $id      = $decoded['id'] ?? 0;
    $message = $decoded['message'] ?? '';
    $errors  = isset($decoded['errors']) ? json_encode($decoded['errors']) : null;

    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare("INSERT INTO yo_logs 
            (initial_store, payload, response_code, response_message, response_errors) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $initial_store, $payload, $id, $message, $errors);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    }

    echo $response;
}

curl_close($curl);
