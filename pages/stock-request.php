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
                <input type="date" id="tanggal_awal" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>

            <!-- Tanggal Selesai -->
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Selesai</label>
                <input type="date" id="tanggal_akhir" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
            </div>

            <!-- IsClosed Filter -->
            <div class="col-md-3">
                <label for="isClosedFilter" class="form-label">Status</label>
                <select id="isClosedFilter" class="form-control form-control-sm">
                    <option value="">-- all --</option>
                    <option value="0">On Going</option>
                    <option value="1">Finish</option>
					<!--<option value="2">Pending</option>-->
                </select>
            </div>

            <!-- Action Button -->
            <div class="col-md-2 align-self-end">
                <button type="button" id="filterButton" class="btn btn-primary w-100">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Data Stock Request</h6>
    </div>
    <div class="card-body">
		<!-- Tabel -->
		<div class="table-responsive">
			<table id="SRTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%">
				<thead class="table-primary">
					<tr class="filters">
						<th><input type="text" class="form-control form-control-sm" placeholder="Cari No Request" /></th>
						<th><input type="text" class="form-control form-control-sm" placeholder="Cari Tgl Request" /></th>
						<th><input type="text" class="form-control form-control-sm" placeholder="Cari Tgl Entry" /></th>
						<th><input type="text" class="form-control form-control-sm" placeholder="Cari Kode Gudang" /></th>
						<th><input type="text" class="form-control form-control-sm" placeholder="Cari Gudang Target" /></th>
						<th><input type="text" class="form-control form-control-sm" placeholder="Cari Parent" /></th>
						<th></th>
						<th></th>
					</tr>
					<tr>
						<th>No Request</th>
						<th>Tgl Request</th>
						<th>Tgl Entry</th>					
						<th>Kode Gudang</th>
						<th>Gudang Target</th>
						<th>Parent Transaction</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
			</table>

		</div>
    </div>
</div>





