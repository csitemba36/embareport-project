<?php
// =====================================
// FIX: Hapus semua output liar
// =====================================
//ob_clean();
ini_set("display_errors", 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

header('Content-Type: application/json; charset=utf-8');
require_once("../config/connect_db.php"); // $mysqli + $conn ODBC

// ===============================
// 1️⃣ Ambil TOKEN dari db_powerone
// ===============================
$tokenData = $mysqli->query("
    SELECT token, tgl_update 
    FROM maserp_api_details 
    WHERE companycode = 'EMB'
    ORDER BY id DESC LIMIT 1
")->fetch_assoc();

$token = $tokenData['token'] ?? '';
$tgl_update = $tokenData['tgl_update'] ?? '';

$tokenExpired = true;
if ($tgl_update) {
    $tokenTime = strtotime($tgl_update);
    $diffHours = (time() - $tokenTime) / 3600;
    if ($diffHours < 12 && $token != '') {
        $tokenExpired = false;
    }
}

// ===============================
// 2️⃣ Jika token kadaluarsa → panggil save_token.php
// ===============================
if ($tokenExpired) {
    $urlToken = "http://localhost/api/maserp_api/save_token.php";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $urlToken,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    $responseToken = curl_exec($ch);  
    $curlError = curl_error($ch);
    $httpTokenCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        ob_end_clean();
        echo json_encode(["error" => "Gagal memanggil save_token.php", "curl_error" => $curlError]);
        exit;
    }

    if ($httpTokenCode !== 200) {
        ob_end_clean();
        echo json_encode(["error" => "save_token.php HTTP $httpTokenCode", "response" => $responseToken]);
        exit;
    }

    // Refresh token
    $tokenRow = $mysqli->query("
        SELECT token FROM maserp_api_details ORDER BY id DESC LIMIT 1
    ")->fetch_assoc();
    $token = $tokenRow['token'] ?? '';
}

// ===============================
// 3️⃣ Ambil nomor bon dari GET
// ===============================
$nomor_bon = $_GET['nomor_bon'] ?? '';
if (!$nomor_bon) {
    ob_end_clean();
    echo json_encode(["error" => "Nomor bon tidak ditemukan"]);
    exit;
}

// ===============================
// 4️⃣ Ambil HEADER dari MySQL
// ===============================
$sqlHeader = "SELECT a.supp_code, a.initial_store, a.store_name, a.nomor_bon, a.created_by, a.sales_date,
                     b.initial_maserp_kd_gudang warehouse_code
              FROM yo_sales_header a
              JOIN yo_store b ON a.initial_store = b.initial
              WHERE a.nomor_bon = ?";
$stmt = $mysqli->prepare($sqlHeader);
$stmt->bind_param("s", $nomor_bon);
$stmt->execute();
$result = $stmt->get_result();
$header = $result->fetch_assoc();
$stmt->close();

if (!$header) {
    ob_end_clean();
    echo json_encode(["error" => "Data header tidak ditemukan"]);
    exit;
}

// ===============================
// 5️⃣ Ambil tambahan dari SQL Server
// ===============================
$kodeGudang = $header['warehouse_code'];
$sqlMasERP = "
    SELECT a.KodeGudang, a.KodeDept, b.kodelgn, b.NamaLgn, b.Alamat1, 
           b.KodeSyarat, b.KodeSales
    FROM warehouses a
    JOIN customers b ON a.CustomerId = b.CustomerId
    WHERE a.KodeGudang = ?
";
$stmtOdbc = odbc_prepare($conn, $sqlMasERP);
odbc_execute($stmtOdbc, [$kodeGudang]);
$maserpData = odbc_fetch_array($stmtOdbc);
odbc_free_result($stmtOdbc);

if (!$maserpData) {
    ob_end_clean();
    echo json_encode(["error" => "Data warehouse/customer tidak ditemukan di MASERP"]);
    exit;
}

// ===============================
// 6️⃣ Ambil DETAIL dari MySQL
// ===============================
$sqlDetail = "SELECT 
                a.brand_name, 

                CASE 
                    WHEN a.brand_name = 'EMBA JEANS' THEN CONCAT('A', a.supplier_barcode)
                    WHEN a.brand_name = 'EMBA CASUAL' THEN CONCAT('B', a.supplier_barcode)
                    ELSE a.supplier_barcode
                END AS supplier_barcode,

                a.art_description, 
                a.sales_qty, 
                a.sales_netto,
                a.sales_bruto,
                a.disc_event,
                a.disc_member,

                (
                    SELECT price 
                    FROM yo_pim 
                    WHERE style_code = 
                        CASE 
                            WHEN a.brand_name = 'EMBA JEANS' THEN CONCAT('A', a.supplier_barcode)
                            WHEN a.brand_name = 'EMBA CASUAL' THEN CONCAT('B', a.supplier_barcode)
                            ELSE a.supplier_barcode
                        END
                    LIMIT 1
                ) AS sales_price

            FROM yo_sales_details a
            WHERE a.nomor_bon = ?";

$stmt = $mysqli->prepare($sqlDetail);
$stmt->bind_param("s", $nomor_bon);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ===============================
// 7️⃣ Build Payload
// ===============================
$payload = [
    "departmentCode" => $maserpData['KodeDept'],
    "projectCode" => "",
    "counterCode" => "ELKON",
    "transactionNumber" => $header['nomor_bon'],
    "transactionDate" => date("c", strtotime($header['sales_date'])),
    "customerCode" => $maserpData['kodelgn'],
    "warehouseCode" => $maserpData['KodeGudang'],
    "paymentTermCode" => $maserpData['KodeSyarat'],
    "customerPurchaseOrderNumber" => "",
    "salesmanCode" => $maserpData['KodeSales'],
    "shippingAddress" => $maserpData['Alamat1'],
    "journalCode" => "1",
    "rate" => 1,
    "extraDiscount1" => 0,
    "extraDiscountPercent1" => 0,
    "extraDiscount2" => 0,
    "extraDiscountPercent2" => 0,
    "ppnType" => "I",
    "kodePpn" => "PPN11",
    "ppnPersen" => 11,
    "taxNumber" => "",
    "taxAdditionalNote" => "07",
    "ppnDate" => date("c", strtotime($header['sales_date'])),
    "pphCode" => "",
    "freightCost" => 0,
    "stamp" => 0,
    "note" => "[PUSH FROM API] ",
    "payType" => "IDR",
    "cannotFillNoFPajak" => true,
    "downPayment" => 0,
    "salesInvoiceItems" => [],
    "salesInvoicePaymentMethods" => []
];

$total = 0;
foreach ($items as $row) {
    $payload["salesInvoiceItems"][] = [
        "usePph" => false,
        "warehouseCode" => $payload["warehouseCode"],
        "rack" => "",
        "itemCode" => $row["supplier_barcode"],
        "itemName" => $row["art_description"],
        "qty" => (int)$row["sales_qty"],
        "unitType" => "1",
        "salesPrice" => ((float)$row['sales_bruto'] - ((float)$row['disc_event'] + (float)$row['disc_member'])) / (int)$row["sales_qty"],
        "discount1" => (float)$row['disc_event'] + (float)$row['disc_member'],
        "discountPercent1" => 0,
        "discount2" => 0,
        "discountPercent2" => 0,
        "discount3" => 0,
        "discountPercent3" => 0,
        "note" => "",
        "batchNumbers" => []
    ];
    $total += (float)$row['sales_bruto'] - ((float)$row['disc_event'] + (float)$row['disc_member']) * $row["sales_qty"];
}

$payload["salesInvoicePaymentMethods"][] = [
    "paymentTypeCode" => "",
    "transactionNumber" => "",
    "cardNumber" => "",
    "amount" => $total
];

// ===============================
// 8️⃣ Kirim ke API MASERP
// ===============================
$curl = curl_init();
$jsonData = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

curl_setopt_array($curl, [
    CURLOPT_URL => 'http://192.168.8.126/api/public/salesInvoice',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $jsonData,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

// ===============================
// 9️⃣ LOG ke MySQL
// ===============================
$logStmt = $mysqli->prepare("
    INSERT INTO yo_logs_sales (nomor_bon, status_code, is_error, error_message, response_json)
    VALUES (?, ?, ?, ?, ?)
");

if ($error) {
    $isError = 1;
    $errMsg = "cURL Error: " . $error;
    $resp = json_encode(["curl_error" => $error]);

    $logStmt->bind_param("siiss", $nomor_bon, $httpcode, $isError, $errMsg, $resp);
    $logStmt->execute();
    $logStmt->close();

    ob_end_clean();
    echo json_encode(["status" => 500, "error" => $error]);
    exit;
}

$isError = ($httpcode !== 200) ? 1 : 0;
$errMsg = $isError ? "HTTP $httpcode" : "";

$logStmt->bind_param("siiss", $nomor_bon, $httpcode, $isError, $errMsg, $response);
$logStmt->execute();
$logStmt->close();

// ===============================
// 10️⃣ Output JSON ke AJAX
// ===============================
ob_end_clean();
echo json_encode([
    "status" => $httpcode,
    "token_expired" => $tokenExpired,
    "token_used" => substr($token, 0, 40) . "...",
    "response" => json_decode($response, true),
    "payload" => $payload
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
