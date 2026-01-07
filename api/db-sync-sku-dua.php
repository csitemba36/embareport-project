<?php
header('Content-Type: application/json; charset=utf-8');

// ==================================================
// 1️⃣ KONEKSI SQL SERVER (MASERP)
// ==================================================
// ==================================================
// 2️⃣ KONEKSI MYSQL (POWERONE)
// ==================================================
require_once('../config/connect_db.php');


// ==================================================
// 3️⃣ AMBIL MASTER BRAND (MYSQL)
// ==================================================
$brandMap = [];

$resBrand = $mysqli->query("
    SELECT kodemerkyo, kodemerk, namamerk
    FROM m_brands
");

if ($resBrand) {
    while ($b = $resBrand->fetch_assoc()) {
        $brandMap[$b['kodemerk']] = [
            'brand_code' => $b['kodemerkyo'],
            'brand_name' => $b['namamerk']
        ];
    }
}

$colorMap = [];

$resColor = $mysqli->query("
    SELECT brand, kode, warna
    FROM m_warna
");

if ($resColor) {
    while ($c = $resColor->fetch_assoc()) {
        // key gabungan: BRAND|KODE_WARNA
        $key = $c['brand'] . '|' . $c['kode'];

        $colorMap[$key] = $c['warna'];
    }
}

// ==================================================
// 3️⃣b AMBIL MASTER RANGE (MYSQL)
// ==================================================
$rangeMap = [];

$resRange = $mysqli->query("
    SELECT brand, kode, `range`
    FROM m_range
");

if ($resRange) {
    while ($r = $resRange->fetch_assoc()) {
        // key gabungan BRAND|KODE_RANGE
        $key = $r['brand'] . '|' . $r['kode'];
        $rangeMap[$key] = $r['range'];
    }
}

// ==================================================
// 3️⃣c AMBIL MASTER STYLE (MYSQL)
// ==================================================
$styleMap = [];

$resStyle = $mysqli->query("
    SELECT brand, kode_range, kode, style
    FROM m_style
");

if ($resStyle) {
    while ($s = $resStyle->fetch_assoc()) {

        // key gabungan BRAND|RANGE|STYLE
        $key = $s['brand'] . '|' . $s['kode_range'] . '|' . $s['kode'];

        $styleMap[$key] = $s['style'];
    }
}

// ==================================================
// 4️⃣ VALIDASI INPUT SKU (supplier_barcode)
// ==================================================
$skus = $_POST['skus'] ?? [];

if (!is_array($skus) || count($skus) === 0) {
    echo json_encode([
        "details" => [],
        "message" => "SKU tidak dikirim"
    ]);
    exit;
}

$skus = array_unique(array_filter($skus));

// ==================================================
// 5️⃣ QUERY INVENTORIES (SQL SERVER)
// ==================================================
$sql = "
    SELECT
    SUBSTRING(i.KodeItem, 1, 1)               AS brand_key,
    i.KodeItem                                AS style_code,
    SUBSTRING(i.KodeItem, 2, LEN(i.KodeItem)) AS supplier_barcode,
    i.ColorCode                               AS color_code,
    i.SizeCode                                AS size,
    i.ColorCode                               AS nm_warna,

    -- 🔥 HARGA DARI ITEM INDUK
    p.HargaJual                               AS price_raw,

    i.RangeCode                               AS range_code,
    i.StyleCode                               AS style_code_key,
    i.NamaBarang                              AS art_desc
FROM Inventories i

OUTER APPLY (
    SELECT TOP 1 HargaJual
    FROM Inventories x
    WHERE x.KodeItem = LEFT(i.KodeItem, 9)
      AND x.HargaJual > 0
) p

WHERE SUBSTRING(i.KodeItem, 2, LEN(i.KodeItem)) = ?
";

$stmt = odbc_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode([
        "error" => "Gagal prepare query SQL Server"
    ]);
    exit;
}

// ==================================================
// 6️⃣ LOOP SKU + JOIN BRAND (PHP)
// ==================================================
$details = [];

foreach ($skus as $sku) {

    if (!odbc_execute($stmt, [$sku])) {
        continue;
    }

    while ($row = odbc_fetch_array($stmt)) {

        // UTF8 normalize
        $row = array_map(function ($v) {
            return is_string($v) ? utf8_encode($v) : $v;
        }, $row);

        $brandKey = $row['brand_key'];
        $colorCd  = $row['color_code']; // BLK
        $rangeCd  = $row['range_code']; // ⬅️ RANGE CODE

        // Mapping brand dari MySQL
        $brandCode = $brandMap[$brandKey]['brand_code'] ?? $brandKey;
        $brandName = $brandMap[$brandKey]['brand_name'] ?? '';

        // WARNA mapping
        $colorKey  = $brandKey . '|' . $colorCd;
        $colorName = $colorMap[$colorKey] ?? $colorCd;

        $rangeKey  = $brandKey . '|' . $rangeCd;
        $rangeName = $rangeMap[$rangeKey] ?? $rangeCd;

        $styleCd  = $row['style_code_key'];
        $styleKey  = $brandKey . '|' . $rangeCd . '|' . $styleCd;
        $styleName = $styleMap[$styleKey] ?? $styleCd;

        $details[] = [
            "brand"            => $brandCode,
            "brand_name"       => $brandName,
            "style_code"       => $row['style_code'],
            "supplier_barcode" => $row['supplier_barcode'],
            "art_desc"         => $row['art_desc'],
            "price"            => is_numeric($row['price_raw'])
                                    ? (float)$row['price_raw']
                                    : null,
            "color_code"       => $colorCd,
            "color"            => $colorName,   // ⬅️ dari m_warna
            //"color"            => $row['nm_warna'],
            "size"             => $row['size'],
            "model"            => $rangeName,   // ⬅️ HASIL MAPPING
            "range_code"       => $rangeCd,
            //"range"            => 
            "group"            => $styleName,   // ⬅️ HASIL FINAL
            //"style"            => 
        ];
    }
}

// ==================================================
// 7️⃣ RESPONSE JSON
// ==================================================
echo json_encode([
    "details" => $details
]);