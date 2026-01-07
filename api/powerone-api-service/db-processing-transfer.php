<?php
require_once "db-config.php";

// Koneksi ke SQL Anywhere via ODBC
$db = new db_odbc("emba_jeans");
$conn = $db->getConnection();

// Koneksi ke MySQL
$mysqli = new mysqli("localhost", "root", "", "db_powerone");
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit;
}

// Ambil parameter dari DataTables
$draw        = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start       = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length      = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

// Filter dasar
$baseWhere = "a.kd_cust = b.kd_cust 
              AND a.tipe_trans = 'KIRIM' 
              AND b.kd_cust_group = 'YOGYA_DS' 
              AND a.kd_cust IN ('GRY_BUAH_BT', 'YGY_GRAND','YGY_SUNDA_60','YGY_TASIK') 
              AND a.status = 'F'
              AND a.tgl >= '2025-07-01'
              ";

// Hitung total record
$sql = "SELECT COUNT(*) AS cnt 
        FROM tspdo1 a, mcust b 
        WHERE $baseWhere";
$rs = odbc_exec($conn, $sql);
$totalRecords = odbc_result($rs, "cnt");

// Filter pencarian tambahan
$searchQuery = "";
if ($searchValue != '') {
    $searchQuery = " AND (a.BUKTI_ID LIKE '%$searchValue%' 
                          OR a.KD_CUST LIKE '%$searchValue%' 
                          OR a.ORDER_REFF LIKE '%$searchValue%')";
}

// Hitung total record dengan filter
$sql = "SELECT COUNT(*) AS cnt 
        FROM tspdo1 a, mcust b 
        WHERE $baseWhere $searchQuery";
$rs = odbc_exec($conn, $sql);
$totalRecordwithFilter = odbc_result($rs, "cnt");

// Ambil data sesuai paging (SQL Anywhere pakai TOP ... START AT)
$startAt = $start + 1; // START AT mulai dari 1, bukan 0
$sql = "SELECT TOP $length START AT $startAt
            a.BUKTI_ID, 
            a.TGL, 
            a.KD_CUST, 
            a.ORDER_REFF, 
            a.KD_DISTRIBUTOR, 
            a.ADD_JUAL, 
            b.cust_gab,
            CASE 
                WHEN LEFT(a.BUKTI_ID, 1) = '1' THEN 'EMBA JEANS'
                WHEN LEFT(a.BUKTI_ID, 1) = '2' THEN 'EMBA CASUAL'
                ELSE 'UNKNOWN'
            END AS BRAND
        FROM tspdo1 a, mcust b
        WHERE $baseWhere $searchQuery
        ORDER BY a.TGL DESC";

$rs = odbc_exec($conn, $sql);

$data = [];
while ($row = odbc_fetch_array($rs)) {
    $custGab = $row['cust_gab'];
    $initialJeans = "";

    // Cari initial dari MySQL
    $stmt = $mysqli->prepare("SELECT initial FROM yo_store WHERE initial_powerone_jeans = ?");
    $stmt->bind_param("s", $custGab);
    $stmt->execute();
    $stmt->bind_result($initialJeans);
    $stmt->fetch();
    $stmt->close();

    $data[] = [
        "BUKTI_ID"       => $row['BUKTI_ID'],
        "TGL"            => $row['TGL'],
        "KD_CUST"        => $row['KD_CUST'],
        "ORDER_REFF"     => $row['ORDER_REFF'],
        "KD_DISTRIBUTOR" => $row['KD_DISTRIBUTOR'],
        "ADD_JUAL"       => $row['ADD_JUAL'],
        "CUST_GAB"       => $row['cust_gab'],
        "BRAND"          => $row['BRAND'],
        "INITIAL"        => $initialJeans // tambahan dari MySQL
    ];
}

// Response JSON
$response = [
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalRecordwithFilter),
    "data" => $data
];

echo json_encode($response);
?>
