<?php
require_once('../config/connect_db.php');
// === Koneksi MySQL ===
$mysqli = new mysqli("localhost", "root", "", "db_retail_unity");
if ($mysqli->connect_error) {
    die("Koneksi MySQL gagal: " . $mysqli->connect_error);
}

// === Koneksi ODBC ke SQL Server ===
/*$dsn = "dbmaserp";
$username = "db";
$password = "3mb4Sejati";
$conn = odbc_connect($dsn, $username, $password);
if (!$conn) {
    die("Koneksi ODBC gagal: " . odbc_errormsg());
}*/


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

// === Query utama ===
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
            xwh.NamaGudang AS GudangTarget,

            f.NamaGudang AS SR_KodeGudang,
            g.NamaGudang AS SR_gdg_target,

            a.Keterangan AS Keterangan, 
            g.AlamatGudang AS alamatGudangTarget,

            -- Tambahan sesuai permintaan
            LEFT(x.KodeItem, 1) AS BrandCode

        FROM StockRequests a
        JOIN InventoryTransactions b 
            ON a.NoRequest = b.AllNoStockRequest

        -- Warehouse asal & target berdasarkan InventoryTransactions
        JOIN Warehouses w 
            ON b.KodeGudang = w.KodeGudang
        JOIN Warehouses xwh 
            ON b.GdgTarget = xwh.KodeGudang

        -- Warehouse asal & target berdasarkan StockRequests
        JOIN Warehouses f 
            ON a.KodeGudang = f.KodeGudang
        JOIN Warehouses g 
            ON a.GdgTarget = g.KodeGudang

        -- Tambahan CROSS APPLY untuk ambil KodeItem
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


$sql .= " ORDER BY a.TglRequest DESC";

// === Eksekusi query ===
$result = odbc_exec($conn, $sql);
if (!$result) {
    die("Query gagal: " . odbc_errormsg());
}

// === Ambil hasil dan bersihkan karakter aneh ===
$data = [];
while ($row = odbc_fetch_array($result)) {
    foreach ($row as $key => $val) {
        if (is_string($val)) {
            if (!mb_check_encoding($val, 'UTF-8')) {
                $val = utf8_encode($val);
            }
            $val = preg_replace('/[[:cntrl:]]/', '', $val);
        }
        $row[$key] = $val;
    }
    $data[] = $row;
}

// === Encode JSON dengan aman ===
echo json_encode(['data' => $data], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
?>
