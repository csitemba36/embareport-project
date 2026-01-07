<?php
header('Content-Type: application/json');
require_once('../config/connect_db.php');

// Periksa koneksi database
if (!$conn) {
    echo json_encode(["error" => "Koneksi gagal: " . odbc_errormsg()]);
    exit;
}

// Ambil parameter dari request
$searchTerm = isset($_POST['term']) ? trim($_POST['term']) : '';
$kodeLgn = isset($_POST['KodeLgn']) ? trim($_POST['KodeLgn']) : '';

// Ambil cookie
$aksesGudangRaw = isset($_COOKIE['aksesgudang']) ? $_COOKIE['aksesgudang'] : '';
$userRoleCode = isset($_COOKIE['userrolecode']) ? $_COOKIE['userrolecode'] : '';

$data = [];

// Parsing aksesGudang dari cookie (jika bukan ADM)
$aksesGudangArray = [];
if (strtoupper($userRoleCode) !== 'ADM' && $aksesGudangRaw !== '') {
    // Hapus tanda kutip dan pecah berdasarkan titik koma
    $aksesGudangArray = array_filter(explode(';', str_replace('"', '', $aksesGudangRaw)));
}

// Proses query
if ($kodeLgn === '') {
    echo json_encode(["error" => "KodeLgn wajib diisi"]);
    exit;
}

if ($kodeLgn === 'GUDANG') {
    // Kode khusus untuk GUDANG
    $sql = "SELECT KodeGudang, NamaGudang 
            FROM warehouses 
            WHERE KodeDept = 'PST'";

    // Tambahkan filter aksesGudang jika user bukan ADM
    if (!empty($aksesGudangArray)) {
        $escaped = array_map(function($g) {
            return "'" . str_replace("'", "''", $g) . "'";
        }, $aksesGudangArray);
        $sql .= " AND KodeGudang IN (" . implode(",", $escaped) . ")";
    }

    // Tambahkan filter pencarian
    if ($searchTerm !== '') {
        $safeTerm = str_replace("'", "''", $searchTerm);
        $sql .= " AND (KodeGudang LIKE '%$safeTerm%' OR NamaGudang LIKE '%$safeTerm%')";
    }

    $sql .= " ORDER BY NamaGudang ASC";
    $stmt = odbc_exec($conn, $sql);

} else {
    // Untuk pelanggan biasa
    $sql = "SELECT a.KodeGudang, a.NamaGudang 
            FROM warehouses a
            JOIN Customers b ON a.CustomerId = b.CustomerId
            WHERE b.KodeLgn = ?";

    // Tambahkan filter aksesGudang jika bukan ADM
    if (!empty($aksesGudangArray)) {
        $escaped = array_map(function($g) {
            return "'" . str_replace("'", "''", $g) . "'";
        }, $aksesGudangArray);
        $sql .= " AND a.KodeGudang IN (" . implode(",", $escaped) . ")";
    }

    // Tambahkan filter pencarian
    if ($searchTerm !== '') {
        $safeTerm = str_replace("'", "''", $searchTerm);
        $sql .= " AND (a.KodeGudang LIKE '%$safeTerm%' OR a.NamaGudang LIKE '%$safeTerm%')";
    }

    $sql .= " ORDER BY a.NamaGudang ASC";
    $stmt = odbc_prepare($conn, $sql);
    odbc_execute($stmt, [$kodeLgn]);
}

// Ambil data hasil query
if ($stmt) {
    while ($row = odbc_fetch_array($stmt)) {
        $data[] = [
            "id" => $row['KodeGudang'],
            "text" => $row['KodeGudang'] . ' - ' . $row['NamaGudang']
        ];
    }
}

odbc_close($conn);
echo json_encode($data);
?>
