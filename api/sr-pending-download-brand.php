<?php
require_once('../config/connect_db.php');

/* ===============================
   PARAM
   =============================== */
$brand = trim($_GET['brand'] ?? '');

$whereBrand = "";
if ($brand !== '') {
    $brand = str_replace("'", "", $brand);
    $whereBrand = "AND d.KodeItem LIKE '{$brand}%'";
}

/* ===============================
   HEADER DOWNLOAD CSV
   =============================== */
$filename = "stock_request_detail_" . ($brand ?: 'ALL') . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$output = fopen('php://output', 'w');

/* ===============================
   CSV HEADER
   =============================== */
fputcsv($output, [
    'NoRequest',
	'TglRequest',
    'KodeGudang',
	'GudangTarget',
    'KodeItem',
    'Qty'
]);

/* ===============================
   QUERY (SESUAI QUERY ANDA)
   =============================== */
$sql = "
SELECT  
    a.NoRequest,
	a.TglRequest,
    a.KodeGudang,
	a.GdgTarget,
    d.KodeItem,
    b.qty
FROM StockRequests a
INNER JOIN StockRequestItems b 
    ON a.StockRequestId = b.StockRequestId
INNER JOIN InventoryStocks c 
    ON b.InventoryStockId = c.InventoryStockId
INNER JOIN Inventories d 
    ON c.InventoryId = d.InventoryId
WHERE c.KodeGudang = 'C-G0001'
  AND a.IsClosedManually = 0
  AND a.IsClosed <> 1
  AND a.GdgTarget IS NOT NULL
  $whereBrand
  AND NOT EXISTS (
        SELECT 1
        FROM InventoryTransactions trans
        WHERE trans.AllNoStockRequest = a.NoRequest
          AND trans.KodeSumber = 'IN'
  )
ORDER BY a.NoRequest, d.KodeItem
";

/* ===============================
   EXEC & OUTPUT
   =============================== */
$rs = odbc_exec($conn, $sql);
if (!$rs) {
    echo odbc_errormsg($conn);
    exit;
}

while ($row = odbc_fetch_array($rs)) {
    fputcsv($output, [
        $row['NoRequest'],
		$row['TglRequest'],
        $row['KodeGudang'],
		$row['GdgTarget'],
        $row['KodeItem'],
        $row['qty']
    ]);
}

fclose($output);
exit;
