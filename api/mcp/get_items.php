<?php
// === KONEKSI DATABASE ===
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_powerone";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// === PARAMETER ===
// Brand wajib, bisa diinput via URL (?brand=4090)
$brand = isset($_GET['brand']) ? intval($_GET['brand']) : 6236;  //ganti sesuai brand yang diambil
$take  = 1000; // ambil 1000 data per batch

// === AMBIL TOKEN TERBARU ===
$sqlToken = "SELECT token FROM mcp_token ORDER BY id DESC LIMIT 1";
$resultToken = $conn->query($sqlToken);
if ($resultToken->num_rows == 0) {
    die("❌ Token tidak ditemukan di tabel mcp_token");
}
$row = $resultToken->fetch_assoc();
$accessToken = trim($row['token']);

// === CEK BERAPA DATA YANG SUDAH ADA DI DATABASE ===
$sqlCount = "SELECT COUNT(*) AS total FROM mcp_items WHERE brandKey = $brand";
$resCount = $conn->query($sqlCount);
$rowCount = $resCount->fetch_assoc();
$skip = intval($rowCount['total']); // mulai dari data terakhir

echo "🚀 Mulai sync brandKey: $brand dari posisi skip=$skip...\n";

// === LOOP SAMPAI HABIS ===
$totalData = null;
$batch = 0;
$countInserted = 0;
$countUpdated = 0;

do {
    $batch++;
    echo "\n=== Batch #$batch | skip=$skip | take=$take ===\n";

    // BODY REQUEST
    $body = [
        "filter" => [
            "skip" => $skip,
            "take" => $take
        ],
        "brands" => [$brand]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://mcp.matahari.co.id:81/ItemDetail/GetItemDetails',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => [
            'Accept: text/plain',
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        echo "❌ CURL Error: $curlError\n";
        break;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Response bukan JSON valid!\n";
        echo $response;
        break;
    }

    if (!isset($data['result']) || !is_array($data['result'])) {
        echo "❌ Format data tidak sesuai!\n";
        echo $response;
        break;
    }

    // Total data API
    $totalData = isset($data['dataCount']) ? intval($data['dataCount']) : 0;
    $resultCount = count($data['result']);

    echo "📦 Diterima $resultCount dari total $totalData data.\n";

    // === Simpan / Update ke database ===
    foreach ($data['result'] as $item) {
        $itemId = $conn->real_escape_string($item['itemId']);
        $check = $conn->query("SELECT itemId FROM mcp_items WHERE itemId = '$itemId'");

        foreach ($item as $key => $val) {
            $item[$key] = $conn->real_escape_string($val);
        }

        if ($check->num_rows > 0) {
            $sql = "
                UPDATE mcp_items SET
                    supplierId='{$item['supplierId']}',
                    supplierBarcode='{$item['supplierBarcode']}',
                    styleCode='{$item['styleCode']}',
                    description='{$item['description']}',
                    color='{$item['color']}',
                    colorKey='{$item['colorKey']}',
                    packSize='{$item['packSize']}',
                    packSizeKey='{$item['packSizeKey']}',
                    retailPrice='{$item['retailPrice']}',
                    brand='{$item['brand']}',
                    brandKey='{$item['brandKey']}',
                    divisi='{$item['div']}',
                    divKey='{$item['divKey']}',
                    grp='{$item['group']}',
                    groupKey='{$item['groupKey']}',
                    dept='{$item['dept']}',
                    deptKey='{$item['deptKey']}',
                    class='{$item['class']}',
                    classKey='{$item['classKey']}',
                    subClass='{$item['subClass']}',
                    subClassKey='{$item['subClassKey']}',
                    size='{$item['size']}',
                    sizeKey='{$item['sizeKey']}',
                    composition='{$item['composition']}',
                    compositionKey='{$item['compositionKey']}',
                    construction='{$item['construction']}',
                    constructionKey='{$item['constructionKey']}',
                    pattern='{$item['pattern']}',
                    patternKey='{$item['patternKey']}',
                    productDesc='{$item['productDesc']}',
                    productDescKey='{$item['productDescKey']}',
                    productType='{$item['productType']}',
                    productTypeKey='{$item['productTypeKey']}',
                    silhouette='{$item['silhouette']}',
                    silhouetteKey='{$item['silhouetteKey']}',
                    world='{$item['world']}',
                    worldKey='{$item['worldKey']}'
                WHERE itemId='$itemId'
            ";
            if ($conn->query($sql)) $countUpdated++;
        } else {
            $sql = "
                INSERT INTO mcp_items (
                    itemId, supplierId, supplierBarcode, styleCode, description, color, colorKey,
                    packSize, packSizeKey, retailPrice, brand, brandKey, divisi, divKey,
                    grp, groupKey, dept, deptKey, class, classKey, subClass, subClassKey,
                    size, sizeKey, composition, compositionKey, construction, constructionKey,
                    pattern, patternKey, productDesc, productDescKey, productType, productTypeKey,
                    silhouette, silhouetteKey, world, worldKey
                ) VALUES (
                    '{$item['itemId']}', '{$item['supplierId']}', '{$item['supplierBarcode']}', '{$item['styleCode']}',
                    '{$item['description']}', '{$item['color']}', '{$item['colorKey']}', '{$item['packSize']}',
                    '{$item['packSizeKey']}', '{$item['retailPrice']}', '{$item['brand']}', '{$item['brandKey']}',
                    '{$item['div']}', '{$item['divKey']}', '{$item['group']}', '{$item['groupKey']}',
                    '{$item['dept']}', '{$item['deptKey']}', '{$item['class']}', '{$item['classKey']}',
                    '{$item['subClass']}', '{$item['subClassKey']}', '{$item['size']}', '{$item['sizeKey']}',
                    '{$item['composition']}', '{$item['compositionKey']}', '{$item['construction']}', '{$item['constructionKey']}',
                    '{$item['pattern']}', '{$item['patternKey']}', '{$item['productDesc']}', '{$item['productDescKey']}',
                    '{$item['productType']}', '{$item['productTypeKey']}', '{$item['silhouette']}', '{$item['silhouetteKey']}',
                    '{$item['world']}', '{$item['worldKey']}'
                )
            ";
            if ($conn->query($sql)) $countInserted++;
        }
    }

    $skip += $take;
    echo "✅ Batch #$batch selesai. (skip sekarang: $skip)\n";

    // delay 2 detik agar server MCP tidak overload
    sleep(2);

} while ($skip < $totalData);

echo "\n🎉 Semua data brandKey=$brand selesai diproses.\n";
echo "🆕 Inserted: $countInserted | 🔁 Updated: $countUpdated | Total API data: $totalData\n";

$conn->close();
?>
