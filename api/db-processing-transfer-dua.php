<?php
//-------------------------------------------------------
// SETTINGS
//-------------------------------------------------------
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

//-------------------------------------------------------
// KONEKSI SQL SERVER (ODBC)
//-------------------------------------------------------
//-------------------------------------------------------
// MYSQL - PRELOAD STORE (INI SUDAH BENAR)
//-------------------------------------------------------
require_once('../config/connect_db.php');

//-------------------------------------------------------
// PARAMETER DATATABLES
//-------------------------------------------------------
$draw   = intval($_GET['draw'] ?? 0);
$start  = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 10);
$search = $_GET['search']['value'] ?? "";
$brandCode = $_GET['brand'] ?? '';
$buktiId = $_GET['buktiId'] ?? '';
$customer = $_GET['customer'] ?? '';
$initial  = $_GET['initial'] ?? '';
$dateFrom = $_GET['dateFrom'] ?? '';
$dateTo   = $_GET['dateTo'] ?? '';

$orderColumnIndex = $_GET['order'][0]['column'] ?? 2;
$orderDir = ($_GET['order'][0]['dir'] ?? "desc") === "asc" ? "ASC" : "DESC";

$columnsAllowed = [
    0 => "ParentTransaction",
    1 => "ParentTransaction",
    2 => "TglEntry",
    3 => "GdgTarget",
    4 => "NamaMerk"
];
$orderBy = $columnsAllowed[$orderColumnIndex] ?? "TglEntry";

//-------------------------------------------------------
// BASE WHERE
//-------------------------------------------------------
//$where = "WHERE a.KodeSumber = 'IN'";

$validGudang = [];

$resGudang = $mysqli->query("
    SELECT initial_maserp_kd_gudang
    FROM yo_store
    WHERE initial_maserp_kd_gudang IS NOT NULL
      AND initial_maserp_kd_gudang <> ''
");

while ($r = $resGudang->fetch_assoc()) {
    $validGudang[] = "'" . $mysqli->real_escape_string($r['initial_maserp_kd_gudang']) . "'";
}

if (empty($validGudang)) {
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ]);
    exit;
}

// jadikan string untuk SQL IN (...)
$validGudangSql = implode(',', $validGudang);


$storeMap = [];

