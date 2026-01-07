<?php
header('Content-Type: application/json');

// ======================================
// KONEKSI SQL SERVER VIA ODBC
// ======================================
$dsn      = "dbmaserp_mcp";   // DSN ODBC (tanpa lock database)
$username = "db";
$password = "3mb4Sejati";

// Buka koneksi ODBC
$conn = odbc_connect($dsn, $username, $password);

if (!$conn) {
    die("Koneksi ODBC gagal: " . odbc_errormsg());
}

$sql = "SELECT * FROM mcp_stores";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'status'  => false,
        'message' => 'Query gagal',
        'error'   => mysqli_error($conn)
    ]);
    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    'status' => true,
    'total'  => count($data),
    'data'   => $data
], JSON_UNESCAPED_UNICODE);

mysqli_close($conn);