<script>
	$(document).ready(function() {
		$('#openStockRequestModal').on('click', function() {
			$('#stockRequestModal').modal('show');
			//loadStockRequestData();
		});
  
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
						term: params.term // Kirim teks pencarian ke server
					};
				},
				processResults: function (data) {
					return { results: data };
				},
				cache: true
			}
		});
		
		$("#gudang_target").select2({
			placeholder: "Pilih Kode Gudang",
			allowClear: true,
			ajax: {
				url: "api/get_warehouses.php",
				type: "GET",
				dataType: "json",
				delay: 250,
				data: function (params) {
					return {
						term: params.term // Kirim teks pencarian ke server
					};
				},
				processResults: function (data) {
					return { results: data };
				},
				cache: true
			}
		});
		
		
		    
	});
	
	
	// Kalau klik button filter
	$(document).ready(function() {
		// Ambil default filter dari form
		const tanggalAwal = $('#tanggal_awal').val();
		const tanggalAkhir = $('#tanggal_akhir').val();
		const kodeGudang = $('#kode_gudang').val();
		const gudangTarget = $('#gudang_target').val();
		const isClosed = $('#isClosedFilter').val();

		// Load pertama kali
		loadData(tanggalAwal, tanggalAkhir, kodeGudang, gudangTarget, isClosed);
		
		

		// Kalau klik tombol filter
		$('#filterButton').on('click', function() {
			const tanggalAwal = $('#tanggal_awal').val();
			const tanggalAkhir = $('#tanggal_akhir').val();
			const kodeGudang = $('#kode_gudang').val();
			const gudangTarget = $('#gudang_target').val();
			const isClosed = $('#isClosedFilter').val();

			loadData(tanggalAwal, tanggalAkhir, kodeGudang, gudangTarget, isClosed);
		});
	
		$('#SRTable tbody').on('click', '.expand-btn', function() {
			var tr = $(this).closest('tr');
			var row = table.row(tr);

			if (row.child.isShown()) {
				// Child lagi terbuka, kita tutup
				row.child.hide();
				tr.removeClass('shown');
				$(this).text('Show Detail');
			} else {
				var noRequest = row.data().NoRequest;

				// Panggil API detail
				$.ajax({
					url: 'api/get_detail_sku.php', // ganti dengan nama file PHP kamu
					type: 'GET',
					data: { noRequest: noRequest },
					success: function(response) {
						row.child(response).show();
						tr.addClass('shown');
						$(tr).find('.expand-btn').text('Hide Detail');
					},
					error: function() {
						alert('Gagal mengambil detail');
					}
				});
			}
		});
	
	});
	
	var table;
	


	function loadData(tanggalAwal, tanggalAkhir, kodeGudang, gudangTarget, isClosed) {
		if (table) table.destroy();

		table = $('#SRTable').DataTable({
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
			ajax: {
				url: 'api/stock-request.php',
				method: 'GET',
				data: {
					tanggal_awal: tanggalAwal,
					tanggal_akhir: tanggalAkhir,
					kode_gudang: kodeGudang,
					gudang_target: gudangTarget,
					is_closed: isClosed
				},
				dataSrc: function (json) {
					// ✅ Hitung jumlah NoRequest
					let countMap = {};
					json.data.forEach(item => {
						countMap[item.NoRequest] = (countMap[item.NoRequest] || 0) + 1;
					});

					// ✅ Urutkan: NoRequest unik (count 1) dulu, baru yang dobel
					json.data.sort((a, b) => {
						let countA = countMap[a.NoRequest];
						let countB = countMap[b.NoRequest];

						// Urutan: count kecil dulu (unik), baru count besar (dobel)
						return countA - countB;
					});

					return json.data;
				}
			},
			columns: [
				{ data: 'NoRequest' },
				{ data: 'TglRequest' },
				{ data: 'TglEntry' },
				{
					data: null,
					render: function(data, type, row, meta) {
						return row.B_KodeGudang + ' - ' + row.GudangAsal;
					},
					title: 'Gudang Sumber'
				},
				{
					data: null,
					render: function(data, type, row, meta) {
						return row.GdgTarget + ' - ' + row.SR_gdg_target;
					},
					title: 'Gudang Target'
				},
				{ data: 'ParentTransaction' },
				{ 
					data: 'isClosed',
					render: function(data, type, row, meta) {
						if (data == 1) {
							return '<span class="badge bg-danger">Finish</span>';
						} else {
							return '<span class="badge bg-success">On Going</span>';
						}
					}
				},
				{
					data: null,
					defaultContent: '<button class="expand-btn btn btn-sm btn-primary">Show Detail</button>',
					orderable: false
				},

			],
			
			language: {
				url: 'assets/id.json'
			}
		});

		table.on('draw', function() {
			highlightClosedRows();
		});
		
		// Tambahkan input filter setelah render
		$('#SRTable thead tr.filters th').each(function(i) {
			$('input', this).on('keyup change', function() {
				if (table.column(i).search() !== this.value) {
					table.column(i).search(this.value).draw();
				}
			});
		});
	}

	
	
	function highlightClosedRows() {
		const noRequestCount = {};

		// Hitung jumlah kemunculan setiap NoRequest
		table.rows({ search: 'applied' }).every(function() {
			const data = this.data();
			const noRequest = data.NoRequest;

			if (noRequest in noRequestCount) {
				noRequestCount[noRequest]++;
			} else {
				noRequestCount[noRequest] = 1;
			}
		});

		// Tandai baris yang NoRequest-nya muncul lebih dari sekali
		table.rows({ search: 'applied' }).every(function() {
			const data = this.data();
			const row = this.node();
			const noRequest = data.NoRequest;

			if (noRequestCount[noRequest] > 1) {
				$(row).addClass('table-success');
			} else {
				$(row).removeClass('table-success');
			}
		});
	}




</script>
