<?php
require_once('../config/connect_db.php');

// Ambil parameter filter (POST dari DataTables)
$merk        = $_POST['merk'] ?? '';
$range       = $_POST['range'] ?? [];
$style       = $_POST['style'] ?? [];
$bahan       = $_POST['bahan'] ?? [];
$warna       = $_POST['warna'] ?? [];
$size        = $_POST['size'] ?? [];
$group       = $_POST['group'] ?? '';
$kode_gudang = $_POST['kode_gudang'] ?? '';
$partnumber  = $_POST['partnumber'] ?? '';
//jika default periode bulan ini ditampilkan semua
//$start_date  = $_POST['start_date'] ?? date('Y-m-01');
//$end_date    = $_POST['end_date'] ?? date('Y-m-t');
$start_date  = $_POST['start_date'] ?? '';
$end_date    = $_POST['end_date'] ?? '';

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = explode(';', $aksesGudangCookie);
    $aksesGudangList = array_map('trim', $aksesGudangList);
    $aksesGudangList = array_filter($aksesGudangList);
}

// Helper untuk filter multiple
function buildMultiFilter($field, $values) {
    if (empty($values) || !is_array($values)) return '';
    $safeVals = array_map(function($v) { return "'" . str_replace("'", "''", $v) . "'"; }, $values);
    return " AND $field IN (" . implode(",", $safeVals) . ")";
}

$sql = "
    SELECT 
        a.NoBukti,
        a.TglFaktur,
        f.KodeGudang,
        f.NamaGudang,
        e.KodeLgn,
        e.NamaLgn,
        b.nomor,
        d.KodeItem,
        d.NamaBarang,
        b.qty,
        b.HargaJual,
        b.ItemDisc,
        FORMAT(b.qty, 'N0', 'id-ID') AS QtyFormatted,
        FORMAT(b.HargaJual, 'N0', 'id-ID') AS HargaJualRupiah,
        FORMAT(b.ItemDisc, 'N0', 'id-ID') AS DiskonRupiah,
        d.PartNumber,
        SUBSTRING(d.KodeItem,2,1) AS Range,
        SUBSTRING(d.KodeItem,3,2) AS Style,
        SUBSTRING(d.KodeItem,5,3) AS Bahan,
        SUBSTRING(d.KodeItem,2,8) AS Model,
        SUBSTRING(d.KodeItem,10,2) AS Warna,
        SUBSTRING(d.KodeItem,12,2) AS Size
    FROM SalesInvoiceHeaders a
    JOIN SalesInvoiceItems b ON a.SalesInvoiceHeaderId = b.SalesInvoiceHeaderId
    JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
    JOIN Inventories d ON c.InventoryId = d.InventoryId
    JOIN Customers e ON a.CustomerId = e.CustomerId
    JOIN Warehouses f ON a.KodeGudang = f.KodeGudang
    WHERE 
        e.KodeDept IN ('ELKON','KON') AND 
        a.TglFaktur >= '$start_date' AND a.TglFaktur < DATEADD(day, 1, '$end_date') AND
        b.qty <> 0
";

// Filter tambahan
if ($merk != '') {
    $sql .= " AND LEFT(d.KodeItem, 1) = '" . str_replace("'", "''", $merk) . "'";
}
$sql .= buildMultiFilter("SUBSTRING(d.KodeItem, 2, 1)", $range);
$sql .= buildMultiFilter("SUBSTRING(d.KodeItem, 3, 2)", $style);
$sql .= buildMultiFilter("SUBSTRING(d.KodeItem, 5, 3)", $bahan);
$sql .= buildMultiFilter("SUBSTRING(d.KodeItem, 10, 2)", $warna);
$sql .= buildMultiFilter("SUBSTRING(d.KodeItem, 12, 2)", $size);

if ($group != '') {
    $sql .= " AND e.KodeLgn = '" . str_replace("'", "''", $group) . "'";
}

if ($kode_gudang != '') {
    $sql .= " AND f.KodeGudang = '" . str_replace("'", "''", $kode_gudang) . "'";
}
if ($partnumber != '') {
    $sql .= " AND d.PartNumber LIKE '%" . str_replace("'", "''", $partnumber) . "%'";
}

// Filter gudang berdasarkan cookie
if (!empty($aksesGudangList)) {
    $inClause = "'" . implode("','", $aksesGudangList) . "'";
    $sql .= " AND f.KodeGudang IN ($inClause)";
}

$sql .= " ORDER BY a.TglFaktur DESC";
$result = odbc_exec($conn, $sql);

$data = [];
while ($row = odbc_fetch_array($result)) {
    // Hitung HargaNet = (HargaJual * Qty) - Diskon
    $harga_jual  = (float)str_replace(['.', ','], ['', '.'], $row['HargaJualRupiah']);
    $qty         = (float)str_replace(['.', ','], ['', '.'], $row['QtyFormatted']);
    $diskon      = (float)str_replace(['.', ','], ['', '.'], $row['DiskonRupiah']);

    $harga_net   = ($harga_jual * $qty) - $diskon;
    $harga_net_formatted = number_format($harga_net, 0, ',', '.');

    $data[] = [
        "NoBukti"      => $row['NoBukti'],
        "TglFaktur"    => date("d-m-Y", strtotime($row['TglFaktur'])),
        "Gudang"       => $row['KodeGudang'] . " - " . $row['NamaGudang'],
        "NamaCustomer" => $row['NamaLgn'],
        "KodeItem"     => $row['KodeItem'],
        "NamaBarang"   => $row['NamaBarang'],
        "QtyFormatted" => $row['QtyFormatted'],
        "HargaJual"    => $row['HargaJualRupiah'],
        "Diskon"       => $row['DiskonRupiah'],
        "HargaNet"     => $harga_net_formatted,
        "PartNumber"   => $row['PartNumber'],
        "Range"        => $row['Range'],
        "Style"        => $row['Style'],
        "Bahan"        => $row['Bahan'],
        "Model"        => $row['Model'],
        "Warna"        => $row['Warna'],
        "Size"         => $row['Size']
    ];
}


echo json_encode(["data" => $data]);
