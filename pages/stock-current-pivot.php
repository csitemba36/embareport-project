<link href="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.css" rel="stylesheet" />
<style>
	.select2-container--default .select2-selection--single {
		height: 38px; /* Sesuaikan dengan tinggi input lain */
		padding: 6px 12px;
		border: 1px solid #ced4da; /* Warna border input */
		border-radius: 4px; /* Border radius sama */
		font-size: 14px;
	}

	.select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 24px; /* Tengahin text */
	}

	.select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 38px; /* Tengahin icon arrow */
	}

	.select2-container {
		width: 100% !important; /* Lebar penuh kayak input */
	}
</style>
<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h5 class="mb-0">Filter Data</h5>
	</div>
    <div class="card-body">
        <form id="filterForm" class="row g-3 align-items-end">
			<!-- Gudang Asal -->
			<div class="col-md-4">
				<label for="kode_gudang" class="form-label">Gudang</label>
				<select id="kode_gudang" class="form-control">
					<option value="">Pilih Gudang</option>
				</select>
			</div>

			<!-- Action Button: Terapkan Filter -->
			<div class="col-md-2">
				<button type="button" id="filterButton" class="btn btn-primary w-100">Terapkan Filter</button>
			</div>

			<!-- Action Button: Export Excel -->
			<div class="col-md-2">
				<button type="button" id="exportButton" class="btn btn-success w-100">Export Excel</button>
			</div>
		</form>

    </div>
</div>

  <div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Pivot Data</h5>
    </div>
    <div class="card-body" style="overflow-x: auto;">
          <div id="pivotOutput" class="text-center text-muted py-5">
			<h5>Silahkan proses data dahulu.</h5>
			</div>
		<!-- Spinner Loading -->
		<div id="loadingSpinner" class="text-center my-4" style="display: none;">
			<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
				<span class="visually-hidden">Loading...</span>
			</div>
			<div>Loading data, please wait...</div>
		</div>
    </div>
	</div>

</div>



<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI (wajib untuk drag & drop PivotTable) -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- PivotTable JS -->
<script src="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
$(document).ready(function () {
    // Inisialisasi Select2 untuk Gudang
    $("#kode_gudang").select2({
        placeholder: "Pilih Kode Gudang",
        allowClear: true,
        ajax: {
            url: "api/get_warehouses.php",
            type: "GET",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    term: params.term
                };
            },
            processResults: function (data) {
                return { results: data };
            },
            cache: true
        }
    });

    // Saat klik tombol Terapkan Filter
    $("#filterButton").click(function () {
        var kodeGudang = $("#kode_gudang").val();

        if (!kodeGudang) {
            alert("Pilih dulu kode gudang!");
            return;
        }

        // Tampilkan spinner
        $("#loadingSpinner").show();
        $("#pivotOutput").hide(); // Sembunyikan Pivot dulu

        // Load data berdasarkan kode gudang
        $.ajax({
			url: 'api/stock_current.php',
			method: 'GET',
			dataType: 'json',
			data: { kode_gudang: kodeGudang },
			success: function (data) {
				$("#pivotOutput").empty(); // Bersihkan pivot lama

				$("#pivotOutput").pivotUI(
					data, // langsung lempar data disini
					{
						unusedAttrsVertical: false,
						rendererName: "Table",
						aggregatorName: "Sum",
						rows: [],  // << kosong default rows
						cols: [],  // << kosong default cols
						vals: ["QtySaldoAwal"]  // boleh preset kalau mau
					},
					true
				);

				$("#loadingSpinner").hide();
				$("#pivotOutput").show();
			},
			error: function (xhr, status, error) {
				console.error('Gagal load data:', error);
				alert('Gagal load data stock_current.php');
				$("#loadingSpinner").hide();
			}
		});


    });
	
	$("#exportButton").click(function () {
        var pivotTable = $("#pivotOutput table")[0]; // pakai [0] untuk ambil elemen DOM dari jQuery object

        if (!pivotTable) {
            alert("Belum ada data Pivot untuk diekspor!");
            return;
        }

        var wb = XLSX.utils.table_to_book(pivotTable, {sheet: "Pivot Data"});
        XLSX.writeFile(wb, 'pivot-data.xlsx');
    });
});
</script>


</body>
</html>
