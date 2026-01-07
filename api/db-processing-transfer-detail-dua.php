<?php
header('Content-Type: application/json');

// ================= SQL SERVER =================// ================= MYSQL =================
require_once __DIR__ . "/../config/connect_db.php";

if (!isset($_GET['buktiID'])) {
    echo json_encode([]);
    exit;
}

$buktiID = $_GET['buktiID'];

$sql = "
SELECT 
    f.KodeMerk,

    e.KodeItem AS KodeItemFull,
    SUBSTRING(e.KodeItem, 2, LEN(e.KodeItem)) AS KodeItem,

    c.Qty AS QtyJual,

    -- 🔥 HARGA DARI ITEM INDUK
    p.HargaJual
FROM InventoryTransactions a
JOIN InventoryTransactionItems c 
    ON a.InventoryTransactionId = c.InventoryTransactionId
JOIN InventoryStocks d 
    ON c.InventoryStockId = d.InventoryStockId 
JOIN Inventories e 
    ON d.InventoryId = e.InventoryId 
JOIN InventoryBrands f 
    ON SUBSTRING(e.KodeItem,1,1) = f.KodeMerk

OUTER APPLY (
    SELECT TOP 1 HargaJual
    FROM Inventories i
    WHERE i.KodeItem = LEFT(e.KodeItem, 9)
      AND i.HargaJual > 0
) p

WHERE a.ParentTransaction = '$buktiID'
  AND c.Qty > 0
ORDER BY e.KodeItem;
";

$result = odbc_exec($conn, $sql);



$stmt = $mysqli->prepare("
    SELECT price 
    FROM yo_pim 
    WHERE style_code = ?
    LIMIT 1
");

$data = [];

while ($row = odbc_fetch_array($result)) {

    /* ===============================
       FORMAT HARGA JUAL MASERP
       =============================== */
    $row['HargaJual'] = number_format(
        (float)$row['HargaJual'],
        0,
        ',',
        '.'
    );

    /* ===============================
       DEFAULT PRICE POWERONE
       =============================== */
    $row['PricePowerOne'] = null;

    /* ===============================
       LOOKUP PRICE POWERONE
       PAKAI KODE FULL !!!
       =============================== */
    if (!empty($row['KodeItemFull'])) {

        $stmt->bind_param("s", $row['KodeItemFull']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($mysqlRow = $res->fetch_assoc()) {
            $row['PricePowerOne'] = number_format(
                (float)$mysqlRow['price'],
                0,
                ',',
                '.'
            );
        }
    }

    $data[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode($data);
exit;