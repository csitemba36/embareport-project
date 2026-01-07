<?php
require '../vendor/autoload.php'; // pastikan sudah install: composer require phpoffice/phpspreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_powerone";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

if (!isset($_FILES['excelFile']) || !isset($_POST['brand'])) {
    die("❌ File atau brand belum dipilih!");
}

$brand = $_POST['brand'];
$fileTmp = $_FILES['excelFile']['tmp_name'];

try {
    // 🔹 Hapus data lama berdasarkan brand
    $delete = $conn->query("DELETE FROM jubelio_inventory WHERE brand = '$brand'");
    if ($delete) {
        echo "🧹 Data lama untuk brand <b>$brand</b> berhasil dihapus.<br>";
    } else {
        echo "⚠️ Gagal menghapus data lama: " . $conn->error . "<br>";
    }

    // 🔹 Baca file Excel
    $spreadsheet = IOFactory::load($fileTmp);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    $inserted = 0;

    // Lewati header (baris pertama)
    foreach (array_slice($rows, 1) as $row) {
        $artikel      = $conn->real_escape_string($row['A']);
        $model        = $conn->real_escape_string($row['B']);
        $warna_kode   = $conn->real_escape_string($row['C']);
        $kode_size    = $conn->real_escape_string($row['D']);
        $hrg_jual     = floatval(str_replace(',', '', $row['E']));
        $qty          = intval($row['F']);
        $kode_kategori= $conn->real_escape_string($row['G']);
        $kategori     = $conn->real_escape_string($row['H']);
        $sub_kategori = $conn->real_escape_string($row['I']);
        $warna_nama   = $conn->real_escape_string($row['J']);
        $size         = $conn->real_escape_string($row['K']);
        $nama_barang  = $conn->real_escape_string($row['L']);
        $p_pakaian    = floatval(str_replace(',', '.', $row['M']));
        $l_pakaian    = floatval(str_replace(',', '.', $row['N']));

        $sql = "
            INSERT INTO jubelio_inventory (
                brand, artikel, model, warna_kode, kode_size, hrg_jual_konsi, qty,
                kode_kategori, kategori, sub_kategori, warna_nama, size,
                nama_barang, p_pakaian_cm, l_pakaian_cm
            ) VALUES (
                '$brand', '$artikel', '$model', '$warna_kode', '$kode_size', '$hrg_jual', '$qty',
                '$kode_kategori', '$kategori', '$sub_kategori', '$warna_nama', '$size',
                '$nama_barang', '$p_pakaian', '$l_pakaian'
            )
        ";

        if ($conn->query($sql)) $inserted++;
    }

    echo "✅ Upload selesai! Total data tersimpan: <b>$inserted</b> baris untuk brand <b>$brand</b>.";
} catch (Exception $e) {
    echo "❌ Gagal memproses file Excel: " . $e->getMessage();
}
?>