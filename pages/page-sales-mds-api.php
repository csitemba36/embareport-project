<style>
.select2-container--default .select2-selection--single {
    height: 30px; /* Sesuaikan dengan tinggi input lain */
    padding: 2px 5px;
    border: 1px solid #ced4da; /* Warna border input */
    border-radius: 4px; /* Border radius sama */
    font-size: 12px;
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
.select2-container--default .select2-selection--single .select2-selection__arrow {
  top: -3px; /* atau 1px, sesuaikan */
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
.dt-buttons {
        margin-left: 10px;
        display: flex;
        gap: 5px;
    }
	
.card-body {
  font-size: 12px; /* Ukuran font kecil */
}

.card-body table th,
.card-body table td {
  padding: 4px 8px; /* Rapatkan padding tabel */
}

.card-body .btn {
  font-size: 12px;
  padding: 5px 6px; /* Perkecil tombol */
}

/* Paging */
.card-body .pagination {
  font-size: 12px;       /* Kecilkan ukuran font */
}

.card-body .page-item {
  margin: 0 2px;         /* Kurangi jarak antar halaman */
}

.card-body .page-link {
  padding: 2px 6px;      /* Perkecil padding tombol halaman */
  font-size: 12px;       /* Ukuran font tombol */
}
</style>
<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h6 class="mb-0">Filter Data</h6>
	</div>
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <!-- Tanggal Mulai -->
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Mulai</label>
                <input type="date" id="startDate" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>

            <!-- Tanggal Selesai -->
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Selesai</label>
                <input type="date" id="endDate" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
            </div>

            <!-- Action Button -->
			<div class="col-md-2 align-self-end">
                <button type="button" id="ViewButton" class="btn btn-primary w-100">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Data Sales MDS</h6>
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


<script>

$(document).ready(function () {
    $('#ViewButton').click(function () {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        if (!startDate || !endDate) {
            alert("Pilih tanggal mulai dan akhir!");
            return;
        }

        // Reinitialize DataTable
        $('#SRTable').DataTable({
            destroy: true,
            processing: true,
            ajax: {
                url: 'api/get-sales-mds-realtime.php', // ganti sesuai nama file PHP kamu
                type: 'GET',
                data: {
                    startDate: startDate,
                    endDate: endDate
                },
                dataSrc: 'result'
            },
            columns: [
                { data: 'transactionDate' },
                { data: 'storeNumber' },
                { data: 'storeName' },
                { data: 'itemQty' },
                { data: 'salesPrice' },
                { data: 'gross' },
                { data: 'discountAuto' },
                { data: 'discountManual' },
                { data: 'discountEmployee' },
                { data: 'discountFreeItem' },
                { data: 'discountStruk' },
                { data: 'discountPercent' },
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
                { data: 'invLine' },
                { data: 'createdDate' },
                { data: 'supplierNumber' },
                { data: 'supplierName' }
            ]
        });
    });
});


</script>