$res = $mysqli->query("
    SELECT initial_maserp_kd_gudang, initial
    FROM yo_store
");
while ($r = $res->fetch_assoc()) {
    $storeMap[$r['initial_maserp_kd_gudang']] = $r['initial'];
}

//-------------------------------------------------------
// AMBIL INVOICE YANG SUDAH DIPOST (SEKALI)
//-------------------------------------------------------
$postedMap = [];

$resPosted = $mysqli->query("
    SELECT DISTINCT
        JSON_UNQUOTE(
            JSON_EXTRACT(payload, '$.details[0].invoice_number')
        ) AS invoice_number
    FROM yo_logs
    WHERE response_code = 200
");

while ($p = $resPosted->fetch_assoc()) {
    if (!empty($p['invoice_number'])) {
        $postedMap[$p['invoice_number']] = true;
    }
}

//-------------------------------------------------------
// BASE WHERE
//-------------------------------------------------------
//$where = "WHERE a.KodeSumber = 'IN'
//          AND g.GdgTarget IN ($validGudangSql)";
$where = [];

$where[] = "a.KodeSumber = 'IN'";
$where[] = "g.GdgTarget IN ($validGudangSql)";

if ($brandCode !== '') {
    $brandCode = str_replace("'", "''", $brandCode);
    $where[] = "f.KodeMerk = '$brandCode'";
}

if ($buktiId !== '') {
    $buktiId = str_replace("'", "''", $buktiId);
    $where[] = "a.ParentTransaction LIKE '%$buktiId%'";
}

if ($customer !== '') {
    $customer = str_replace("'", "''", $customer);
    $where[] = "g.GdgTarget LIKE '%$customer%'";
}

if ($dateFrom !== '') {
    $dateFrom = str_replace("'", "''", $dateFrom);
    $where[] = "CAST(a.TglEntry AS DATE) >= '$dateFrom'";
}

if ($dateTo !== '') {
    $dateTo = str_replace("'", "''", $dateTo);
    $where[] = "CAST(a.TglEntry AS DATE) <= '$dateTo'";
}

$isPosted = intval($_GET['isPosted'] ?? 0);

$postedInvoices = array_keys($postedMap);

if (!empty($postedInvoices)) {

    $escaped = array_map(
        fn($v) => "'" . str_replace("'", "''", $v) . "'",
        $postedInvoices
    );

    $postedSql = implode(',', $escaped);

    if ($isPosted === 1) {
        $where[] = "a.ParentTransaction IN ($postedSql)";
    } else {
        $where[] = "a.ParentTransaction NOT IN ($postedSql)";
    }

} else {
    // 🔥 JIKA BELUM ADA SATUPUN POSTED
    if ($isPosted === 1) {
        // minta data posted → pasti kosong
        $where[] = "1 = 0";
    }
    // kalau isPosted = 0 → tampilkan semua (tidak tambah filter)
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/*if ($search !== "") {
    $search = str_replace("'", "''", $search);
    $where .= "
        AND (
            a.ParentTransaction LIKE '%$search%' OR
            g.GdgTarget LIKE '%$search%' OR
            f.NamaMerk LIKE '%$search%'
        )
    ";
}*/

//debug empty
if (empty($postedInvoices)) {
    error_log('POSTED INVOICES EMPTY');
}

//-------------------------------------------------------
// TOTAL DATA (LEBIH RINGAN)
//-------------------------------------------------------
$countSql = "
SELECT COUNT(*) AS total FROM (
    SELECT a.ParentTransaction
    FROM InventoryTransactions a
    WHERE a.KodeSumber = 'IN'
    GROUP BY a.ParentTransaction
) x
";
$countRes = odbc_exec($conn, $countSql);
$recordsTotal = odbc_fetch_array($countRes)['total'] ?? 0;

//-------------------------------------------------------
// TOTAL FILTERED
//-------------------------------------------------------
$countFilteredSql = "
SELECT COUNT(*) AS total FROM (
    SELECT a.ParentTransaction
    FROM InventoryTransactions a
    JOIN StockRequests g ON g.NoRequest = a.AllNoStockRequest
    JOIN InventoryTransactionItems c ON a.InventoryTransactionId = c.InventoryTransactionId
    JOIN InventoryStocks d ON c.InventoryStockId = d.InventoryStockId
    JOIN Inventories e ON d.InventoryId = e.InventoryId
    JOIN InventoryBrands f ON SUBSTRING(e.KodeItem,1,1) = f.KodeMerk
    $whereSql
    GROUP BY a.ParentTransaction
) x
";
$countFilteredRes = odbc_exec($conn, $countFilteredSql);
$recordsFiltered = odbc_fetch_array($countFilteredRes)['total'] ?? 0;

//-------------------------------------------------------
// QUERY UTAMA (CTE + GROUP BY + PAGINATION)
//-------------------------------------------------------
$sql = "
WITH base AS (
    SELECT
        a.ParentTransaction,
        MAX(a.TglEntry) AS TglEntry,
        MAX(g.GdgTarget) AS GdgTarget,
        MAX(f.NamaMerk) AS NamaMerk
    FROM InventoryTransactions a
    JOIN StockRequests g ON g.NoRequest = a.AllNoStockRequest
    JOIN InventoryTransactionItems c ON a.InventoryTransactionId = c.InventoryTransactionId
    JOIN InventoryStocks d ON c.InventoryStockId = d.InventoryStockId
    JOIN Inventories e ON d.InventoryId = e.InventoryId
    JOIN InventoryBrands f ON SUBSTRING(e.KodeItem,1,1) = f.KodeMerk
    $whereSql
    GROUP BY a.ParentTransaction
)
SELECT *
FROM base
ORDER BY $orderBy $orderDir
OFFSET $start ROWS FETCH NEXT $length ROWS ONLY
";

$result = odbc_exec($conn, $sql);

// 🔥 DEBUG SQL ERROR (WAJIB SAAT TESTING)
if (!$result) {
    ob_clean();
    echo json_encode([
        "error" => odbc_errormsg($conn),
        "sql"   => $sql
    ]);
    exit;
}



$postedInvoices = array_keys($postedMap);

$postedSql = '';
if (!empty($postedInvoices)) {
    $escaped = array_map(
        fn($v) => "'" . str_replace("'", "''", $v) . "'",
        $postedInvoices
    );
    $postedSql = implode(',', $escaped);
}

//-------------------------------------------------------
// GABUNG DATA (FINAL + STATUS POST)
//-------------------------------------------------------
$data = [];

while ($row = odbc_fetch_array($result)) {

    $buktiID = $row['ParentTransaction'];

    $data[] = [
        "BuktiID"      => $buktiID,
        "Tanggal"      => $row['TglEntry'],
        "Customer"     => $row['GdgTarget'],
        "BRAND"        => $row['NamaMerk'],
        "InitialStore" => $storeMap[$row['GdgTarget']] ?? null,

        // 🔥 INI YANG PENTING
        "IsPosted"     => isset($postedMap[$buktiID])
    ];
}

error_log("POSTED COUNT = " . count($postedMap));
error_log("IS POSTED FILTER = " . $isPosted);

$mysqli->close();
odbc_close($conn);

//-------------------------------------------------------
// OUTPUT
//-------------------------------------------------------
ob_clean();
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data" => $data
]);
exit;