<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php');

/* ===============================
   PARAM DATATABLES
   =============================== */
$draw   = intval($_POST['draw'] ?? 1);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$brand  = trim($_POST['brand'] ?? '');
$search = trim($_POST['search']['value'] ?? '');

/* ===============================
   FILTER BRAND
   =============================== */
$whereBrand = "";
if ($brand !== '') {
    $brand = str_replace("'", "", $brand);
    $whereBrand = "AND b.KodeItem LIKE '{$brand}%'"; // Gunakan alias b untuk Inventories
}

$whereBrandUnion = "";
if ($brand !== '') {
    $whereBrandUnion = "AND d.KodeItem LIKE '{$brand}%'"; // Gunakan alias d untuk Inventories di Union
}

/* ===============================
   FILTER SEARCH (KodeItem saja)
   =============================== */
$whereSearch = "";
if ($search !== '') {
    $search = str_replace("'", "", $search);
    $whereSearch = "AND t.KodeItem LIKE '%{$search}%'";
}

/* ===============================
   PAGING HANYA SAAT SEARCH KOSONG
   =============================== */
$wherePaging = "";
if ($search === '') {
    $wherePaging = "AND t.rn BETWEEN " . ($start + 1) . " AND " . ($start + $length);
}

/* ===============================
   QUERY DATA (DITAMBAH ORDER BY DI AKHIR)
   =============================== */
$sql = "
SELECT *
FROM (
    SELECT
        x.KodeItem,
        SUM(x.StokGudang)       AS StokGudang,
        SUM(x.QtySalesOrder)   AS QtySalesOrder,
        SUM(x.QtyStockRequest) AS QtyStockRequest,
        SUM(x.StokGudang)
          - SUM(x.QtySalesOrder)
          - SUM(x.QtyStockRequest) AS QtyReady,
        ROW_NUMBER() OVER (ORDER BY x.KodeItem ASC) AS rn
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
            0, 0, 0
        FROM SalesOrders a
        INNER JOIN SalesOrderItems b ON a.SalesOrderId = b.SalesOrderId
        INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
        INNER JOIN Inventories d ON c.InventoryId = d.InventoryId
        WHERE c.KodeGudang = 'C-G0001'
          $whereBrandUnion
        GROUP BY d.KodeItem

        UNION ALL

        /* STOCK REQUEST */
        SELECT 
            d.KodeItem,
            0, 0,
            SUM(b.qty)
        FROM StockRequests a
        INNER JOIN StockRequestItems b ON a.StockRequestId = b.StockRequestId
        INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
        INNER JOIN Inventories d ON c.InventoryId = d.InventoryId
        WHERE c.KodeGudang = 'C-G0001'
          AND a.IsClosedManually = 0
          AND a.IsClosed <> 1
          AND a.GdgTarget IS NOT NULL
          $whereBrandUnion
          AND NOT EXISTS (
              SELECT 1
              FROM InventoryTransactions trans
              WHERE trans.AllNoStockRequest = a.NoRequest
                AND trans.KodeSumber = 'IN'
          )
        GROUP BY d.KodeItem
    ) x
    GROUP BY x.KodeItem
) t
WHERE 1=1
$whereSearch
$wherePaging
ORDER BY t.KodeItem ASC
";

/* ===============================
   EXEC DATA
   =============================== */
$rs = odbc_exec($conn, $sql);
$data = [];
while ($row = odbc_fetch_array($rs)) {
    $data[] = $row;
}

/* ===============================
   RECORDS TOTAL (TANPA SEARCH)
   =============================== */
$totalSql = "
SELECT COUNT(*) FROM (
    SELECT x.KodeItem
    FROM (
        SELECT b.KodeItem
        FROM InventoryStocks a
        INNER JOIN Inventories b ON a.InventoryId = b.InventoryId
        WHERE a.KodeGudang = 'C-G0001'
        $whereBrand

        UNION ALL

        SELECT d.KodeItem
        FROM SalesOrders a
        INNER JOIN SalesOrderItems b ON a.SalesOrderId = b.SalesOrderId
        INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
        INNER JOIN Inventories d ON c.InventoryId = d.InventoryId
        WHERE c.KodeGudang = 'C-G0001'
        $whereBrandUnion

        UNION ALL

        SELECT d.KodeItem
        FROM StockRequests a
        INNER JOIN StockRequestItems b ON a.StockRequestId = b.StockRequestId
        INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
        INNER JOIN Inventories d ON c.InventoryId = d.InventoryId
        WHERE c.KodeGudang = 'C-G0001'
          AND a.IsClosedManually = 0
          AND a.IsClosed <> 1
          AND a.GdgTarget IS NOT NULL
          $whereBrandUnion
    ) x
    GROUP BY x.KodeItem
) t
";
$rsTotal = odbc_exec($conn, $totalSql);
odbc_fetch_row($rsTotal);
$recordsTotal = odbc_result($rsTotal, 1);

/* ===============================
   RECORDS FILTERED
   =============================== */
if ($search !== '') {
    // Jika ada search, hitung ulang jumlah record yang sesuai search
    $filteredCountSql = "
    SELECT COUNT(*) FROM (
        SELECT x.KodeItem
        FROM (
            /* Subquery UNION yang sama dengan di atas agar filter brand tetap jalan */
            SELECT b.KodeItem FROM InventoryStocks a INNER JOIN Inventories b ON a.InventoryId = b.InventoryId WHERE a.KodeGudang = 'C-G0001' $whereBrand
            UNION ALL
            SELECT d.KodeItem FROM SalesOrders a INNER JOIN SalesOrderItems b ON a.SalesOrderId = b.SalesOrderId INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId INNER JOIN Inventories d ON c.InventoryId = d.InventoryId WHERE c.KodeGudang = 'C-G0001' $whereBrandUnion
            UNION ALL
            SELECT d.KodeItem FROM StockRequests a INNER JOIN StockRequestItems b ON a.StockRequestId = b.StockRequestId INNER JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId INNER JOIN Inventories d ON c.InventoryId = d.InventoryId WHERE c.KodeGudang = 'C-G0001' AND a.IsClosedManually = 0 AND a.IsClosed <> 1 AND a.GdgTarget IS NOT NULL $whereBrandUnion
        ) x
        WHERE x.KodeItem LIKE '%{$search}%'
        GROUP BY x.KodeItem
    ) t";
    $rsFiltered = odbc_exec($conn, $filteredCountSql);
    odbc_fetch_row($rsFiltered);
    $recordsFiltered = odbc_result($rsFiltered, 1);
} else {
    $recordsFiltered = $recordsTotal;
}

/* ===============================
   RESPONSE DATATABLES
   =============================== */
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => (int)$recordsTotal,
    "recordsFiltered" => (int)$recordsFiltered,
    "data" => $data
]);