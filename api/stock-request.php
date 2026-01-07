<?php
require_once('../config/connect_db.php');

// Set tanggal default (misalnya bulan ini)
$tanggal_awal  = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-t');
$kode_gudang   = isset($_GET['kode_gudang']) ? $_GET['kode_gudang'] : '';
$gudang_target = isset($_GET['gudang_target']) ? $_GET['gudang_target'] : '';
$is_closed     = isset($_GET['is_closed']) ? $_GET['is_closed'] : '0';

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

// Mulai Query
if ($is_closed === '2') {
    // Kalau pilih Pending
    $sql = "
        SELECT 
            a.TglRequest, 
            a.TglEntry, 
            a.NoRequest, 
            a.KodeGudang, 
            a.GdgTarget,
            '' AS ParentTransaction,
            a.isClosed,
            f.NamaGudang AS GudangAsal,
            g.NamaGudang AS GudangTarget
        FROM StockRequests a
        LEFT JOIN InventoryTransactions b ON a.NoRequest = b.AllNoStockRequest
        LEFT JOIN Warehouses f ON a.KodeGudang = f.KodeGudang
        LEFT JOIN Warehouses g ON a.GdgTarget = g.KodeGudang
        WHERE b.AllNoStockRequest IS NULL
          AND a.TglRequest BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ";

    // Filter berdasarkan cookie
    if (!empty($aksesGudangList)) {
        $gudangFilter = "'" . implode("','", array_map(function($v) {
            return str_replace("'", "''", $v);
        }, $aksesGudangList)) . "'";
        $sql .= " AND (f.KodeGudang IN ($gudangFilter) OR g.KodeGudang IN ($gudangFilter))";
    }

} else {
    // Query normal
    $sql = "
        SELECT 
            a.TglRequest, 
            a.TglEntry, 
            a.NoRequest, 
            a.KodeGudang, 
            a.GdgTarget, 
            a.isClosed,
            b.ParentTransaction, 
            b.KodeGudang AS B_KodeGudang, 
            b.GdgTarget AS B_GdgTarget,
            w.NamaGudang AS GudangAsal,
            x.NamaGudang AS GudangTarget,
            f.NamaGudang AS SR_KodeGudang,
            g.NamaGudang AS SR_gdg_target
        FROM StockRequests a
        JOIN InventoryTransactions b ON a.NoRequest = b.AllNoStockRequest
        JOIN Warehouses w ON b.KodeGudang = w.KodeGudang
        JOIN Warehouses x ON b.GdgTarget = x.KodeGudang
        JOIN Warehouses f ON a.KodeGudang = f.KodeGudang
        JOIN Warehouses g ON a.GdgTarget = g.KodeGudang
        WHERE a.TglRequest BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ";

    // Tambahkan filter dinamis
    if (!empty($kode_gudang)) {
        $sql .= " AND a.KodeGudang = '$kode_gudang'";
    }
    if (!empty($gudang_target)) {
        $sql .= " AND a.GdgTarget = '$gudang_target'";
    }
    if ($is_closed !== '') {
        $sql .= " AND a.isClosed = '$is_closed'";
    }

    // Filter berdasarkan cookie
    if (!empty($aksesGudangList)) {
        $gudangFilter = "'" . implode("','", array_map(function($v) {
            return str_replace("'", "''", $v);
        }, $aksesGudangList)) . "'";
        $sql .= " AND (f.KodeGudang IN ($gudangFilter) OR g.KodeGudang IN ($gudangFilter))";
    }

    // Urutkan
    $sql .= " ORDER BY a.TglRequest ASC";
}

// Eksekusi query
$result = odbc_exec($conn, $sql);

if (!$result) {
    die("Query gagal: " . odbc_errormsg());
}

// Fetch the result and prepare the response
$data = [];
while ($row = odbc_fetch_array($result)) {
    $data[] = $row;
}

// Return the data as JSON
echo json_encode(['data' => $data]);
