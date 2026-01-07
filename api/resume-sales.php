<?php
require_once('../config/connect_db.php'); // koneksi ke SQL Server

$brand      = $_GET['brand'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-t');
$group      = $_GET['group'] ?? '';
$kodegudang = $_GET['kodegudang'] ?? '';

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

$sql = "
    SELECT 
        f.KodeGudang,
        f.NamaGudang,
        e.NamaLgn,
        SUM(b.qty) AS TotalQty,
        SUM(b.HargaJual) AS TotalHargaJual,
        SUM(b.ItemDisc) AS TotalItemDisc,
        FORMAT(SUM(b.qty), 'N0', 'id-ID') AS TotalQtyFormatted,
        FORMAT(SUM(b.HargaJual), 'N0', 'id-ID') AS TotalHargaJualRupiah,
        FORMAT(SUM(b.ItemDisc), 'N0', 'id-ID') AS TotalItemDiscRupiah
    FROM SalesInvoiceHeaders a
    JOIN SalesInvoiceItems b ON a.SalesInvoiceHeaderId = b.SalesInvoiceHeaderId
    JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
    JOIN Inventories d ON c.InventoryId = d.InventoryId
    JOIN Customers e ON a.CustomerId = e.CustomerId
    JOIN Warehouses f ON a.KodeGudang = f.KodeGudang
    WHERE 
        e.KodeDept IN ('ELKON','KON') 
        AND a.TglFaktur >= '$start_date' 
        AND a.TglFaktur < DATEADD(day, 1, '$end_date') 
        AND b.qty <> 0
";

// === Filter gudang berdasarkan cookie (kalau ada) ===
if (!empty($aksesGudangList)) {
    $escapedGudang = array_map(function ($g) {
        return "'" . str_replace("'", "''", $g) . "'";
    }, $aksesGudangList);

    $sql .= " AND f.KodeGudang IN (" . implode(",", $escapedGudang) . ")";
}

// === Filter gudang berdasarkan dropdown (kalau diisi) ===
if (!empty($kodegudang)) {
    $sql .= " AND f.KodeGudang = '" . str_replace("'", "''", $kodegudang) . "'";
}

// === Filter brand ===
if (!empty($brand)) {
    $sql .= " AND LEFT(d.KodeItem, 1) = '" . str_replace("'", "''", $brand) . "'";
}

// === Filter group (customer) ===
if (!empty($group)) {
    $sql .= " AND e.KodeLgn = '" . str_replace("'", "''", $group) . "'";
}

$sql .= " 
    GROUP BY f.KodeGudang, f.NamaGudang, e.NamaLgn 
    ORDER BY f.KodeGudang
";

$result = odbc_exec($conn, $sql);

$data = [];
while ($row = odbc_fetch_array($result)) {
    // Hitung harga net
    $harga_net = (float)str_replace(['.', ','], ['', '.'], $row['TotalHargaJualRupiah']) 
               - (float)str_replace(['.', ','], ['', '.'], $row['TotalItemDiscRupiah']);
    $harga_net_formatted = number_format($harga_net, 0, ',', '.');

    // === Ambil data wilayah dari MySQL berdasarkan kode_gudang ===
    $kodeGudang = $row['KodeGudang'];
    $wilayahQuery = $mysqli->prepare("
        SELECT wilayah, area, kota, direktur_operasional, regional_manager, manager, supervisor 
        FROM m_wilayah 
        WHERE kode_gudang = ?
        LIMIT 1
    ");
    $wilayahQuery->bind_param("s", $kodeGudang);
    $wilayahQuery->execute();
    $wilayahResult = $wilayahQuery->get_result();
    $wilayahRow = $wilayahResult->fetch_assoc();

    $data[] = [
        "Gudang"             => $row['KodeGudang'] . " - " . $row['NamaGudang'],
        "NamaCustomer"       => $row['NamaLgn'],
        "TotalQty"           => $row['TotalQtyFormatted'],
        "TotalDiskon"        => $row['TotalItemDiscRupiah'],
        "HargaNet"           => $harga_net_formatted,
        "wilayah"            => $wilayahRow['wilayah'] ?? "",
        "area"               => $wilayahRow['area'] ?? "",
        "kota"               => $wilayahRow['kota'] ?? "",
        "direktur_operasional" => $wilayahRow['direktur_operasional'] ?? "",
        "regional_manager"   => $wilayahRow['regional_manager'] ?? "",
        "manager"            => $wilayahRow['manager'] ?? "",
        "supervisor"         => $wilayahRow['supervisor'] ?? ""
    ];
}

echo json_encode(['data' => $data]);
