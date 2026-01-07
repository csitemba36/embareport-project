<?php
require_once "db-config.php"; // Koneksi ODBC

// ====== KONEKSI KE SQL ANYWHERE ======
$db = new db_odbc("used_jeans");
$conn = $db->getConnection();
if (!$conn) {
    die("Koneksi ODBC gagal.");
}

// ====== KONEKSI KE MYSQL ======
$mysqli = new mysqli("localhost", "root", "", "db_powerone");
if ($mysqli->connect_errno) {
    die("Koneksi MySQL gagal: " . $mysqli->connect_error);
}

// ====== AMBIL ARTIKEL DARI jubelio_inventory ======
$sqlInv = "
    SELECT DISTINCT artikel 
    FROM jubelio_inventory 
    WHERE brand = 'USED JEANS' 
      AND artikel IS NOT NULL 
      AND artikel != ''
";

$result = $mysqli->query($sqlInv);
if (!$result || $result->num_rows == 0) {
    die("Tidak ada artikel untuk brand USED JEANS di tabel jubelio_inventory.");
}

$artikels = [];
while ($row = $result->fetch_assoc()) {
    $artikels[] = $row['artikel'];
}

$totalArtikel = count($artikels);
echo "🚀 Mulai sync SKU dari SQL Anywhere untuk $totalArtikel artikel...\n\n";

// ====== PROSES PER ARTIKEL (BATCH) ======
$countInsert = 0;
$countUpdate = 0;
$batchSize = 200; // per batch 200 artikel
$totalProcessed = 0;

foreach (array_chunk($artikels, $batchSize) as $batch) {
    $inList = implode("','", array_map('trim', $batch));

    $sql = "
        SELECT 
            'USED JEANS' AS brand,
            substr(a.kd_sku,1,12) AS kd_sku,
            b.keterangan,
            b.nm_style,
            c.nm_warna,
            d.nm_model
        FROM mpsku a
        JOIN mpstyle b ON 
            b.kd_range = substr(a.kd_sku,1,1) 
            AND b.kd_style = substr(a.kd_sku,2,2)
        JOIN mpwarna c ON 
            c.kd_warna = substr(a.kd_sku,9,2)
        JOIN mpmodel d ON 
            d.kd_model = substr(a.kd_sku,1,8)
        WHERE substr(a.kd_sku,1,12) IN ('$inList')
    ";

    $rs = odbc_exec($conn, $sql);
    if (!$rs) {
        echo "⚠️ Query ODBC gagal batch berikutnya dilewati: " . odbc_errormsg($conn) . "\n";
        continue;
    }

    while ($row = odbc_fetch_array($rs)) {
        $brand      = $mysqli->real_escape_string($row['brand']);
        $kd_sku     = $mysqli->real_escape_string($row['kd_sku']);
        $keterangan = $mysqli->real_escape_string($row['keterangan']);
        $nm_style   = $mysqli->real_escape_string($row['nm_style']);
        $nm_warna   = $mysqli->real_escape_string($row['nm_warna']);
        $nm_model   = $mysqli->real_escape_string($row['nm_model']);

        $query = "
            INSERT INTO sku_powerone (brand, kd_sku, keterangan, nm_style, nm_warna, nm_model)
            VALUES ('$brand', '$kd_sku', '$keterangan', '$nm_style', '$nm_warna', '$nm_model')
            ON DUPLICATE KEY UPDATE
                brand = VALUES(brand),
                keterangan = VALUES(keterangan),
                nm_style = VALUES(nm_style),
                nm_warna = VALUES(nm_warna),
                nm_model = VALUES(nm_model),
                updated_at = CURRENT_TIMESTAMP
        ";

        if ($mysqli->query($query)) {
            if ($mysqli->affected_rows == 1) $countInsert++;
            else $countUpdate++;
        } else {
            echo "❌ Error insert/update SKU: $kd_sku → " . $mysqli->error . "\n";
        }
    }

    $totalProcessed += count($batch);
    echo "✅ Batch selesai: $totalProcessed / $totalArtikel artikel diproses.\n";
}

echo "\n🎯 Selesai sync brand USED JEANS. Inserted: $countInsert | Updated: $countUpdate\n";

odbc_close($conn);
$mysqli->close();
?>
