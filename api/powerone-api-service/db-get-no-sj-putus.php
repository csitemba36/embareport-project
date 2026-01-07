<?php
require_once "db-config.php";

// Ambil parameter brand dan pencarian dari request AJAX
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'emba_jeans';
$search = isset($_GET['q']) ? $_GET['q'] : '';

// Validasi brand agar aman
$allowedBrands = ['emba_jeans', 'bbg_twist'];
if (!in_array($brand, $allowedBrands)) {
    echo json_encode(["results" => []]);
    exit;
}

// --- Koneksi ke SQL Anywhere ---
$db = new db_odbc($brand);
$conn = $db->getConnection();

// Cegah SQL injection
$search = str_replace("'", "''", $search);

// Query ambil bukti_id sesuai filter pencarian
$sql = "
    SELECT TOP 20 bukti_id 
    FROM tjual1
    WHERE bukti_id 
    ORDER BY bukti_id DESC
";

$rs = odbc_exec($conn, $sql);
if (!$rs) {
    echo json_encode(["results" => []]);
    exit;
}

// Format hasil untuk Select2
$results = [];
while ($row = odbc_fetch_array($rs)) {
    $results[] = [
        "id" => $row["bukti_id"],
        "text" => $row["bukti_id"]
    ];
}

echo json_encode(["results" => $results], JSON_UNESCAPED_UNICODE);
?>
