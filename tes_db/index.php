<?php
// Konfigurasi koneksi SQL Server
$serverName = "192.168.8.126";
$connectionOptions = [
    "Database" => "EMBdb002",
    "Uid" => "db",
    "PWD" => "db"
];

// Koneksi ke SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Query
$sql = "
SELECT *
FROM (
    SELECT  
        SUBSTRING(b.KodeItem,1,13) AS KodeItem,
        b.NamaBarang, b.PartNumber, c.Nama AS NamaBrand,
        a.KodeGudang, a.KodeGudang + ' - ' + w.NamaGudang AS Gudang,
        cu.NamaLgn AS NamaCustomer,
        CAST(a.QtySaldoAwal AS INT) AS QtySaldoAwal,
        CAST(a.QtyTerima AS INT) AS QtyTerima,
        CAST(a.QtyKeluar AS INT) AS QtyKeluar,
        CAST((a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) AS INT) AS SaldoAkhir,
        SUBSTRING(b.KodeItem,2,1) AS Range,
        SUBSTRING(b.KodeItem,3,2) AS Style,
        SUBSTRING(b.KodeItem,5,3) AS Bahan,
        SUBSTRING(b.KodeItem,2,8) AS Model,
        SUBSTRING(b.KodeItem,10,2) AS Warna,
        SUBSTRING(b.KodeItem,12,2) AS Size,
        a.QtyMax, b.HargaJual, b.weight,
        ROW_NUMBER() OVER (ORDER BY b.KodeItem asc) AS RowNum
    FROM InventoryStocks a
    JOIN Inventories b ON a.InventoryId = b.InventoryId
    LEFT JOIN DepartmentBrands c ON LEFT(b.KodeItem,1) = c.Kode
    LEFT JOIN Warehouses w ON a.KodeGudang = w.KodeGudang
    LEFT JOIN Customers cu ON w.CustomerId = cu.CustomerId
    WHERE LEN(b.KodeItem) = 13 
      AND (a.QtySaldoAwal + a.QtyTerima + a.QtyKeluar) <> 0 
      AND LEFT(b.KodeItem, 1) = 'D' 
      AND a.KodeGudang IN ('C-G0001') 
      AND b.typestock = 'INV'
) AS Temp
WHERE RowNum BETWEEN 1 AND 100
";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Stok</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Data Stok Current</h2>
    <table>
        <thead>
            <tr>
                <th>Kode Item</th>
                <th>Nama Barang</th>
                <th>Part Number</th>
                <th>Brand</th>
                <th>Gudang</th>
                <th>Customer</th>
                <th>Saldo Awal</th>
                <th>Terima</th>
                <th>Keluar</th>
                <th>Saldo Akhir</th>
                <th>Range</th>
                <th>Style</th>
                <th>Bahan</th>
                <th>Model</th>
                <th>Warna</th>
                <th>Size</th>
                <th>Qty Max</th>
                <th>Harga Jual</th>
                <th>Weight</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['KodeItem']); ?></td>
                <td><?= htmlspecialchars($row['NamaBarang']); ?></td>
                <td><?= htmlspecialchars($row['PartNumber']); ?></td>
                <td><?= htmlspecialchars($row['NamaBrand']); ?></td>
                <td><?= htmlspecialchars($row['Gudang']); ?></td>
                <td><?= htmlspecialchars($row['NamaCustomer']); ?></td>
                <td><?= $row['QtySaldoAwal']; ?></td>
                <td><?= $row['QtyTerima']; ?></td>
                <td><?= $row['QtyKeluar']; ?></td>
                <td><?= $row['SaldoAkhir']; ?></td>
                <td><?= $row['Range']; ?></td>
                <td><?= $row['Style']; ?></td>
                <td><?= $row['Bahan']; ?></td>
                <td><?= $row['Model']; ?></td>
                <td><?= $row['Warna']; ?></td>
                <td><?= $row['Size']; ?></td>
                <td><?= $row['QtyMax']; ?></td>
                <td><?= $row['HargaJual']; ?></td>
                <td><?= $row['weight']; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</body>
</html>
<?php
// Tutup koneksi
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
