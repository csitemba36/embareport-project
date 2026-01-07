<?php
$gudangSumber = $_POST['gudang_sumber'] ?? '';
$gudangTarget = $_POST['gudang_target'] ?? '';
$kodeMerk     = $_POST['kode_merk'] ?? '';

// Validasi sederhana
if (!$gudangSumber || !$gudangTarget || !$kodeMerk) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi.']);
    exit;
}

// Koneksi ODBC
$dsn = "dbmaserp";
$username = "db";
$password = "db";
$conn = odbc_connect($dsn, $username, $password);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit;
}

// Escape input (basic)
$gudangSumber = str_replace("'", "''", $gudangSumber);
$gudangTarget = str_replace("'", "''", $gudangTarget);
$kodeMerk     = str_replace("'", "''", $kodeMerk);

// Query disusun langsung (tanpa ? parameter di bagian string concatenation)
$sql = "
INSERT INTO [InventoryStocks] (
    [InventoryStockId], [KodeGudang], [InventoryId],
    [QtySaldoAwal], [QtyTerima], [QtyKeluar],
    [JlhSaldoAwal], [JlhTerima], [JlhKeluar],
    [QtyJual], [JlhJual], [JlhHpp],
    [QtyPakai], [JlhPakai], [QtyAwalTahun], [JlhAwalTahun],
    [QtyBoSo], [QtyIndent], [QtyBoPo], [QtyMin], [QtyMax],
    [HPokokAwal], [HPokokSkr], [Aisle], [Rack], [Shelf],
    [IsFromCloseBook], [DataSourceType], [ExInventoryStockId]
)
SELECT 
    NEWID(), '$gudangTarget', S.[InventoryId],
    0,0,0,0,0,0,0,0,0,0,0,0,0,
    0,0,0,0,0,0,0,
    S.[Aisle], S.[Rack], S.[Shelf],
    0, S.[DataSourceType], S.[ExInventoryStockId]
FROM [InventoryStocks] S
INNER JOIN Inventories I ON S.InventoryId = I.InventoryId
WHERE S.KodeGudang = '$gudangSumber'
  AND I.KodeMerk = '$kodeMerk'
  AND ('$gudangTarget' + CONVERT(NVARCHAR(100), S.InventoryId)) NOT IN (
      SELECT (KodeGudang + CONVERT(NVARCHAR(100), InventoryId)) FROM [InventoryStocks]
  )
";

// Eksekusi query
$result = odbc_exec($conn, $sql);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil disalin.']);
} else {
    $error = odbc_errormsg($conn);
    echo json_encode(['success' => false, 'message' => 'Query gagal: ' . $error]);
}

odbc_close($conn);
?>
