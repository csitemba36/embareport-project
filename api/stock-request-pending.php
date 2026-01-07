<?php
require_once('../config/connect_db.php');

// === Cek koneksi dulu ===
if (!$conn) {
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

// === Ambil cookie role user ===
$userRoleCode = $_COOKIE['userrolecode'] ?? '';

// ==== Ambil akses merk dari cookie ====
$aksesMerk = isset($_COOKIE['aksesmerk']) ? trim($_COOKIE['aksesmerk']) : "";
$aksesMerkUpper = strtoupper($aksesMerk);

// ===== SQL Dasar =====
$sql = "
    SELECT 
        a.TglRequest, 
        a.TglEntry, 
        a.NoRequest, 
        a.KodeGudang, 
        a.GdgTarget,
        '' AS ParentTransaction,
        a.Keterangan,
        a.isClosed,
        f.NamaGudang AS GudangAsal,
        g.NamaGudang AS GudangTarget,
        g.alamatGudang AS alamatGudangTarget,
        LEFT(x.KodeItem, 1) AS BrandCode
    FROM StockRequests a
    LEFT JOIN InventoryTransactions it ON a.NoRequest = it.AllNoStockRequest
    LEFT JOIN Warehouses f ON a.KodeGudang = f.KodeGudang
    LEFT JOIN Warehouses g ON a.GdgTarget = g.KodeGudang
    CROSS APPLY (
        SELECT TOP 1 d.KodeItem
        FROM StockRequestItems sri
        JOIN InventoryStocks c ON sri.InventoryStockId = c.InventoryStockId
        JOIN Inventories d ON c.InventoryId = d.InventoryId
        WHERE sri.StockRequestId = a.StockRequestId
    ) x
    WHERE it.AllNoStockRequest IS NULL 
      AND a.IsClosedManually = 0
      AND a.IsClosed != 1
      AND a.GdgTarget IS NOT NULL
";

// ==== Filter Role PST ====  
if ($userRoleCode !== 'ADM' && $userRoleCode !== 'WHSPV') {
    $sql .= " AND f.KodeDept <> 'PST'";
}

// ==== Filter Akses Gudang ====  
if (!empty($aksesGudangList) && $userRoleCode !== 'ADM') {
    $gudangFilter = "'" . implode("','", array_map('addslashes', $aksesGudangList)) . "'";
    $sql .= " AND (f.KodeGudang IN ($gudangFilter) OR g.KodeGudang IN ($gudangFilter))";
}

// ==== Filter Akses Merk (bukan ADM) ====  
if ($userRoleCode !== 'ADM' && $userRoleCode !== 'WHSPV') {

   

    // Jika cookie berisi merk (bukan kosong dan bukan ALL MERK)
    if ($aksesMerk !== "" && $aksesMerkUpper !== "ALL MERK") {

        // Pecah merk: C;E;H → ['C','E','H']
        $merkList = explode(";", $aksesMerk);

        // Trim dan sanitasi
        $merkList = array_filter(array_map(function($m){
            return addslashes(trim($m));
        }, $merkList));

        if (!empty($merkList)) {

            // Bangun IN ('C','E','H')
            $merkFilter = "'" . implode("','", $merkList) . "'";
            $sql .= " AND LEFT(x.KodeItem, 1) IN ($merkFilter)";
        }
    }
    
}

$sql .= " ORDER BY a.TglEntry DESC";




//echo $sql;

// === Eksekusi query ===
$result = odbc_exec($conn, $sql);

if (!$result) {
    echo json_encode(['error' => 'Query gagal', 'detail' => odbc_errormsg($conn), 'sql' => $sql]);
    exit;
}

// === Ambil data ===
$data = [];
while ($row = odbc_fetch_array($result)) {
    // Pastikan semua string diubah ke UTF-8 untuk mencegah masalah encoding
    $utf8Row = array_map(function ($val) {
        return is_string($val) ? utf8_encode($val) : $val;
    }, $row);
    $data[] = $utf8Row;
}

// === Jika kosong, beri info ===
if (empty($data)) {
    echo json_encode([
        'data' => [],
        'message' => 'Tidak ada data ditemukan',
        'sql' => $sql,
        'aksesGudang' => $aksesGudangList,
        'role' => $userRoleCode
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// === Output JSON sukses ===
echo json_encode([
    'data' => $data,
    'count' => count($data),
    'sql' => $sql,
    'aksesGudang' => $aksesGudangList,
    'role' => $userRoleCode
], JSON_UNESCAPED_UNICODE);
?>
