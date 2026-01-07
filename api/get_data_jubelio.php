<?php
header('Content-Type: application/json');

// 🔹 Koneksi ke MySQL
$mysqli = new mysqli("localhost", "root", "", "db_powerone");
if ($mysqli->connect_errno) {
    die(json_encode(["error" => "Koneksi gagal: " . $mysqli->connect_error]));
}

// 🔹 Ambil brand dari parameter GET
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';

// 🔹 Mapping nama brand ke brandKey
$brandMap = [
    'EMBA LADIES'  => 4090,
    'MORPHIDAE'    => 6236,
    'EMBA CASUAL' => 6399,
    'EMBA DENIM'   => 6471,  // sebelumnya EMBA JEANS → ubah ke EMBA DENIM
    'USED JEANS'   => 6484
];

// 🔹 Jika brand tidak valid → kembalikan data kosong
if (!isset($brandMap[$brand])) {
    echo json_encode(['data' => [], 'debug' => ['note' => 'brand tidak valid atau kosong', 'brand' => $brand]]);
    exit;
}

$brandKey = $brandMap[$brand];

// 🔹 Query gabungan
$sql = "
    SELECT 
        a.artikel AS barcode,
        a.artikel,
        b.keterangan,
        b.nm_style,
        b.nm_warna,
        c.composition AS komposisi,
        b.nm_model,
        c.size,
        a.p_pakaian_cm,
        a.l_pakaian_cm,
        a.qty,
        a.hrg_jual_konsi AS harga,
        b.cmt_or_fob
    FROM jubelio_inventory a
    LEFT JOIN sku_powerone b ON a.artikel = b.kd_sku
    LEFT JOIN mcp_items c ON a.artikel = c.supplierBarcode
        AND c.brandKey = $brandKey
    WHERE a.brand = '$brand'
";

// 🔹 Jalankan query
$result = $mysqli->query($sql);
$data = [];

// 🔹 Cek error query
if (!$result) {
    echo json_encode([
        'error' => $mysqli->error,
        'sql' => $sql
    ]);
    exit;
}

// 🔹 Cek apakah hasil kosong
if ($result->num_rows === 0) {
    echo json_encode([
        'debug' => [
            'note' => 'Query berhasil tapi hasil kosong',
            'brand' => $brand,
            'brandKey' => $brandKey,
            'sql' => $sql
        ],
        'data' => []
    ]);
    exit;
}

// 🔹 Ambil hasil jika ada
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// 🔹 Output JSON untuk DataTables
echo json_encode(['data' => $data]);

$mysqli->close();
?>
