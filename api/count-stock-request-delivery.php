<?php
// === Koneksi MySQL ===// === Koneksi ODBC ke SQL Server ===
require_once('../config/connect_db.php');

// === Ambil blacklist dari MySQL ===
$blacklist = [];
$res = $mysqli->query("SELECT no_sr FROM blacklist_sr_on_going WHERE flag = 1");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $blacklist[] = "'" . $row['no_sr'] . "'";
    }
}
$blacklistStr = implode(',', $blacklist);

// === Ambil parameter is_closed ===
$is_closed = isset($_GET['is_closed']) ? $_GET['is_closed'] : '0';

// === Ambil cookie akses gudang ===
$aksesGudangCookie = $_COOKIE['aksesgudang'] ?? '';
$aksesGudangList = [];
if (!empty($aksesGudangCookie)) {
    $aksesGudangList = array_filter(array_map('trim', explode(';', $aksesGudangCookie)));
}

// === Ambil cookie userrolecode ===
$userRoleCode = $_COOKIE['userrolecode'] ?? '';

// ==== Ambil akses merk dari cookie ====
$aksesMerk = isset($_COOKIE['aksesmerk']) ? trim($_COOKIE['aksesmerk']) : "";
$aksesMerkUpper = strtoupper($aksesMerk);

// === Query COUNT saja ===
$sql = "
    SELECT COUNT(1) AS total
    FROM StockRequests a
    JOIN InventoryTransactions b 
        ON a.NoRequest = b.AllNoStockRequest
    JOIN Warehouses f 
        ON a.KodeGudang = f.KodeGudang
    JOIN Warehouses g 
        ON a.GdgTarget = g.KodeGudang
    CROSS APPLY (
        SELECT TOP 1 d.KodeItem
        FROM StockRequestItems sri
        JOIN InventoryStocks c 
            ON sri.InventoryStockId = c.InventoryStockId
        JOIN Inventories d 
            ON c.InventoryId = d.InventoryId
        WHERE sri.StockRequestId = a.StockRequestId
    ) x
    WHERE 1 = 1
";

// === Filter kondisi ===
if ($is_closed !== '') {
    $sql .= " AND a.isClosed = '$is_closed'";
}

if (!empty($blacklistStr)) {
    $sql .= " AND a.NoRequest NOT IN ($blacklistStr)";
}

if (!empty($aksesGudangList)) {
    $gudangFilter = "'" . implode("','", array_map(function($v) {
        return str_replace("'", "''", $v);
    }, $aksesGudangList)) . "'";
    $sql .= " AND (f.KodeGudang IN ($gudangFilter) OR g.KodeGudang IN ($gudangFilter))";
}

if ($userRoleCode !== 'ADM' && substr($userRoleCode, 0, 2) !== 'WH') {
    $sql .= " AND g.KodeDept <> 'PST'";
}

if ($userRoleCode !== 'ADM') {

    // Filter merk
    if ($aksesMerk !== "" && $aksesMerkUpper !== "ALL MERK") {

        $merkList = explode(";", $aksesMerk);
        $merkList = array_filter(array_map(function($m){
            return addslashes(trim($m));
        }, $merkList));

        if (!empty($merkList)) {
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
$total = (int)$row['total'];

// === Output JSON ===
echo json_encode(['total' => $total]);
?>
