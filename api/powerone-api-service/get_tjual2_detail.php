<?php
require_once "db-config.php";

$brand = $_GET['brand'] ?? 'emba_jeans';
$bukti_id = $_GET['bukti_id'] ?? '';

if ($bukti_id == '') {
    echo json_encode(["status" => "error", "message" => "Bukti ID kosong"]);
    exit;
}

$db = new db_odbc($brand);
$conn = $db->getConnection();

$bukti_id_safe = str_replace("'", "''", $bukti_id);

$sql = "
    SELECT 
        bukti_id,
        urut,
        kd_sku,
        qty1,
        qty2,
        hrg_jual,
        pot_jual,
        add_jual,
        pot1,
        pot2,
        kd_acara,
        tipe_harga,
        total_harga
    FROM tjual2_so
    WHERE bukti_id = '$bukti_id_safe'
    ORDER BY urut
";

$rs = odbc_exec($conn, $sql);

$data = [];
if ($rs) {
    while ($row = odbc_fetch_array($rs)) {
        // Pastikan semua field UTF-8 agar tidak error di JSON
        $data[] = array_map('utf8_encode', $row);
    }

    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => odbc_errormsg($conn)]);
}
?>
