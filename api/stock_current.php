<?php
header('Content-Type: application/json');

$dsn = "dbmaserp";
$username = "db";
$password = "db";

$conn = odbc_connect($dsn, $username, $password);
if (!$conn) {
    die(json_encode(["error" => odbc_errormsg()]));
}

// Terima kode gudang dari GET
$kode_gudang = isset($_GET['kode_gudang']) ? $_GET['kode_gudang'] : '';

if (!$kode_gudang) {
    die(json_encode(["error" => "Kode gudang kosong"]));
}

$sql = "
    SELECT  
        b.KodeItem AS Kode_Item,
        b.NamaBarang AS Nama_Barang,
        b.QtySaldoAwal AS Qty,
        b.PartNumber AS Part_Number,
        c.Nama AS Merk
    FROM 
        InventoryStocks a
    JOIN 
        Inventories b ON a.InventoryId = b.InventoryId
    LEFT JOIN 
        DepartmentBrands c ON LEFT(b.KodeItem, 1) = c.Kode
    WHERE 
        a.KodeGudang = '$kode_gudang'
";

$rs = odbc_exec($conn, $sql);

$data = [];
while ($row = odbc_fetch_array($rs)) {
    $data[] = $row;
}

echo json_encode($data);
?>
