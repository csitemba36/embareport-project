<?php
// Config DB
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_powerone";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Koneksi gagal: " . $conn->connect_error);
}

// Ambil token terakhir dari mcp_token
$sqlToken = "SELECT token FROM mcp_token ORDER BY id DESC LIMIT 1";
$resToken = $conn->query($sqlToken);
if (!($resToken && $resToken->num_rows > 0)) {
    die("❌ Tidak ada token ditemukan di tabel mcp_token.");
}
$token = $resToken->fetch_assoc()['token'];
if (!$token) die("❌ Token kosong.");

// CURL GET brands
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://mcp.matahari.co.id:81/Brand/GetBrandsByUser',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Accept: application/json',
    'Authorization: Bearer ' . $token
  ),
));

$response = curl_exec($curl);
$curlErr = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Debug
echo "<h3>HTTP Status: $httpCode</h3>";
if ($curlErr) {
    die("❌ CURL Error: " . htmlspecialchars($curlErr));
}
echo "<h3>Raw Response:</h3><pre style='white-space:pre-wrap;word-break:break-word;'>" . htmlspecialchars($response) . "</pre><hr>";

// Decode JSON
$data = json_decode($response, true);
$jsonErr = json_last_error();
if ($jsonErr !== JSON_ERROR_NONE) {
    die("❌ JSON decode error: " . json_last_error_msg());
}

// normalize to array of brands
$brands = null;
if (is_array($data)) {
    // two possibilities:
    // 1) root is array of brand objects -> use directly
    // 2) root is associative array with key 'result' -> check that
    $isAssoc = array_keys($data) !== range(0, count($data) - 1);
    if ($isAssoc && isset($data['result']) && is_array($data['result'])) {
        $brands = $data['result'];
    } elseif (!$isAssoc) {
        // root is numeric array -> assume it's array of brands
        $brands = $data;
    } else {
        // some other object structure
        if (isset($data['result']) && is_array($data['result'])) {
            $brands = $data['result'];
        } else {
            die("❌ Struktur JSON tidak sesuai: root object tapi tidak ada 'result'.");
        }
    }
} else {
    die("❌ Response bukan array/object JSON yang valid.");
}

// function: normalize ISO datetime to MySQL DATETIME (remove fractional part & timezone Z)
function iso_to_mysql_datetime($s) {
    if ($s === null || $s === '') return null;
    // handle formats like 2022-12-10T00:48:34.727432 or 2022-12-10T00:48:34Z
    // remove timezone Z and fractional seconds
    $s = str_replace('Z', '', $s);
    $parts = preg_split('/[.]/', $s);
    $base = $parts[0]; // e.g. 2022-12-10T00:48:34
    $base = str_replace('T', ' ', $base);
    return $base; // e.g. 2022-12-10 00:48:34
}

// Insert / update loop
$inserted = 0;
$errors = [];

