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

tr.filters input {
    font-size: 12px;
}	
</style>

<div class="card mb-2 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h6 class="mb-0">Filter Data</h6>
	</div>
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <!-- Gudang Target -->
			<div class="col-md-2">
                <label for="brand" class="form-label">Merk</label>
                <select id="brand" class="form-control">
                    <option value="">Pilih Merk</option>
                </select>
            </div>
			<div class="col-md-2">
                <label for="customer" class="form-label">Grup</label>
                <select id="customer" class="form-control">
                    <option value="">Pilih Grup</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="gudang" class="form-label">Gudang/Toko</label>
                <select id="gudang" class="form-control">
                    <option value="">Pilih Gudang/Toko</option>
                </select>
            </div>
			<div class="col-md-2">
				<div class="d-flex align-items-center justify-content-between mb-0">
					<label for="kode_item" class="form-label mb-1">Kode Item</label>
					<div class="form-check form-switch">						
						<div id="kodeItemLoading" style="display:none;" class="spinner-border text-primary spinner-border-sm" role="status">
						</div>
						<input class="form-check-input" type="checkbox" id="filterToggle">
					</div>
					
				</div>
				<select id="kode_item" class="form-control" disabled>
					<option value="">Pilih Kode Item</option>
				</select>
			</div>
			
            <!-- Action Button -->
            <div class="col-md-2 align-self-end">
                <button type="button" id="filterButton" class="btn btn-primary w-100">Tampilkan Data</button>
            </div>
        </form>
    </div>
</div>


<div class="card mb-2 shadow-sm">

    <div id="collapseStockCurrent" class="collapse show">
        <div class="card-body">
            <!-- Tabel -->
            <div class="table-responsive">
				<!-- Filter Select2 per Kolom -->	
                <table id="itemTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%">
                    <thead class="table-primary">
						<tr class="filters">
							<!--<th><input type="text" placeholder="🔍️ Merk" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Customer" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Gudang" class="form-control form-control-sm" /></th>-->
							<th><input type="text" placeholder="🔍️ Kode Item" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Nama Barang" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Part Number" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Range" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Style" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Bahan" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Model" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Warna" class="form-control form-control-sm" /></th>
							<th><input type="text" placeholder="🔍️ Size" class="form-control form-control-sm" /></th>
							<th></th>
							<th></th> <!-- Qty Max -->
							
						</tr>
						<tr>
							<!--<th>Merk</th>
							<th>Customer</th>
							<th>Gudang / Toko</th>-->
							<th>Kode Item</th>
							<th>Nama Barang</th>
							<th>Part Number</th>
							<th>Range</th>
							<th>Style</th>
							<th>Bahan</th>
							<th>Model</th>
							<th>Warna</th>
							<th>Size</th>
							<th>SaldoAkhir</th>
							<th style="width:100px">Qty Max</th>
						</tr>
					</thead>
                </table>
            </div>
        </div>
    </div>
</div>







<script>



	$('#exportExcel').on('click', function () {
	
		const table = document.querySelector('.pvtTable');
		if (!table) {
			alert('Tabel Pivot belum tersedia!');
			return;
		}

		const wb = XLSX.utils.book_new();
		const ws = XLSX.utils.table_to_sheet(table);
		XLSX.utils.book_append_sheet(wb, ws, 'PivotData');
		XLSX.writeFile(wb, 'PivotTable.xlsx');
	});
	
	$('#buatPivot').on('click', function () {
	  const data = dataTableToObjects($('#itemTable').DataTable());

	  if (data.length === 0) {
		alert('Tidak ada data untuk ditampilkan.');
		return;
	  }

	  $('#pivotContainer').pivotUI(data, {
		rows: ['NamaBrand'],
		cols: ['Gudang'],
		vals: ['SaldoAkhir', 'QtySaldoAwal', 'QtyTerima', 'QtyKeluar'],
		aggregatorName: 'Sum',
		rendererName: 'Table'
	  });
	});


	// Fungsi konversi datatable ke array of objects
	function dataTableToObjects(dataTable) {
	  return dataTable.rows({ search: 'applied' }).data().toArray();
	}

	
	
	$('#filterToggle').on('change', function () {
		const isChecked = $(this).is(':checked');

		$('#kode_item').prop('disabled', !isChecked).trigger('change.select2');
		$('#filterStatus').text(isChecked ? 'Filter aktif' : 'Filter nonaktif');

		if (isChecked) {
			const merk = $('#brand').val();
			const kodeGudang = $('#gudang').val();

			// Tampilkan loading
			$('#kodeItemLoading').show();

			$.ajax({
				url: 'api/stock-current.php',
				data: {
					merk: merk,
					kode_gudang: kodeGudang
				},
				success: function (response) {
					const data = response.data || [];

					$('#kode_item').empty().append('<option value="">Pilih Kode Item</option>');

					data.forEach(function (item) {
						if (item.KodeItem) {
							$('#kode_item').append(
							  '<option value="' + item.KodeItem + '">' + item.KodeItem + ' - ' + item.PartNumber + '</option>'
							);
						}
					});

					$('#kode_item').trigger('change.select2');
				},
				error: function () {
					alert('Gagal mengambil data kode item');
				},
				complete: function () {
					// Sembunyikan loading
					$('#kodeItemLoading').hide();
				}
			});
		} else {
			$('#kode_item').empty().append('<option value="">Pilih Kode Item</option>').trigger('change.select2');
		}
	});



</script>

