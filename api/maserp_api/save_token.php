<?php
/* =====================================================
   SAVE TOKEN MAS ERP
   ===================================================== */

/* ===============================
   1️⃣ Koneksi database
   =============================== */
$servername   = "localhost";
$username_db  = "root";
$password_db  = "";
$dbname       = "db_powerone";

/* ===============================
   2️⃣ Data dikirim via CURL
   =============================== */
$postData = [
    "companyCode" => "MAS",
    "username"    => "userapi",
    "password"    => "userapi",
    "hostAddress" => ".\\SQL2022"
];

/* ===============================
   3️⃣ Endpoint API
   =============================== */
$baseUrl = "http://192.168.8.126";
$url     = $baseUrl . "/api/token";

/* ===============================
   4️⃣ CURL request
   =============================== */
$curl = curl_init($url);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_POSTFIELDS     => json_encode($postData),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
]);

$response = curl_exec($curl);
$error    = curl_error($curl);
curl_close($curl);

if ($error) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "status" => "FAILED",
        "error"  => $error
    ], JSON_PRETTY_PRINT);
    exit;
}

/* ===============================
   5️⃣ Decode hasil API
   =============================== */
$result = json_decode($response, true);

if (isset($result['access_token'])) {
    $access_token = $result['access_token'];
    $status = "SUCCESS";
} else {
    $access_token = null;
    $status = "FAILED";
}

/* ===============================
   6️⃣ Simpan / update database
   =============================== */
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "status" => "FAILED",
        "error"  => "DB Connection failed: " . $conn->connect_error
    ], JSON_PRETTY_PRINT);
    exit;
}

$query = "
INSERT INTO maserp_api_details
(status, host, companycode, username, password, token, tgl_update)
VALUES (?, ?, ?, ?, ?, ?, NOW())
ON DUPLICATE KEY UPDATE
status = VALUES(status),
host = VALUES(host),
password = VALUES(password),
token = VALUES(token),
tgl_update = NOW()
";

$stmt = $conn->prepare($query);
$stmt->bind_param(
    "ssssss",
    $status,
    $postData['hostAddress'],
    $postData['companyCode'],
    $postData['username'],
    $postData['password'],
    $access_token
);

$stmt->execute();
$stmt->close();
$conn->close();

/* ===============================
   7️⃣ Output data (API / UI)
   =============================== */
$output = [
    "status"       => $status,
    "company"      => $postData['companyCode'],
    "username"     => $postData['username'],
    "host"         => $postData['hostAddress'],
    "access_token" => $access_token,
    "api_response" => $result,
    "updated_at"   => date('Y-m-d H:i:s')
];

/* ===============================
   8️⃣ Deteksi API request
   =============================== */
$isApiRequest = isset($_SERVER['HTTP_ACCEPT']) &&
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($isApiRequest) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MAS ERP API Token</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 40px;
}
.card {
    max-width: 900px;
    margin: auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
}
.header {
    padding: 20px 30px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.badge {
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    color: #fff;
    font-weight: 600;
}
.success { background: #28a745; }
.failed  { background: #dc3545; }

.content {
    padding: 30px;
}
.label {
    font-weight: 600;
    color: #555;
}
pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 13px;
}
.footer {
    padding: 15px 30px;
    background: #fafafa;
    border-top: 1px solid #eee;
    font-size: 13px;
    color: #888;
    text-align: right;
}
</style>
</head>
<body>

<div class="card">
    <div class="header">
        <h2>API Token Result</h2>
        <span class="badge <?= $status === 'SUCCESS' ? 'success' : 'failed' ?>">
            <?= $status ?>
        </span>
    </div>

    <div class="content">
        <p><span class="label">Company:</span> <?= htmlspecialchars($output['company']) ?></p>
        <p><span class="label">Username:</span> <?= htmlspecialchars($output['username']) ?></p>
        <p><span class="label">Host:</span> <?= htmlspecialchars($output['host']) ?></p>
        <p><span class="label">Updated At:</span> <?= $output['updated_at'] ?></p>

        <h3>Access Token</h3>
        <pre><?= htmlspecialchars($output['access_token'] ?: '-') ?></pre>

        <h3>Raw API Response</h3>
        <pre><?= json_encode($output['api_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
    </div>

    <div class="footer">
        PowerOne • MAS ERP Integration
    </div>
</div>

</body>
</html>
