<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "db_powerone_mcp");
if ($mysqli->connect_errno) {
    echo json_encode(["error" => $mysqli->connect_error]);
    exit;
}

$transactNo = $_POST['transact_no'] ?? '';
$storeNo    = $_POST['store_no'] ?? '';
$dept       = $_POST['dept'] ?? '';

//echo json_encode(['status' => true, 'message' => 'Parameter', 'transact_no' => $transactNo, 'store_no' => $storeNo, 'dept' => $dept]);
//exit;

if (!$transactNo || !$storeNo) {
    echo json_encode(['status' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

/* ===============================
   AMBIL HEADER
   =============================== */
$sqlHeader = "
    SELECT *
    FROM sales_transaction_header
    WHERE TRANSACT_NO = ? AND STORE_NO = ? AND DEPT = ?
";
$stmt = $mysqli->prepare($sqlHeader);
$stmt->bind_param('sss', $transactNo, $storeNo, $dept);
$stmt->execute();
$header = $stmt->get_result()->fetch_assoc();

if (!$header) {
    echo json_encode(['status' => false, 'message' => 'Header tidak ditemukan']);
    exit;
}

/* ===============================
   AMBIL DETAIL
   =============================== */
$sqlDetail = "
    SELECT a.*, b.kode_maserp
    FROM sales_transaction_header a,
         mcp_stores b
    WHERE a.TRANSACT_NO = ? AND a.STORE_NO = ? AND a.DEPT = ? AND
          a.STORE_NO = b.store_id
    ORDER BY TRANSACT_LINE_NO
";
$stmt = $mysqli->prepare($sqlDetail);
$stmt->bind_param('sss', $transactNo, $storeNo, $dept);
$stmt->execute();
$details = $stmt->get_result();

/* ===============================
   SUSUN ITEMS
   =============================== */
$salesItems = [];

while ($d = $details->fetch_assoc()) {

    $salesItems[] = [
        "usePph" => false,
        "warehouseCode" => $d['STORE_NO'],
        "rack" => "",
        "itemCode" => $d['BARCODE'],
        "itemName" => $d['ITEM'],
        "batchNumber" => "",
        "qty" => (int)$d['QTY'],
        "unitType" => "PCS",
        "salesPrice" => (float)$d['ITEM_PRICE'],
        "discount1" => (float)$d['DISC_AUTO'],
        "discountPercent1" => 0,
        "discount2" => (float)$d['DISC_PROMO'],
        "discountPercent2" => 0,
        "discount3" => (float)$d['DISC_EMPLOYEE'],
        "discountPercent3" => 0,
        "note" => "",
        "batchNumbers" => []
    ];
}

/* ===============================
   SUSUN JSON MASERP
   =============================== */
$json = [
    "departmentCode" => 'KON',
    "projectCode" => "",
    "counterCode" => $header['POS_NO'],
    "transactionNumber" => $header['TRANSACT_NO'],
    "transactionDate" => date('c', strtotime($header['TRANSACT_DATE'])),
    "customerCode" => "CASH",
    "warehouseCode" => $header['STORE_NO'],
    "paymentTermCode" => "CASH",
    "customerPurchaseOrderNumber" => "",
    "salesmanCode" => "",
    "shippingAddress" => $header['STORE_NAME'],
    "journalCode" => "SI",
    "rate" => 1,
    "extraDiscount1" => 0,
    "extraDiscountPercent1" => 0,
    "extraDiscount2" => 0,
    "extraDiscountPercent2" => 0,
    "ppnType" => "NON",
    "kodePpn" => "",
    "ppnPersen" => 0,
    "taxNumber" => "",
    "taxAdditionalNote" => "",
    "ppnDate" => date('c', strtotime($header['TRANSACT_DATE'])),
    "pphCode" => "",
    "freightCost" => 0,
    "stamp" => 0,
    "note" => "Sales Invoice from MCP",
    "payType" => "CASH",
    "cannotFillNoFPajak" => true,
    "downPayment" => 0,
    "salesInvoiceItems" => $salesItems,
    "salesInvoicePaymentMethods" => [
        [
            "paymentTypeCode" => "CASH",
            "transactionNumber" => $header['TRANSACT_NO'],
            "cardNumber" => "",
            "amount" => (float)$header['TOTAL_NET']
        ]
    ]
];

//echo json_encode($json, JSON_PRETTY_PRINT);
echo json_encode(['status' => true, 'message' => 'Data JSON', 'data' => $json]);
