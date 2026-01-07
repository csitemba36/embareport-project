<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "db_emba_rfid");

// Jika error
if ($mysqli->connect_errno) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to connect to MySQL: " . $mysqli->connect_error
    ]);
    exit;
}

$sql = "
SELECT 
    r.brand,
    r.kd_gudang,
    r.sku,
    SUM(r.stock) AS stok_komputer,
    COALESCE(o.stok_sekarang, 0) AS stok_sekarang
FROM epc_rfid r
LEFT JOIN (
    SELECT sku, kd_gudang, SUM(stock) AS stok_sekarang
    FROM epc_opname
    GROUP BY sku, kd_gudang
) o
ON r.sku = o.sku AND r.kd_gudang = o.kd_gudang
GROUP BY r.brand, r.kd_gudang, r.sku
ORDER BY r.sku ASC
";

$result = $mysqli->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);

$mysqli->close();
