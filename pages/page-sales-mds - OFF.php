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

.highlight {
    background-color: #fff3cd !important; /* kuning lembut */
}

	.dataTables_length {
        margin-right: 10px;
        display: flex;
        align-items: center;
    }
    .dt-buttons {
        margin-left: 10px;
        display: flex;
        gap: 5px;
    }
	
	#loadingModal .modal-content {
		border: none;
		box-shadow: none;
		background-color: transparent;
	}
</style>
<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h5 class="mb-0">Filter Data</h5>
	</div>
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <!-- Tanggal Mulai -->
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Mulai</label>
                <input type="date" id="tanggal_awal" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>

            <!-- Tanggal Selesai -->
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Selesai</label>
                <input type="date" id="tanggal_akhir" class="form-control" value="<?= date('Y-m-t') ?>">
            </div>

            <!-- Action Button -->
            <div class="col-md-3 align-self-end">
                <button type="button" id="filterButton" class="btn btn-primary w-100">Sync Sales MDS</button>
            </div>
			<div class="col-md-3 align-self-end">
                <button type="button" id="ViewButton" class="btn btn-primary w-100">View Data</button>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h5 class="mb-0">Data Sales MDS</h5>
    </div>
    <div class="card-body">
		<!-- Tabel -->
		<div class="table-responsive">
			<table id="SRTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%">
				<thead class="table-primary">
					<tr>
						<th>Transaction Date</th>
						<th>Store Number</th>
						<th>Store Name</th>
						<th>Item Qty</th>
						<th>Sales Price</th>
						<th>Gross</th>
						<th>Discount Auto</th>
						<th>Discount Manual</th>
						<th>Discount Employee</th>
						<th>Discount Free Item</th>
						<th>Discount Struk</th>
						<th>Discount %</th>
						<th>Nett Amount</th>
						<th>SKU</th>
						<th>Barcode</th>
						<th>Class Name</th>
						<th>Group Name</th>
						<th>Brand Name</th>
						<th>Dept Name</th>
						<th>Color Name</th>
						<th>Size Name</th>
						<th>Reg Number</th>
						<th>Inv Number</th>
						<th>Inv Line</th>
						<th>Created Date</th>
						<th>Supplier Number</th>
						<th>Supplier Name</th>
					</tr>
				</thead>
			</table>
		</div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="syncModalLabel">Peringatan Sinkronisasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Catatan:</strong> Jangan pilih rentang tanggal terlalu panjang saat menarik data karena akan memperlambat proses sinkronisasi.</p>
        <p>Apakah Anda yakin ingin melanjutkan sinkronisasi data Sales MDS?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmSync" class="btn btn-primary">OK !!! I Agree</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body py-5">
        <div class="spinner-border text-primary mb-3" role="status"></div>
        <h5>Sinkronisasi sedang berlangsung...</h5>
        <p>Mohon tunggu sebentar.</p>
      </div>
    </div>
  </div>
</div>


<script>
$(document).ready(function () {
	
	var table;

	// Inisialisasi kosong
	table = $('#SRTable').DataTable({
		
		data: [], // kosongkan saat awal
		columns: [
			{ data: 'transactionDate' },
			{ data: 'storeNumber' },
			{ data: 'storeName' },
			{ data: 'itemQty' },
			{ data: 'salesPrice' },
			{ data: 'gross' },
			{ data: 'discAuto' },
			{ data: 'discManual' },
			{ data: 'discEmployee' },
			{ data: 'discFreeItem' },
			{ data: 'discStruk' },
			{ data: 'discPct' },
			{ data: 'nettAmount' },
			{ data: 'sku' },
			{ data: 'barcode' },
			{ data: 'className' },
			{ data: 'groupName' },
			{ data: 'brandName' },
			{ data: 'deptName' },
			{ data: 'colorName' },
			{ data: 'sizeName' },
			{ data: 'regNumber' },
			{ data: 'invNumber' },
			{ data: 'invLineNumber' },
			{ data: 'createdDate' },
			{ data: 'supplierNumber' },
			{ data: 'supplierName' }
		],
		scrollX: true, // biarkan horizontal scroll aktif kalau kolom banyak
		paging: true,
		searching: true,
		responsive: false
	});

	$('#ViewButton').on('click', function () {
		if ($.fn.DataTable.isDataTable('#SRTable')) {
			$('#SRTable').DataTable().destroy();
		}

		$('#SRTable').DataTable({
			scrollX: true,
			dom: '<"row align-items-center mb-3"<"col-md-6 d-flex"lB><"col-md-6"f>>tip',
			buttons: [
				{
					extend: 'copy',
					text: 'Copy Table',
					className: 'btn btn-success btn-sm'
				},
				{
					extend: 'excel',
					text: 'Export Excel',
					className: 'btn btn-info btn-sm'
				},
				{
					extend: 'pdf',
					text: 'Export PDF',
					className: 'btn btn-danger btn-sm'
				}
			],
			lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
			order: [[1, 'desc']], // <<<<<< ADD INI
			ajax: {
				url: 'api/api-sales-mds.php',
				type: 'GET', // atau 'POST' jika server menerima POST
				data: function (d) {
					d.startdate = $('#tanggal_awal').val();   // ambil dari input tanggal_awal
					d.enddate = $('#tanggal_akhir').val();    // ambil dari input tanggal_akhir
				},
				dataSrc: ''
			},
			columns: [
				{ data: 'transactionDate' },
				{ data: 'storeNumber' },
				{ data: 'storeName' },
				{ data: 'itemQty' },
				{ data: 'salesPrice' },
				{ data: 'gross' },
				{ data: 'discAuto' },
				{ data: 'discManual' },
				{ data: 'discEmployee' },
				{ data: 'discFreeItem' },
				{ data: 'discStruk' },
				{ data: 'discPct' },
				{ data: 'nettAmount' },
				{ data: 'sku' },
				{ data: 'barcode' },
				{ data: 'className' },
				{ data: 'groupName' },
				{ data: 'brandName' },
				{ data: 'deptName' },
				{ data: 'colorName' },
				{ data: 'sizeName' },
				{ data: 'regNumber' },
				{ data: 'invNumber' },
				{ data: 'invLineNumber' },
				{ data: 'createdDate' },
				{ data: 'supplierNumber' },
				{ data: 'supplierName' }
			],
			
		});

	});

	// Saat tombol "Sync Sales MDS" diklik, tampilkan modal
	$('#filterButton').on('click', function () {
		$('#syncModal').modal('show');
	});
	
	$('#confirmSync').on('click', function () {
		// Tutup modal peringatan
		$('#syncModal').modal('hide');

		// Ambil nilai tanggal dari input
		const tanggalAwal = $('#tanggal_awal').val();
		const tanggalAkhir = $('#tanggal_akhir').val();

		// Tampilkan modal loading
		$('#loadingModal').modal('show');

		// Jalankan AJAX
		$.ajax({
			url: '../apimcp/sales/getsales.php',
			method: 'POST',
			data: {
				tanggal_awal: tanggalAwal,
				tanggal_akhir: tanggalAkhir
			},
			success: function (response) {
				alert('Sinkronisasi berhasil!');
				// Tambahkan aksi jika ingin reload tabel atau data
			},
			error: function () {
				alert('Sinkronisasi gagal! Silakan coba lagi.');
			},
			complete: function () {
				// Sembunyikan loading
				$('#loadingModal').modal('hide');
			}
		});
	});





});
</script>

