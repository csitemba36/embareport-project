<?php
require_once('../config/connect_db.php');

// ==========================================
// VALIDASI MERK
// ==========================================
$merk = $_GET['brand'] ?? '';

if (empty($merk)) {
    die("Merk tidak boleh kosong");
}

$merkSafe = str_replace("'", "''", $merk);

// ==========================================
// AKSES GUDANG DARI COOKIE
// ==========================================
$userRoleCode = $_COOKIE['userrolecode'] ?? '';
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];


if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

// ==========================================
// BUILD WHERE
// ==========================================
$whereConditions = [];

// wajib filter merk
$whereConditions[] = "LEFT(b.KodeItem,1) = '$merkSafe'";



if ($userRoleCode === 'WHSPV') {

    // KHUSUS SUPERVISOR WAREHOUSE
    $whereConditions[] = "w.KodeDept IN ('PST','ECOM')";

} else {

    // USER BIASA → FILTER GUDANG DARI COOKIE (JIKA ADA)
    if (!empty($aksesGudangList)) {
        $escapedGudang = array_map(function ($v) {
            return "'" . str_replace("'", "''", $v) . "'";
        }, $aksesGudangList);

        $whereConditions[] = "a.KodeGudang IN (" . implode(',', $escapedGudang) . ")";
    }
}


// gabungkan kondisi
$whereSQL = implode(" AND ", $whereConditions);

// ==========================================
// QUERY SQL SERVER
// ==========================================
$sql = "
SELECT  
    SUBSTRING(b.KodeItem,1,13) AS KodeItem,
    b.NamaBarang,
    b.PartNumber,
    c.Nama AS NamaBrand,
    a.KodeGudang + ' - ' + w.NamaGudang AS Gudang,
    cu.NamaLgn AS NamaCustomer,
    CAST(a.QtySaldoAwal AS INT) AS QtySaldoAwal,
    CAST(a.QtyTerima AS INT) AS QtyTerima,
    CAST(a.QtyKeluar AS INT) AS QtyKeluar,
    CAST((a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) AS INT) AS SaldoAkhir,
    SUBSTRING(b.KodeItem,2,1) AS Range,
    SUBSTRING(b.KodeItem,3,2) AS Style,
    SUBSTRING(b.KodeItem,5,3) AS Bahan,
    SUBSTRING(b.KodeItem,2,8) AS Model,
    SUBSTRING(b.KodeItem,10,2) AS Warna,
    SUBSTRING(b.KodeItem,12,2) AS Size,
    a.QtyMax,
    b.HargaJual,
    b.Weight
FROM InventoryStocks a
JOIN Inventories b ON a.InventoryId = b.InventoryId
LEFT JOIN DepartmentBrands c ON LEFT(b.KodeItem,1) = c.Kode
LEFT JOIN Warehouses w ON a.KodeGudang = w.KodeGudang
LEFT JOIN Customers cu ON w.CustomerId = cu.CustomerId
WHERE LEN(b.KodeItem) = 13
  AND (a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) <> 0
  AND $whereSQL
ORDER BY b.KodeItem
";

$stmt = odbc_exec($conn, $sql);
if (!$stmt) {
    die("Query gagal dieksekusi.");
}

// ==========================================
// OUTPUT TXT
// ==========================================
$filename = "StockCurrent_" . $merk . "_" . date("Ymd_His") . ".txt";

header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: no-cache, must-revalidate");

// header kolom
$headers = [
    'KodeItem','NamaBarang','PartNumber','NamaBrand','Gudang','NamaCustomer',
    'QtySaldoAwal','QtyTerima','QtyKeluar','SaldoAkhir',
    'Range','Style','Bahan','Model','Warna','Size',
    'QtyMax','HargaJual','Weight'
];

echo implode("\t", $headers) . "\n";

// data
while ($row = odbc_fetch_array($stmt)) {
    $lineData = [];
    foreach ($headers as $field) {
        $lineData[] = $row[$field] ?? "";
    }
    echo implode("\t", $lineData) . "\n";
}

exit;
?>
