<?php
require_once('../config/connect_db.php'); // koneksi ODBC (SQL Server) & MySQL

header('Content-Type: application/json');

// === Ambil parameter DataTables ===
$draw   = intval($_POST['draw'] ?? 1);
$start  = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$search = $_POST['search']['value'] ?? '';
$orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = $_POST['order'][0]['dir'] ?? 'asc';

// === Mapping kolom DataTables ke SQL ===
$columns = [
    "b.KodeItem", "b.NamaBarang", "b.PartNumber", "c.Nama",
    "a.KodeGudang", "cu.NamaLgn",
    "a.QtySaldoAwal", "a.QtyTerima", "a.QtyKeluar",
    "(a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar)",
    "a.QtyMax", "SUBSTRING(b.KodeItem,2,1)", "SUBSTRING(b.KodeItem,3,2)",
    "SUBSTRING(b.KodeItem,5,3)", "SUBSTRING(b.KodeItem,2,8)",
    "SUBSTRING(b.KodeItem,10,2)", "SUBSTRING(b.KodeItem,12,2)",
    "b.weight", "b.HargaJual"
];
$orderColumn = $columns[$orderColumnIndex] ?? "b.KodeItem";

// === Ambil filter custom dari POST ===
$merk   = $_POST['merk']  ?? '';
$range  = $_POST['range'] ?? [];
$style  = $_POST['style'] ?? [];
$bahan  = $_POST['bahan'] ?? [];
$warna  = $_POST['warna'] ?? [];
$size   = $_POST['size']  ?? [];
$group  = $_POST['group'] ?? '';
$partnumber = $_POST['partnumber'] ?? '';
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = !empty($aksesGudangCookie) ? array_filter(array_map('trim', explode(';', $aksesGudangCookie))) : [];

if (!empty($_POST['kode_gudang'])) {
    $kodeGudang = is_array($_POST['kode_gudang']) ? $_POST['kode_gudang'] : [$_POST['kode_gudang']];
    $kodeGudang = array_filter(array_map('trim', $kodeGudang));
} elseif (!empty($aksesGudangList)) {
    $kodeGudang = $aksesGudangList;
} else {
    $kodeGudang = null;
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
if (!empty($merk)) $whereParts[] = "LEFT(b.KodeItem, 1) = '" . str_replace("'", "''", $merk) . "'";
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 2, 1)", $range), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 3, 2)", $style), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 5, 3)", $bahan), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 10, 2)", $warna), ' AND');
$whereParts[] = ltrim(buildMultiFilter("SUBSTRING(b.KodeItem, 12, 2)", $size), ' AND');

if (!empty($group) && $group !== 'GUDANG') {
    $whereParts[] = "cu.KodeLgn = '" . str_replace("'", "''", $group) . "'";
}
if (!is_null($kodeGudang) && count($kodeGudang) > 0) {
    if (!empty($aksesGudangList)) {
        $kodeGudang = array_intersect($kodeGudang, $aksesGudangList);
    }
    if (!empty($kodeGudang)) {
        $escapedGudang = array_map(function($v){ return "'" . str_replace("'", "''", $v) . "'"; }, $kodeGudang);
        $whereParts[] = "a.KodeGudang IN (" . implode(',', $escapedGudang) . ")";
    }
}

if (!empty($partnumber)) {
    $whereParts[] = "b.PartNumber = '" . str_replace("'", "''", $partnumber) . "'";
}
if (!empty($search)) {
    $search = str_replace("'", "''", $search);
    $whereParts[] = "(b.NamaBarang LIKE '%$search%' OR b.PartNumber LIKE '%$search%' OR b.KodeItem LIKE '%$search%')";
}
$whereParts[] = "b.typestock = 'INV'";

$whereClause = 'WHERE ' . implode(' AND ', array_filter($whereParts));

// ============================
// Hitung total records
// ============================
$sqlCount = "SELECT COUNT(*) AS cnt
FROM InventoryStocks a
JOIN Inventories b ON a.InventoryId = b.InventoryId
LEFT JOIN DepartmentBrands c ON LEFT(b.KodeItem,1) = c.Kode
LEFT JOIN Warehouses w ON a.KodeGudang = w.KodeGudang
LEFT JOIN Customers cu ON w.CustomerId = cu.CustomerId
$whereClause";

$stmtCount = odbc_exec($conn, $sqlCount);
$totalFiltered = ($row = odbc_fetch_array($stmtCount)) ? (int)$row['cnt'] : 0;

$sqlData = "
SELECT *
FROM (
    SELECT  
        SUBSTRING(b.KodeItem,1,13) AS KodeItem,
        b.NamaBarang, b.PartNumber, c.Nama AS NamaBrand,
        a.KodeGudang, a.KodeGudang + ' - ' + w.NamaGudang AS Gudang,
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
        a.QtyMax, b.HargaJual, b.weight,
        ROW_NUMBER() OVER (ORDER BY $orderColumn $orderDir) AS RowNum
    FROM InventoryStocks a
    JOIN Inventories b ON a.InventoryId = b.InventoryId
    LEFT JOIN DepartmentBrands c ON LEFT(b.KodeItem,1) = c.Kode
    LEFT JOIN Warehouses w ON a.KodeGudang = w.KodeGudang
    LEFT JOIN Customers cu ON w.CustomerId = cu.CustomerId
    $whereClause
) AS Temp
WHERE RowNum BETWEEN 1 AND 20";

//echo $sqlData;

$stmtData = odbc_exec($conn, $sqlData);

// ============================
// Ambil mapping gudang dari MySQL
// ============================
$mapGudang = [];
$resMy = $mysqli->query("SELECT * FROM m_wilayah");
while ($g = $resMy->fetch_assoc()) {
    $mapGudang[$g['kode_gudang']] = $g;
}

// ============================
// Gabungkan hasil
// ============================
$data = [];
while ($row = odbc_fetch_array($stmtData)) {
    $kodeG = $row['KodeGudang'];
    $wilayahData = $mapGudang[$kodeG] ?? [
        "wilayah" => "", "area" => "", "kota" => "",
        "direktur_operasional" => "", "regional_manager" => "",
        "manager" => "", "supervisor" => "", "kode_gudang" => ""
    ];

    $data[] = array_merge([
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
        'HargaJual'     => 'Rp ' . number_format($row['HargaJual'], 0, ',', '.'),
        'Weight'        => $row['weight'],
    ], $wilayahData);
}

// ============================
// Response JSON DataTables
// ============================
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => 0,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);
 