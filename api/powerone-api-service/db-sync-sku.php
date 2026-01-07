<?php
header('Content-Type: application/json; charset=utf-8');
require_once "db-config.php";

try {
    $db = new db_odbc("emba_jeans");
    $conn = $db->getConnection();

    $skus = $_POST['skus'] ?? [];
    if (!is_array($skus) || count($skus) == 0) {
        echo json_encode(["details" => [], "message" => "SKU tidak dikirim"]);
        exit;
    }

    $details = [];
    foreach ($skus as $sku) {
        $sql = "
            SELECT 
                'E395' as brand,
                'A' || substr(a.kd_sku, 0, 12) AS style_code,
                substr(a.kd_sku, 0, 12) as supplier_barcode,
                a.kd_warna as color,
                a.kd_size as size,
                b.nm_warna,
                c.hrg_jual as price_raw,
                d.nm_range as model,
                e.nm_style as nm_group,
                f.nm_model as art_desc
            FROM mpsku a,
                 mpwarna b,
                 mpmodel_harga c,
                 mprange d,
                 mpstyle e,
                 mpmodel f
            WHERE substr(a.kd_sku,0,12) = ?
              AND a.kd_warna = b.kd_warna
              AND substr(a.kd_sku,0,8) = c.kd_model
              AND c.kd_harga = '01'
              AND substr(a.kd_sku,0,1) = d.kd_range
              AND substr(a.kd_sku,0,1) = e.kd_range
              AND substr(a.kd_sku,2,2) = e.kd_style
              AND a.kd_model = f.kd_model
        ";

        $stmt = odbc_prepare($conn, $sql);
        $exec = odbc_execute($stmt, [$sku]);

        if ($exec) {
            while ($row = odbc_fetch_array($stmt)) {
                $row = array_map("utf8_encode", $row);

                // Validasi harga di PHP, bukan SQL
                $price = null;
                if (isset($row['price_raw']) && is_numeric($row['price_raw'])) {
                    $price = (float)$row['price_raw'];
                }

                $details[] = [
                    "brand"            => $row['brand'],
                    "style_code"       => $row['style_code'],
                    "supplier_barcode" => $row['supplier_barcode'],
                    "art_desc"         => $row['art_desc'],
                    "price"            => $price,
                    "color"            => $row['nm_warna'],
                    "size"             => $row['size'],
                    "model"            => $row['model'],
                    "group"            => $row['nm_group']
                ];
            }
        }
    }

    echo json_encode(["details" => $details]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
