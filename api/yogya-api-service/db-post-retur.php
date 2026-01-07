<?php
require_once('config.php');

header('Content-Type: application/json');

// ================= AMBIL JSON =================
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "id" => 400,
        "message" => "Invalid JSON payload"
    ]);
    exit;
}

// ================= ENDPOINT =================
$endpoint = $api['endpoint'] . "/api/v1/yogya-cims/returns/store";

// ================= HEADER DATA =================
$initial_store = $data['initial_store'] ?? '';   // a.KodeGudang
$return_number = $data['return_number'] ?? '';   // a.ParentTransaction
$return_date   = $data['return_date'] ?? date('Y-m-d');

$detailsInput  = $data['details'] ?? [];

// fullname dari cookies login
$fullname = $_COOKIE['fullname'] ?? 'SYSTEM';

// ================= VALIDASI HEADER =================
if ($initial_store === '' || $return_number === '' || empty($detailsInput)) {
    echo json_encode([
        "id" => 400,
        "message" => "Header retur atau detail kosong"
    ]);
    exit;
}

// ================= AMBIL POSITION (yo_store.name) =================
$position = '';

if (!$mysqli->connect_errno) {
    $stmt = $mysqli->prepare("
        SELECT name
        FROM yo_store
        WHERE initial = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $initial_store);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $position = $res['name'] ?? '';
    $stmt->close();
}

// ================= NORMALISASI DETAIL =================
$details = [];

foreach ($detailsInput as $d) {

    if (
        empty($d['supplier_barcode']) ||
        empty($d['qty_return'])
    ) continue;

    $qty = (int)$d['qty_return'];
    if ($qty <= 0) continue;

    $details[] = [
        "supplier_barcode" => trim((string)$d['supplier_barcode']),
        "qty_return"       => $qty
    ];
}

if (count($details) === 0) {
    echo json_encode([
        "id" => 400,
        "message" => "Detail retur tidak valid"
    ]);
    exit;
}

// ================= PAYLOAD =================
$payloadArr = [
    "initial_store" => $initial_store,
    "return_number" => $return_number,
    "return_date"   => $return_date,
    "in_charge"     => $fullname,
    "position"      => $position,
    "picked_by"     => "Sales",
    "picked_name"   => $fullname,
    "reason"        => "Other",
    "note"          => "retur api",
    "details"       => $details
];

$payload = json_encode($payloadArr, JSON_UNESCAPED_SLASHES);

// ================= CEK DUPLIKAT RETURN =================
if (!$mysqli->connect_errno) {

    $sql = "SELECT COUNT(*) AS cnt
            FROM yo_logs
            WHERE payload LIKE ?
              AND response_code = 200";

    $stmt = $mysqli->prepare($sql);
    $search = '%"return_number":"'.$return_number.'"%';
    $stmt->bind_param("s", $search);
    $stmt->execute();

    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res['cnt'] > 0) {
        echo json_encode([
            "id" => 409,
            "message" => "Return number $return_number sudah terkirim ke CIMS"
        ]);
        exit;
    }
}

// ================= CURL =================
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
        "Content-Type: application/json",
        "Content-Length: " . strlen($payload)
    ],
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($curl);

// ================= ERROR CURL =================
if (curl_errno($curl)) {

    $error = curl_error($curl);

    if (!$mysqli->connect_errno) {
        $stmt = $mysqli->prepare("
            INSERT INTO yo_logs
            (initial_store, payload, response_code, response_message, response_errors)
            VALUES (?, ?, ?, ?, ?)
        ");
        $code = 500;
        $msg  = "cURL Error";
        $stmt->bind_param("ssiss", $initial_store, $payload, $code, $msg, $error);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    }

    echo json_encode([
        "id" => 500,
        "message" => "cURL Error",
        "errors" => [$error]
    ]);

    curl_close($curl);
    exit;
}

// ================= RESPONSE API =================
curl_close($curl);

// ================= LOG RESPONSE =================
if (!$mysqli->connect_errno) {
    $decoded = json_decode($response, true);
    $id      = $decoded['id'] ?? 0;
    $message = $decoded['message'] ?? '';
    $errors  = isset($decoded['errors']) ? json_encode($decoded['errors']) : null;

    $stmt = $mysqli->prepare("
        INSERT INTO yo_logs
        (initial_store, payload, response_code, response_message, response_errors)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssiss", $initial_store, $payload, $id, $message, $errors);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// ================= OUTPUT =================
echo $response;
exit;