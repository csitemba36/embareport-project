<!DOCTYPE html>
<html>
<head>
    <title>Test PivotTable</title>
    <meta charset="utf-8">

    <!-- jQuery & jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- PivotTable -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.css">
    <script src="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.js"></script>
</head>
<body>
    <div id="pivotContainer"></div>

    <script>
        $(function() {
            const testData = [
                { NamaBrand: "Toyota", KodeGudang: "G01", QtySaldoAwal: 100, QtyTerima: 20, QtyKeluar: 30, SaldoAkhir: 90 },
                { NamaBrand: "Honda", KodeGudang: "G01", QtySaldoAwal: 50, QtyTerima: 10, QtyKeluar: 5, SaldoAkhir: 55 }
            ];

            $('#pivotContainer').pivotUI(testData, {
                rows: ['NamaBrand'],
                cols: ['KodeGudang'],
                vals: ['QtySaldoAwal', 'QtyTerima', 'QtyKeluar', 'SaldoAkhir'],
                aggregatorName: 'Sum',
                rendererName: 'Table'
            });
        });
    </script>
</body>
</html>
