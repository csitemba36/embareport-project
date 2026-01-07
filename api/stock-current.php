<?php
header('Content-Type: application/json');

require_once('../config/connect_db.php');

if (!$conn) {
    echo json_encode(["data" => [], "error" => odbc_errormsg()]);
    exit;
}

$merk = isset($_GET['merk']) ? $_GET['merk'] : '';
$kode_gudang = isset($_GET['kode_gudang']) ? $_GET['kode_gudang'] : '';

if (!$merk) {
    echo json_encode(["data" => []]);
    exit;
}

$sql = "
    SELECT DISTINCT 
        SUBSTRING(b.KodeItem, 1, 11) AS KodeItem,
		b.PartNumber AS PartNumber
    FROM 
        InventoryStocks a
    JOIN 
        Inventories b ON a.InventoryId = b.InventoryId
    LEFT JOIN 
        DepartmentBrands c ON LEFT(b.KodeItem, 1) = c.Kode
    WHERE 
        LEN(b.KodeItem) = 13
        AND LEFT(b.KodeItem, 1) = '$merk'
        AND (a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) <> 0
";

// Tambahkan filter gudang jika bukan 'ALL'
if ($kode_gudang !== 'ALL') {
    $sql .= " AND a.KodeGudang = '$kode_gudang'";
}

$rs = odbc_exec($conn, $sql);

$data = [];
while ($row = odbc_fetch_array($rs)) {
    $data[] = $row;
}

echo json_encode(["data" => $data]);