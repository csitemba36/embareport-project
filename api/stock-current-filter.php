<?php
header('Content-Type: application/json');

require_once('../config/connect_db.php');

if (!$conn) {
    echo json_encode(["data" => [], "error" => odbc_errormsg()]);
    exit;
}

$merk = isset($_GET['merk']) ? $_GET['merk'] : '';
$kode_gudang = isset($_GET['kode_gudang']) ? $_GET['kode_gudang'] : '';
$kode_item = isset($_GET['kode_item']) ? $_GET['kode_item'] : ''; // <- tambahkan

if (!$merk) {
    echo json_encode(["data" => []]);
    exit;
}

$sql = "
		SELECT  
			SUBSTRING(b.KodeItem, 1, 13) AS KodeItem, 
			b.NamaBarang AS NamaBarang,
			b.PartNumber AS PartNumber,
			c.Nama AS NamaBrand,
			a.KodeGudang + ' - ' + w.NamaGudang AS Gudang,
			cu.NamaLgn AS NamaCustomer, 
			CAST(a.QtySaldoAwal AS INT) AS QtySaldoAwal,
			CAST(a.QtyTerima AS INT) AS QtyTerima,
			CAST(a.QtyKeluar AS INT) AS QtyKeluar,
			CAST((a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) AS INT) AS SaldoAkhir,
			SUBSTRING(b.KodeItem, 2, 1) AS Range,         
			SUBSTRING(b.KodeItem, 3, 2) AS Style, 
			SUBSTRING(b.KodeItem, 5, 3) AS Bahan, 
			SUBSTRING(b.KodeItem, 2, 8) AS Model,        
			SUBSTRING(b.KodeItem, 10,2) AS Warna,        
			SUBSTRING(b.KodeItem, 12, 13) AS Size,
			a.QtyMax
		FROM 
			InventoryStocks a
		JOIN 
			Inventories b ON a.InventoryId = b.InventoryId
		LEFT JOIN 
			DepartmentBrands c ON LEFT(b.KodeItem, 1) = c.Kode
		LEFT JOIN 
			Warehouses w ON a.KodeGudang = w.KodeGudang
		LEFT JOIN 
			Customers cu ON w.CustomerId = cu.CustomerId
		WHERE 
			LEN(b.KodeItem) = 13
			AND LEFT(b.KodeItem, 1) = '$merk'
			AND (a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) <> 0

";


// Filter gudang jika bukan ALL
if ($kode_gudang !== 'ALL') {
    $sql .= " AND a.KodeGudang = '$kode_gudang'";
}

// Filter kode item jika diisi
if (!empty($kode_item)) {
    $sql .= " AND LEFT(b.KodeItem, 11) = '$kode_item'";
}

$rs = odbc_exec($conn, $sql);

$data = [];
while ($row = odbc_fetch_array($rs)) {
    $data[] = $row;
}

echo json_encode(["data" => $data]);
