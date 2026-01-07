<?php
require_once('../config/connect_db.php');

/* ===============================
   PARAM BRAND
   =============================== */
$brand = trim($_GET['brand'] ?? '');

$whereBrand = "";
if ($brand !== '') {
    $brand = str_replace("'", "", $brand);
    $whereBrand = "AND KodeItem LIKE '{$brand}%'";
}

/* ===============================
   HEADER DOWNLOAD CSV
   =============================== */
$filename = "stock_current_" . ($brand ?: 'ALL') . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$output = fopen('php://output', 'w');

/* ===============================
   CSV HEADER
   =============================== */
fputcsv($output, [
    'KodeItem',
    'StokGudang',
    'QtySalesOrder',
    'QtyStockRequest',
    'QtyReady'
]);

/* ===============================
   QUERY DATA (TANPA PAGING)
   =============================== */
$sql = "
SELECT
    x.KodeItem,
    SUM(x.StokGudang)       AS StokGudang,
    SUM(x.QtySalesOrder)   AS QtySalesOrder,
    SUM(x.QtyStockRequest) AS QtyStockRequest,
    SUM(x.StokGudang)
      - SUM(x.QtySalesOrder)
      - SUM(x.QtyStockRequest) AS QtyReady
FROM (
    /* STOCK GUDANG */
    SELECT 
        b.KodeItem,
        CAST((a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) AS INT) AS StokGudang,
        0 AS QtySalesOrder,
        0 AS QtyStockRequest
    FROM InventoryStocks a
    INNER JOIN Inventories b ON a.InventoryId = b.InventoryId
    WHERE (a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) <> 0
      AND a.KodeGudang = 'C-G0001'
      $whereBrand

    UNION ALL

    /* SALES ORDER */
    SELECT 
        d.KodeItem,
        0 AS StokGudang,
        0 AS QtySalesOrder,
        0 AS QtyStockRequest
    FROM SalesOrders a
    INNER JOIN SalesOrderItems b ON a.SalesOrderId = b.SalesOrderId
    INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
    INNER JOIN Inventories d ON c.InventoryId = d.InventoryId
    WHERE c.KodeGudang = 'C-G0001'
      $whereBrand
    GROUP BY d.KodeItem

    UNION ALL

    /* STOCK REQUEST */
    SELECT 
        d.KodeItem,
        0 AS StokGudang,
        0 AS QtySalesOrder,
        SUM(b.qty) AS QtyStockRequest
    FROM StockRequests a
    INNER JOIN StockRequestItems b ON a.StockRequestId = b.StockRequestId
    INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
    INNER JOIN Inventories d ON c.InventoryId = d.InventoryId
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
    GROUP BY d.KodeItem
) x
GROUP BY x.KodeItem
ORDER BY x.KodeItem
";

/* ===============================
   EXEC & OUTPUT CSV
   =============================== */
$rs = odbc_exec($conn, $sql);

while ($row = odbc_fetch_array($rs)) {
    fputcsv($output, [
        $row['KodeItem'],
        $row['StokGudang'],
        $row['QtySalesOrder'],
        $row['QtyStockRequest'],
        $row['QtyReady']
    ]);
}

fclose($output);
exit;
