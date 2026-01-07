<?php
require_once "db-config.php";
require_once('../../config/connect_db.php'); // koneksi ke MySQL

$db = new db_odbc("emba_jeans");
$conn = $db->getConnection();

$bukti_id = $_GET['bukti_id'] ?? '';

$sql = "SELECT
            CASE 
                WHEN LEFT(BUKTI_ID, 1) = '1' THEN 'E395'
                WHEN LEFT(BUKTI_ID, 1) = '2' THEN 'E060'
                ELSE 'UNKNOWN'
            END AS BRAND_CODE,
            CASE 
                WHEN LEFT(BUKTI_ID, 1) = '1' THEN 'EMBA JEANS'
                WHEN LEFT(BUKTI_ID, 1) = '2' THEN 'EMBA CASUAL'
                ELSE 'UNKNOWN'
            END AS BRAND,
            URUT, 
            LEFT(KD_SKU, 12) AS KD_SKU, 
            QTY_REN_DO, 
            QTY_ACT_DO, 
            hrg_jual, -- harga asli dari ODBC
            REPLACE(STR(CAST(hrg_jual AS INT), 15, 0), ' ', '') AS hrg_jual_rupiah,
            pot_jual, 
            add_jual
        FROM tspdo2 
        WHERE BUKTI_ID = ?";

$stmt = odbc_prepare($conn, $sql);
odbc_execute($stmt, [$bukti_id]);

$data = [];

while ($row = odbc_fetch_array($stmt)) {
    $row = array_map("utf8_encode", $row);

    $kd_sku   = $row['KD_SKU'];
    $brand    = $row['BRAND'];
    $hrg_jual = (int)$row['hrg_jual']; // harga dari ODBC (SQL Anywhere)

    $row['cims_price'] = null; // default null

    // cek ke MySQL
    $cek = $mysqli->prepare("SELECT price FROM yo_pim WHERE supplier_barcode = ? AND brand_desc = ?");
    $cek->bind_param("ss", $kd_sku, $brand);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        $pim   = $result->fetch_assoc();
        $price = (int)$pim['price'];

        $row['cims_price'] = $price; // simpan harga dari CIMS

        if ($price === $hrg_jual) {
            $row['KETERANGAN'] = '<span class="text-success">Ready ✅ | Harga sama ✅</span>';
        } else {
            $row['KETERANGAN'] = '<span class="text-warning">Ready ✅ | Harga berbeda dengan CIMS ❌</span>';
        }
    } else {
        $row['KETERANGAN'] = '<span class="text-danger">SKU belum terdaftar di CIMS ❌</span>';
    }

    $data[] = $row;
}

echo json_encode(["data" => $data]);
