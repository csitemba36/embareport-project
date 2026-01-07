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

// === Query dasar ===
$sql = "
    SELECT COUNT(*) AS total
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

/// ==== Filter Role PST ====  
if ($userRoleCode !== 'ADM' && $userRoleCode !== 'WHSPV') {
    $sql .= " AND f.KodeDept <> 'PST'";
}


// ==== Filter Akses Gudang ====  
if (!empty($aksesGudangList) && $userRoleCode !== 'ADM') {
    $gudangFilter = "'" . implode("','", array_map('addslashes', $aksesGudangList)) . "'";
    $sql .= " AND (f.KodeGudang IN ($gudangFilter) OR g.KodeGudang IN ($gudangFilter))";
}

// ==== Filter Akses Merk (bukan ADM) ====  
// Jika BUKAN ADM dan BUKAN WHSPV → baru pakai filter merk
if ($userRoleCode !== 'ADM' && $userRoleCode !== 'WHSPV') {

    // Jika cookie berisi merk (bukan kosong dan bukan ALL MERK)
    if ($aksesMerk !== "" && strtoupper($aksesMerk) !== "ALL MERK") {

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




// === Eksekusi query ===
$result = odbc_exec($conn, $sql);

if (!$result) {
    die("Query gagal: " . odbc_errormsg());
}

$row = odbc_fetch_array($result);

// === Return JSON ===
echo json_encode(['total' => (int)$row['total']]);
