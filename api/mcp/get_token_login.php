<?php
// =======================================
// KONEKSI DATABASE
// =======================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_powerone_mcp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// =======================================
// CURL LOGIN REQUEST
// =======================================
$curl = curl_init();

$postBody = json_encode([
    "email" => "finance@embajeans.com",
    "password" => "3Mb4123!"
]);

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://mcp.matahari.co.id:81/Auth/Login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postBody,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json'
    ],
]);

$response   = curl_exec($curl);
$curl_errno = curl_errno($curl);
$curl_error = curl_error($curl);
$http_code  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// =======================================
// DEBUG CURL
// =======================================
echo "<h3>Debug CURL</h3>";
echo "<b>HTTP Status:</b> $http_code<br>";
if ($curl_errno) {
    echo "<b>cURL Error:</b> " . htmlspecialchars($curl_error) . "<br>";
}
echo "<hr>";

echo "<h3>Raw Response</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "<hr>";

// =======================================
// DECODE JSON
// =======================================
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<h3>JSON Decode Error</h3>";
    echo json_last_error_msg();
    exit;
}

// =======================================
// VALIDASI STRUKTUR RESPONSE
// =======================================
if (!isset($data['result']['userInfo'])) {
    echo "<h3>❌ Format response tidak valid</h3>";
    exit;
}

$userInfo = $data['result']['userInfo'];

// =======================================
// AMBIL DATA
// =======================================
$userId     = $conn->real_escape_string($userInfo['userId'] ?? '');
$firstName = $conn->real_escape_string($userInfo['firstName'] ?? '');
$lastName  = $conn->real_escape_string($userInfo['lastName'] ?? '');
$username  = $conn->real_escape_string($userInfo['username'] ?? '');
$email     = $conn->real_escape_string($userInfo['email'] ?? '');
$vendorId  = isset($userInfo['vendorId']) ? intval($userInfo['vendorId']) : "NULL";

$requirePasswordChange = !empty($userInfo['requirePasswordChange']) ? 1 : 0;
$purchaseType = $conn->real_escape_string($userInfo['purchaseType'] ?? '');
$role         = $conn->real_escape_string($userInfo['role'] ?? '');

$token          = $conn->real_escape_string($data['result']['token'] ?? '');
$expiration     = $conn->real_escape_string($data['result']['expiration'] ?? null);
$keycloakToken  = $conn->real_escape_string($data['result']['keycloakToken'] ?? '');
$success        = !empty($data['success']) ? 1 : 0;
$message        = $conn->real_escape_string($data['message'] ?? '');

// =======================================
// CEK DATA SUDAH ADA ATAU BELUM
// =======================================
$check = $conn->query("
    SELECT id 
    FROM mcp_token 
    WHERE username = '$username' 
      AND email = '$email'
    LIMIT 1
");

if ($check && $check->num_rows > 0) {

    // ===================================
    // UPDATE
    // ===================================
    $row = $check->fetch_assoc();
    $id  = intval($row['id']);

    $sql = "
        UPDATE mcp_token SET
            userId = '$userId',
            firstName = '$firstName',
            lastName = '$lastName',
            vendorId = $vendorId,
            requirePasswordChange = $requirePasswordChange,
            purchaseType = '$purchaseType',
            role = '$role',
            token = '$token',
            expiration = " . ($expiration ? "'$expiration'" : "NULL") . ",
            keycloakToken = '$keycloakToken',
            success = $success,
            message = '$message',
            updated_at = NOW()
        WHERE id = $id
    ";

    if ($conn->query($sql)) {
        echo "<h3>♻️ Token berhasil diperbarui (UPDATE)</h3>";
    } else {
        echo "<h3>❌ Gagal UPDATE</h3>";
        echo htmlspecialchars($conn->error);
    }

} else {

    // ===================================
    // INSERT
    // ===================================
    $sql = "
        INSERT INTO mcp_token (
            userId, firstName, lastName, username, email, vendorId,
            requirePasswordChange, purchaseType, role, token,
            expiration, keycloakToken, success, message, created_at
        ) VALUES (
            '$userId', '$firstName', '$lastName', '$username', '$email', $vendorId,
            $requirePasswordChange, '$purchaseType', '$role', '$token',
            " . ($expiration ? "'$expiration'" : "NULL") . ",
            '$keycloakToken', $success, '$message', NOW()
        )
    ";

    if ($conn->query($sql)) {
        echo "<h3>✅ Token baru berhasil disimpan (INSERT)</h3>";
    } else {
        echo "<h3>❌ Gagal INSERT</h3>";
        echo htmlspecialchars($conn->error);
    }
}

// =======================================
// TAMPILKAN TOKEN TERBARU
// =======================================
$res = $conn->query("
    SELECT id, username, email, expiration, created_at 
    FROM mcp_token 
    ORDER BY id DESC
    LIMIT 5
");

if ($res && $res->num_rows > 0) {
    echo "<h3>Latest Tokens</h3>";
    echo "<table border='1' cellpadding='6'>";
    echo "<tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Expiration</th>
            <th>Created</th>
          </tr>";
    while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td>{$r['id']}</td>
                <td>{$r['username']}</td>
                <td>{$r['email']}</td>
                <td>{$r['expiration']}</td>
                <td>{$r['created_at']}</td>
              </tr>";
    }
    echo "</table>";
}

$conn->close();
?>
