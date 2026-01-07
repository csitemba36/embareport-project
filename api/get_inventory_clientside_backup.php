<?php
require_once('../config/connect_db.php'); // koneksi ODBC

header('Content-Type: application/json');

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

// === Ambil filter dari POST ===
$merk   = $_POST['merk']  ?? '';
$range  = $_POST['range'] ?? [];
$style  = $_POST['style'] ?? [];
$bahan  = $_POST['bahan'] ?? [];
$warna  = $_POST['warna'] ?? [];
$size   = $_POST['size']  ?? [];
$group  = $_POST['group']  ?? '';

// === Ambil kode gudang: POST > Cookie > Semua ===
if (!empty($_POST['kode_gudang'])) {
    $kodeGudang = is_array($_POST['kode_gudang']) ? $_POST['kode_gudang'] : [$_POST['kode_gudang']];
    $kodeGudang = array_filter(array_map('trim', $kodeGudang));
} elseif (!empty($aksesGudangList)) {
    $kodeGudang = $aksesGudangList;
} else {
    $kodeGudang = null; // tampilkan semua gudang
}

// === Cegah load awal tanpa merk ===
if (empty($merk)) {
    echo json_encode([]);
    exit;
}

// === Helper multi filter ===
function buildMultiFilter($field, $values) {
    if (!empty($values)) {
        $escaped = array_map(function($v){ return "'" . str_replace("'", "''", $v) . "'"; }, $values);
        return " AND $field IN (" . implode(',', $escaped) . ")";
    }
    return '';
}

// ============================
// Bangun WHERE clause
// ============================
$whereParts = [];
$whereParts[] = "LEN(b.KodeItem) = 13 AND (a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) <> 0";
$whereParts[] = "LEFT(b.KodeItem, 1) = '" . str_replace("'", "''", $merk) . "'";
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 2, 1)", $range), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 3, 2)", $style), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 5, 3)", $bahan), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 10, 2)", $warna), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 12, 2)", $size), ' AND');

// ============================
// Filter group customer
// ============================
if (!empty($group) && $group !== 'GUDANG') {
    $whereParts[] = "cu.KodeLgn = '" . str_replace("'", "''", $group) . "'";
}

// ============================
// Filter gudang
// ============================
if (!is_null($kodeGudang) && count($kodeGudang) > 0) {
    // pastikan kodeGudang ada di aksesGudangList jika cookie ada
    if (!empty($aksesGudangList)) {
        $kodeGudang = array_intersect($kodeGudang, $aksesGudangList);
    }
    if (!empty($kodeGudang)) {
        $escapedGudang = array_map(function($v){ 
            return "'" . str_replace("'", "''", $v) . "'"; 
        }, $kodeGudang);
        $whereParts[] = "a.KodeGudang IN (" . implode(',', $escapedGudang) . ")";
    }
}
// Kalau cookie kosong + POST gudang kosong → tidak tambahkan filter gudang


$whereClause = 'WHERE ' . implode(' AND ', array_filter($whereParts));

// ============================
// Ambil data
// ============================
$sqlData = "
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
    b.HargaJual
FROM InventoryStocks a
JOIN Inventories b ON a.InventoryId = b.InventoryId
LEFT JOIN DepartmentBrands c ON LEFT(b.KodeItem,1) = c.Kode
LEFT JOIN Warehouses w ON a.KodeGudang = w.KodeGudang
LEFT JOIN Customers cu ON w.CustomerId = cu.CustomerId
$whereClause
ORDER BY b.KodeItem
";

//echo $sqlData;

$stmtData = odbc_exec($conn, $sqlData);

$data = [];
while ($row = odbc_fetch_array($stmtData)) {
    $data[] = [
        'KodeItem'      => $row['KodeItem'],
        'NamaBarang'    => $row['NamaBarang'],
        'PartNumber'    => $row['PartNumber'],
        'NamaBrand'     => $row['NamaBrand'],
        'Gudang'        => $row['Gudang'],
        'NamaCustomer'  => $row['NamaCustomer'],
        'QtySaldoAwal'  => (int)$row['QtySaldoAwal'],
        'QtyTerima'     => (int)$row['QtyTerima'],
        'QtyKeluar'     => (int)$row['QtyKeluar'],
        'SaldoAkhir'    => (int)$row['SaldoAkhir'],
        'Range'         => $row['Range'],
        'Style'         => $row['Style'],
        'Bahan'         => $row['Bahan'],
        'Model'         => $row['Model'],
        'Warna'         => $row['Warna'],
        'Size'          => $row['Size'],
        'QtyMax'        => (int)$row['QtyMax'],
        'HargaJual' => 'Rp ' . number_format($row['HargaJual'], 0, ',', '.')
    ];
}


echo json_encode($data);