<script>
	$(document).ready(function() {
		var table;
			
		getBrand();
		//getDepartemen();
		getGudang();
		getCustomer();
		
		table = $('#itemTable').DataTable({
			scrollX: true,
			// konfigurasi lainnya
		});
			
		$('#kode_item').select2({
			placeholder: 'Pilih Kode Item'
		});
		
		// Event ketika tombol filter diklik
		$('#filterButton').click(function () {
			
			const merk = $('#brand').val();
			const kodeGudang = $('#gudang').val();

			if (!merk) {
				alert('Pilih merk terlebih dahulu!');
				return;
			}

			if (!kodeGudang) {
				alert('Pilih gudang terlebih dahulu!');
				return;
			}
			
			if (table) table.destroy();
			
			const filterAktif = $('#filterToggle').is(':checked');
			const kodeItem = $('#kode_item').val();

			table = $('#itemTable').DataTable({
				scrollX: true,
				dom: '<"row align-items-center mb-3"<"col-md-6 d-flex"lB><"col-md-6"f>>tip',
				buttons: [
					{ extend: 'copy', text: 'Copy Table', className: 'btn btn-success btn-sm' },
					{ extend: 'excel', text: 'Export Excel', className: 'btn btn-info btn-sm' },
					{ extend: 'pdf', text: 'Export PDF', className: 'btn btn-danger btn-sm' }
				],
				lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
				ajax: {
					url: 'api/stock-current-filter.php',
					data: function (d) {
						d.merk = merk;
						//d.kode_gudang = 'C-G0001';
						d.kode_gudang = kodeGudang;

						if (filterAktif && kodeItem) {
							d.kode_item = kodeItem;
						}
					},
					dataSrc: 'data'
				},
				columns: [
							//{ data: 'NamaBrand' },
							//{ data: 'NamaCustomer' },
							//{ data: 'Gudang' },
							{ data: 'KodeItem' },
							{ data: 'NamaBarang' },
							{ data: 'PartNumber' },
							{ data: 'Range' },
							{ data: 'Style' },
							{ data: 'Bahan' },
							{ data: 'Model' },
							{ data: 'Warna' },
							{ data: 'Size' },
							{ data: 'SaldoAkhir' },
							{
								data: 'QtyMax',
								render: function(data, type, row, meta) {
									return '<div class="input-group input-group-sm">' +
												'<input type="number" size="5" class="form-control form-control-sm qty-input" value="' + data + '" data-row="' + meta.row + '" />' +
												'<button class="btn btn-success btn-sm btn-save-qty" data-row="' + meta.row + '">' +
													'<i class="bi bi-check"></i>' +
												'</button>' +
										   '</div>';
								}
							}
						],

				
				initComplete: function () {
					this.api().columns().every(function (index) {
						// Skip kolom 12 - 15 (index mulai dari 0)
						if (index >= 12) return;

						const column = this;
						$('input', $('.filters th')[index]).on('keyup change', function () {
							if (column.search() !== this.value) {
								column.search(this.value).draw();
							}
						});
					});
				}
			});


		});

		table.columns.adjust().draw();

		
	});
	
	$('#itemTable tbody').on('click', '.btn-save-qty', function () {
		alert();
		var rowIdx = $(this).data('row');
		var input = $('.qty-input[data-row="' + rowIdx + '"]');
		var newQty = input.val();

		var table = $('#itemTable').DataTable();
		var rowData = table.row(rowIdx).data();
		rowData.QtyMax = newQty;
		table.row(rowIdx).data(rowData).invalidate();

		// Optional: Simpan ke backend
		/*
		$.post('update_qtymax.php', {
			id: rowData.id, // atau kolom unik lainnya
			QtyMax: newQty
		}, function(response) {
			alert('QtyMax berhasil diupdate!');
		}).fail(function() {
			alert('Gagal mengupdate QtyMax');
		});
		*/
	});
	
	
	function getGudang(){
		$('#gudang').select2({
			placeholder: 'Pilih Gudang/Toko',
		});
	}
	
	function getBrand(){
		$('#brand').select2({
        placeholder: 'Pilih Merk',
        allowClear: true,
			ajax: {
				url: 'api/get_brand.php', // Ganti dengan path file PHP kamu
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						term: params.term // Kirim parameter pencarian ke PHP
					};
				},
				processResults: function (data) {
					return {
						results: data
					};
				},
				cache: true
			}
		});
	}
	
	function getDepartemen(){
		$('#departemen').select2({
        placeholder: 'Pilih Departemen',
        allowClear: true,
			ajax: {
				url: 'api/get_departemen.php', // Ganti dengan path file PHP kamu
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						term: params.term // Kirim parameter pencarian ke PHP
					};
				},
				processResults: function (data) {
					return {
						results: data
					};
				},
				cache: true
			}
		});
	}
	
	function getCustomer(){
		$('#customer').select2({
        placeholder: 'Pilih Customer',
        allowClear: true,
			ajax: {
				url: 'api/get_group_customer.php', // Ganti dengan path file PHP kamu
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						term: params.term // Kirim parameter pencarian ke PHP
					};
				},
				processResults: function (data) {
					return {
						results: data
					};
				},
				cache: true
			}
		});
	}
	
	$('#customer').on('change', function () {
        let selectedId = $(this).val();
		
		//alert(selectedId);

        // Reset gudang saat customer berubah
        $('#gudang').val(null).trigger('change');

        // Inisialisasi ulang Select2 dengan data AJAX
        $('#gudang').select2({
            placeholder: 'Pilih Gudang',
            allowClear: true,
            ajax: {
                url: 'api/get_warehouses_by_customer.php',
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term || '',
                        KodeLgn: selectedId
                    };
                },
                processResults: function (data) {
                    if (data.error) {
                        alert(data.error);
                        return { results: [] };
                    }
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    });
	
	
		
	
</script>
