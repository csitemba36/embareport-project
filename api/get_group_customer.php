<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php');

if (!$conn) {
    echo json_encode(["error" => "Koneksi gagal: " . odbc_errormsg()]);
    exit;
}

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

$conditions = ["KodeDept = 'KON'"]; // Tambahkan kondisi default
$params = [];

if ($searchTerm !== '') {
    $conditions[] = "(KodeLgn LIKE ? OR NamaLgn LIKE ?)";
    $like = "%$searchTerm%";
    $params[] = $like;
    $params[] = $like;
}

$whereClause = implode(' AND ', $conditions);
$sql = "SELECT KodeLgn, NamaLgn FROM customers WHERE $whereClause ORDER BY NamaLgn ASC";

$stmt = odbc_prepare($conn, $sql);
$result = odbc_execute($stmt, $params);

$data = [];

// Tambahkan opsi default
$data[] = [
    "id" => "GUDANG",
    "text" => "GUDANG PUSAT"
];

/*$data[] = [
    "id" => "ALL",
    "text" => "SEMUA CUSTOMER"
];*/

if ($result) {
    while ($row = odbc_fetch_array($stmt)) {
        $data[] = [
            "id" => $row['KodeLgn'],
            "text" => $row['NamaLgn']
        ];
    }
}

odbc_close($conn);
echo json_encode($data);
?>
