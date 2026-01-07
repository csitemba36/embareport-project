<?php
header('Content-Type: application/json');

// ===============================
// KONEKSI DATABASE
// ===============================
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_powerone_mcp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Koneksi DB gagal",
        "error" => $conn->connect_error
    ]);
    exit;
}

// ===============================
// AMBIL TOKEN TERBARU
// ===============================
$qToken = $conn->query("
    SELECT token 
    FROM mcp_token 
    WHERE success = 1 
    ORDER BY id DESC 
    LIMIT 1
");

if (!$qToken || $qToken->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Token tidak ditemukan di database"
    ]);
    exit;
}

$rowToken = $qToken->fetch_assoc();
$token = $rowToken['token'];

// ===============================
// CURL GET STORES
// ===============================
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://mcp.matahari.co.id:81/Store/GetStores',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ],
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curl_error = curl_error($curl);
curl_close($curl);

// ===============================
// VALIDASI RESPONSE
// ===============================
if ($response === false) {
    echo json_encode([
        "success" => false,
        "message" => "CURL error",
        "error" => $curl_error
    ]);
    exit;
}

$data = json_decode($response, true);

if ($http_code !== 200 || !is_array($data)) {
    echo json_encode([
        "success" => false,
        "message" => "Response API tidak valid",
        "http_code" => $http_code,
        "raw_response" => $response
    ]);
    exit;
}

// ===============================
// INSERT / UPDATE DATA STORE
// ===============================
$inserted = 0;
$updated  = 0;

foreach ($data as $store) {

    $storeId   = intval($store['storeId']);
    $storeName = $conn->real_escape_string($store['storeName']);
    $openDate  = !empty($store['openDate']) ? date('Y-m-d H:i:s', strtotime($store['openDate'])) : null;
    $closeDate = !empty($store['closeDate']) ? date('Y-m-d H:i:s', strtotime($store['closeDate'])) : null;
    $status    = $conn->real_escape_string($store['status']);
    $margin    = $conn->real_escape_string($store['storeTypeMargin']);

    // Cek existing
    $check = $conn->query("SELECT id FROM mcp_stores WHERE store_id = $storeId");

    if ($check->num_rows > 0) {

        // UPDATE
        $sql = "
            UPDATE mcp_stores SET
                store_name = '$storeName',
                open_date = " . ($openDate ? "'$openDate'" : "NULL") . ",
                close_date = " . ($closeDate ? "'$closeDate'" : "NULL") . ",
                status = '$status',
                store_type_margin = '$margin',
                updated_at = NOW()
            WHERE store_id = $storeId
        ";

        if ($conn->query($sql)) {
            $updated++;
        }

    } else {

        // INSERT
        $sql = "
            INSERT INTO mcp_stores (
                store_id, store_name, open_date, close_date, status, store_type_margin
            ) VALUES (
                $storeId,
                '$storeName',
                " . ($openDate ? "'$openDate'" : "NULL") . ",
                " . ($closeDate ? "'$closeDate'" : "NULL") . ",
                '$status',
                '$margin'
            )
        ";

        if ($conn->query($sql)) {
            $inserted++;
        }
    }
}

// ===============================
// RESPONSE AKHIR
// ===============================
echo json_encode([
    "success" => true,
    "message" => "Sync store selesai",
    "total_api_data" => count($data),
    "inserted" => $inserted,
    "updated" => $updated
], JSON_PRETTY_PRINT);

$conn->close();
