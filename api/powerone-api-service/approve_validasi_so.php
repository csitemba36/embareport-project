<?php
require_once "db-config.php";
header('Content-Type: application/json; charset=utf-8'); // ✅ pastikan output JSON selalu valid

// ==== Validasi & ambil parameter ====
$brand = $_POST['brand'] ?? 'emba_jeans';
$bukti_id = trim($_POST['bukti_id'] ?? '');

$allowedBrands = ['emba_jeans', 'bbg_twist'];
if (!in_array($brand, $allowedBrands)) {
    echo json_encode(["status" => "error", "message" => "Invalid brand"]);
    exit;
}

if ($bukti_id === '') {
    echo json_encode(["status" => "error", "message" => "Bukti ID tidak boleh kosong"]);
    exit;
}

// ==== Koneksi ODBC ====
$db = new db_odbc($brand);
$conn = $db->getConnection();
if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Koneksi ke database $brand gagal"]);
    exit;
}

// ==== Sanitasi input ====
$bukti_id_safe = str_replace("'", "''", $bukti_id);

// ==== Cek apakah bukti_id ada ====
$sql_check = "SELECT COUNT(*) AS jml FROM tjual1_so WHERE TRIM(bukti_id) = '$bukti_id_safe'";
$rs_check = @odbc_exec($conn, $sql_check);

if (!$rs_check) {
    echo json_encode(["status" => "error", "message" => "Query cek gagal: " . odbc_errormsg($conn)]);
    exit;
}

$row = odbc_fetch_array($rs_check);
if (empty($row) || (int)$row['jml'] === 0) {
    echo json_encode(["status" => "error", "message" => "bukti_id tidak ditemukan di database $brand"]);
    exit;
}

// ==== Jalankan update ====
$sql_update = "
    UPDATE tjual1_so
    SET flag_print_sj = '1'
    WHERE TRIM(bukti_id) = '$bukti_id_safe';
    SELECT @@ROWCOUNT AS affected;
";

$rs_update = @odbc_exec($conn, $sql_update);

if ($rs_update) {
    $affected = 0;
    while (odbc_fetch_row($rs_update)) {
        $affected = (int)odbc_result($rs_update, "affected");
    }

    $db->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Update sukses ($affected baris) untuk $bukti_id di $brand"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal update: " . odbc_errormsg($conn)
    ]);
}
?>
