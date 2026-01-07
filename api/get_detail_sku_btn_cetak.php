<?php
require_once('../config/connect_db.php');

if (isset($_GET['noRequest']) && isset($_GET['ParentTransaction'])) {
    $noRequest = $_GET['noRequest'];
    $ParentTransaction = $_GET['ParentTransaction'];

    $sql = "SELECT 
                d.KodeItem,
                d.namaBarang,
                d.SizeCode,
                SUM(b.Qty) AS Qty,
                d.HargaJual
            FROM 
                StockRequests a
            JOIN StockRequestItems b ON a.StockRequestId = b.StockRequestId
            JOIN InventoryStocks c ON b.InventoryStockId = c.InventoryStockId
            JOIN Inventories d ON c.InventoryId = d.InventoryId
            WHERE 
                a.NoRequest = ?
            GROUP BY 
                d.KodeItem,
                d.namaBarang,
                d.SizeCode,
                d.HargaJual";

    $stmt = odbc_prepare($conn, $sql);
    $result = odbc_execute($stmt, [$noRequest]);

    if ($result) {
        $data = [];
        $sizeCodes = [];
        $firstKodeItem = null; // untuk ambil param2 dari kode item pertama

        while ($row = odbc_fetch_array($stmt)) {
            $kodeItem = substr($row['KodeItem'], 0, 11);
            $sizeCode = $row['SizeCode'];
            $qty = $row['Qty'];

            if ($firstKodeItem === null) {
                $firstKodeItem = $row['KodeItem']; // simpan kode item pertama
            }

            if (!in_array($sizeCode, $sizeCodes)) {
                $sizeCodes[] = $sizeCode;
            }

            if (!isset($data[$kodeItem])) {
                $data[$kodeItem] = [
                    'KodeItem' => $kodeItem,
                    'Details' => [],
                    'TotalQty' => 0,
                    'HargaJual' => $row['HargaJual']
                ];
            }
            $data[$kodeItem]['Details'][$sizeCode] = $qty;
            $data[$kodeItem]['TotalQty'] += $qty;
        }

        sort($sizeCodes);

        // param2 dari karakter pertama kode item pertama
        $param2 = substr($firstKodeItem, 0, 1);

        // cek role user
        $userRole = $_COOKIE['userrolecode'] ?? '';

        if ($userRole === 'ADM' || substr($userRole, 0, 2) === 'WH') {
            // tombol cetak label di atas tabel
            echo '<div class="mb-2">';
            echo '<form action="http://myemba.co:83/epos/printlb/index.php" method="POST" target="_blank" style="display:inline;">';
            echo '    <input type="hidden" name="param1" value="' . htmlspecialchars($ParentTransaction) . '">';
            echo '    <input type="hidden" name="param2" value="' . htmlspecialchars($param2) . '">';
            echo '    <button type="submit" class="btn btn-warning btn-sm">Cetak Label</button>';
            echo '</form>';
            echo '</div>';
        }

        // cetak tabel
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-striped table-sm mb-0">';
        echo '<thead class="table-primary">';
        echo '<tr>';
        echo '<th>Kode Item</th>';
        foreach ($sizeCodes as $size) {
            echo '<th>Size ' . htmlspecialchars($size) . '</th>';
        }
        echo '<th>Total Qty</th>';
        echo '<th>Harga</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['KodeItem']) . '</td>';
            foreach ($sizeCodes as $size) {
                $qty = isset($row['Details'][$size]) ? number_format($row['Details'][$size], 0) : '-';
                echo '<td class="text-center">' . $qty . '</td>';
            }
            echo '<td class="text-center fw-bold">' . number_format($row['TotalQty'], 0) . '</td>';
            echo '<td class="text-center fw-bold">' . number_format($row['HargaJual'], 0, '', '.') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '<tfoot class="table-light">';
        echo '<tr>';
        echo '<th>Total</th>';

        // total qty per size
        $totals = array_fill_keys($sizeCodes, 0);
        $grandTotalQty = 0;

        foreach ($data as $row) {
            foreach ($sizeCodes as $size) {
                if (isset($row['Details'][$size])) {
                    $totals[$size] += $row['Details'][$size];
                }
            }
            $grandTotalQty += $row['TotalQty'];
        }

        foreach ($sizeCodes as $size) {
            echo '<th class="text-center">' . number_format($totals[$size], 0) . '</th>';
        }

        echo '<th class="text-center fw-bold">' . number_format($grandTotalQty, 0) . '</th>';
        echo '<th></th>';
        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';
        echo '</div>';

    } else {
        echo "<div class='alert alert-warning'>No details found.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No 'noRequest' or 'ParentTransaction' parameter found.</div>";
}

odbc_close($conn);
?>