foreach ($brands as $brand) {
    // safety: skip if not object/array
    if (!is_array($brand)) continue;

    $brandId = isset($brand['brandId']) ? intval($brand['brandId']) : 0;
    if ($brandId === 0) {
        $errors[] = "brandId kosong/0, skipping: " . json_encode($brand);
        continue;
    }

    $brandName = $conn->real_escape_string($brand['brandName'] ?? '');
    $creatorUser = $conn->real_escape_string($brand['creatorUser'] ?? '');
    $creatorUserId = isset($brand['creatorUserId']) && $brand['creatorUserId'] !== null
                      ? ("'".$conn->real_escape_string($brand['creatorUserId'])."'") : "NULL";
    $creationTime = isset($brand['creationTime']) && $brand['creationTime'] !== null
                      ? ("'".$conn->real_escape_string(iso_to_mysql_datetime($brand['creationTime']))."'") : "NULL";
    $lastModifierUser = isset($brand['lastModifierUser']) && $brand['lastModifierUser'] !== null
                      ? ("'".$conn->real_escape_string($brand['lastModifierUser'])."'") : "NULL";
    $lastModifierUserId = isset($brand['lastModifierUserId']) && $brand['lastModifierUserId'] !== null
                      ? ("'".$conn->real_escape_string($brand['lastModifierUserId'])."'") : "NULL";
    $lastModificationTime = isset($brand['lastModificationTime']) && $brand['lastModificationTime'] !== null
                      ? ("'".$conn->real_escape_string(iso_to_mysql_datetime($brand['lastModificationTime']))."'") : "NULL";
    $deleteUser = isset($brand['deleteUser']) && $brand['deleteUser'] !== null
                      ? ("'".$conn->real_escape_string($brand['deleteUser'])."'") : "NULL";
    $deleteUserId = isset($brand['deleteUserId']) && $brand['deleteUserId'] !== null
                      ? ("'".$conn->real_escape_string($brand['deleteUserId'])."'") : "NULL";
    $deletionTime = isset($brand['deletionTime']) && $brand['deletionTime'] !== null
                      ? ("'".$conn->real_escape_string(iso_to_mysql_datetime($brand['deletionTime']))."'") : "NULL";
    $isDeleted = !empty($brand['isDeleted']) ? 1 : 0;

    // Build insert with ON DUPLICATE KEY UPDATE
    $sql = "
      INSERT INTO mcp_brands (
        brandId, brandName, creatorUser, creatorUserId, creationTime,
        lastModifierUser, lastModifierUserId, lastModificationTime,
        deleteUser, deleteUserId, deletionTime, isDeleted
      ) VALUES (
        $brandId, '$brandName', '$creatorUser', $creatorUserId, $creationTime,
        $lastModifierUser, $lastModifierUserId, $lastModificationTime,
        $deleteUser, $deleteUserId, $deletionTime, $isDeleted
      )
      ON DUPLICATE KEY UPDATE
        brandName = VALUES(brandName),
        creatorUser = VALUES(creatorUser),
        creatorUserId = VALUES(creatorUserId),
        creationTime = IF(VALUES(creationTime) IS NOT NULL, VALUES(creationTime), creationTime),
        lastModifierUser = VALUES(lastModifierUser),
        lastModifierUserId = VALUES(lastModifierUserId),
        lastModificationTime = VALUES(lastModificationTime),
        deleteUser = VALUES(deleteUser),
        deleteUserId = VALUES(deleteUserId),
        deletionTime = VALUES(deletionTime),
        isDeleted = VALUES(isDeleted)
    ";

    if ($conn->query($sql)) {
        $inserted++;
    } else {
        $errors[] = "Error brandId $brandId: " . $conn->error;
    }
}

// Result
echo "<h3>Result</h3>";
echo "Total brands processed: " . count($brands) . "<br>";
echo "Inserted/Updated: $inserted<br>";
if (!empty($errors)) {
    echo "<h4>Errors:</h4><pre>" . htmlspecialchars(implode("\n", $errors)) . "</pre>";
}

// Show all rows in mcp_brands (optional)
$resAll = $conn->query("SELECT brandId, brandName, creationTime, lastModificationTime, isDeleted, created_at, updated_at FROM mcp_brands ORDER BY brandId ASC");
if ($resAll) {
    echo "<h3>Current mcp_brands table</h3>";
    echo "<table border='1' cellpadding='6'><tr><th>brandId</th><th>brandName</th><th>creationTime</th><th>lastModificationTime</th><th>isDeleted</th><th>created_at</th><th>updated_at</th></tr>";
    while ($r = $resAll->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".htmlspecialchars($r['brandId'])."</td>";
        echo "<td>".htmlspecialchars($r['brandName'])."</td>";
        echo "<td>".htmlspecialchars($r['creationTime'])."</td>";
        echo "<td>".htmlspecialchars($r['lastModificationTime'])."</td>";
        echo "<td>".htmlspecialchars($r['isDeleted'])."</td>";
        echo "<td>".htmlspecialchars($r['created_at'])."</td>";
        echo "<td>".htmlspecialchars($r['updated_at'])."</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>
