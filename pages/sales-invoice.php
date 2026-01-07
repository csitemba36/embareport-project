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
	.cilik{
		font-size: 12px;
	}

	/*pivot*/
	.pvtTable th {
		vertical-align: top !important;
	}
</style>

<div class="card mb-2 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h6 class="mb-0">Filter Data</h6>
	</div>
    <div class="card-body">
        <form id="filterForm" class="row g-3">

            <!-- Merk -->
            <div class="col-md-2">
                <label for="filter-merk" class="form-label">Merk</label>
                <select id="filter-merk" class="form-select form-select-sm select2">
                    <option value="">Pilih Merk</option>
                </select>
            </div>

            <!-- Periode -->
            <div class="col-md-2">
                <label class="form-label">Periode Awal</label>
                <input type="date" name="start_date" id="start_date" 
                       class="form-control form-control-sm" 
                       value=""
					   >
            </div>
            <div class="col-md-2">
                <label class="form-label">Periode Akhir</label>
                <input type="date" name="end_date" id="end_date" 
                       class="form-control form-control-sm" 
                       value=""
					   >
            </div>

            <!-- Group -->
            <div class="col-md-2">
                <label for="filter-group" class="form-label">Group</label>
                <select id="filter-group" class="form-select form-select-sm select2">
                    <option value="">-- Choose --</option>
                </select>
            </div>

            <!-- Gudang / Toko -->
            <div class="col-md-3">
                <label for="filter-gudang" class="form-label">Gudang / Toko</label>
                <select id="filter-gudang" class="form-select form-select-sm select2">
                    <option value="">-- Choose --</option>
                </select>
            </div>

            <!-- Range -->
            <div class="col-md-2 mt-2">
                <label for="filter-range" class="form-label">Range</label>
                <select id="filter-range" class="form-select form-select-sm select2" multiple>
                    <option value="">-- Choose --</option>
                </select>
            </div>

            <!-- Style -->
            <div class="col-md-2 mt-2">
                <label for="filter-style" class="form-label">Fitting</label>
                <select id="filter-style" class="form-select form-select-sm select2" multiple>
                    <option value="">-- Choose --</option>
                </select>
            </div>

            <!-- Material -->
            <div class="col-md-2 mt-2">
                <label for="filter-material" class="form-label">Bahan</label>
                <select id="filter-material" class="form-select form-select-sm select2" multiple>
                    <option value="">-- Choose --</option>
                </select>
            </div>

            <!-- Color -->
            <div class="col-md-2 mt-2">
                <label for="filter-color" class="form-label">Warna</label>
                <select id="filter-color" class="form-select form-select-sm select2" multiple>
                    <option value="">-- Choose --</option>
                </select>
            </div>

            <!-- Size -->
            <div class="col-md-2 mt-2">
                <label for="filter-size" class="form-label">Ukuran</label>
                <select id="filter-size" class="form-select form-select-sm select2" multiple>
                    <option value="">-- Choose --</option>
                </select>
            </div>

			<div class="col-md-2 pt-6">
				<div class="form-group w-100">
				<label for="f">&nbsp;</label>
					<button id="btn-retrieve" type="button" class="btn btn-primary btn-sm py-1 w-100" style="font-size: 0.85rem;">
						Retrieve
					</button>
				</div>
			</div>

        </form>
    </div>
</div>


<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Data Sales Invoice</h6> <button class="btn btn-sm btn-success me-2" id="DTexportExcel"  style="font-size:0.8rem; line-height:1.2;">Datatable Export Excel</button>
    </div>
    <div class="card-body">
	<!-- Tabel -->
	<div class="table-responsive">
		<table id="invoiceTable" class="table table-bordered table-hover table-sm table-striped nowrap" style="width:100%">
			<thead class="table-primary">
				<tr>
					<th>No Bukti</th>
					<th>Tgl Faktur</th>
					<th>Bulan</th>
					<th>Gudang</th>
					<th>Group</th>
					<th>Kode Item</th>
					<th>Nama Barang</th>
					<th class="text-end">Qty</th>
					<th class="text-end">Harga Jual</th>
					<th class="text-end">Diskon</th>
					<th class="text-end">Harga Net</th>
					<th>Part Number</th>
					<th>Range</th>
					<th>Fitting</th>
					<th>Bahan</th>
					<th>Model</th>
					<th>Warna</th>
					<th>Ukuran</th>
					<th>Wilayah</th>
					<th>Area</th>
					<th>Kota</th>
					<th>Dir. Operasional</th>
					<th>Reg. Manajer</th>
					<th>Manajer</th>
					<th>Supervisor</th>
				</tr>
			</thead>
			<tfoot class="table-light">
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th class="text-end"></th>
					<th class="text-end"></th>
					<th class="text-end"></th>
					<th class="text-end"></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					
				</tr>
			</tfoot>
		</table>
	</div>
    </div>
</div>

<!-- Tombol & Pivot Container -->
<div class="card mt-4 shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
    <h6 class="mb-0">Pivot Table</h6>
    <div>
      <button class="btn btn-sm btn-primary me-2" id="buatPivot"  style="font-size:0.8rem; line-height:1.2;">Tampilkan Pivot</button>
      <button class="btn btn-sm btn-success" id="exportExcelPivot" style="font-size:0.8rem; line-height:1.2;">Export Pivot ke Excel</button>
    </div>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto;">
      <div id="pivotContainer" style="min-width:600px;">Data belum ada.</div>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {

	$('.select2').select2({
		width: '100%',
		placeholder: '-- Choose --',
		allowClear: true
    });

	$('#filter-merk').select2({
		placeholder: '-- Choose --',
		allowClear: true,
		ajax: {
			url: 'api/get_brand.php', // ganti dengan path yang benar
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					term: params.term // kirim keyword pencarian ke server
				};
			},
			processResults: function (data) {
				return {
					results: data // hasil sudah dalam format id & text
				};
			},
			cache: true
		}
	});

	$('#filter-group').select2({
		placeholder: '-- Choose --',
		allowClear: true,
		ajax: {
			url: 'api/get_group_customer.php', // ganti dengan path yang benar
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					term: params.term // kirim keyword pencarian ke server
				};
			},
			processResults: function (data) {
				return {
					results: data // hasil sudah dalam format id & text
				};
			},
			cache: true
		}
	});

	$('#filter-group').on('change', function () {
		let selectedId = $(this).val();

		// AJAX ambil data gudang/toko
		$.ajax({
			url: 'api/get_warehouses_by_customer.php',
			type: 'POST',
			dataType: 'json',
			data: {
				KodeLgn: selectedId,
				term: '' // kosongkan jika ingin semua
			},
			success: function (data) {
				// Kosongkan dan isi ulang dropdown
				let $gudang = $('#filter-gudang');
				$gudang.empty();
 
				// Tambahkan placeholder/default option
				$gudang.append('<option value="">-- Choose --</option>');

				// Tambahkan opsi dari hasil AJAX
				$.each(data, function (index, item) {
					$gudang.append(
						$('<option>', {
							value: item.id,
							text: item.text
						})
					);
				});

				// Trigger update Select2
				$gudang.trigger('change');
			},
			error: function (xhr, status, error) {
				console.error('Error loading gudang:', error);
			}
		});
	});

	$('#filter-merk').on('change', function () {   
		$('#filter-style').empty().append('<option value="">-- Choose --</option>').trigger('change');   
		let selectedBrand = $(this).val();
		$.ajax({
			url: 'api/get_range_by_brand.php',
			type: 'GET',
			dataType: 'json',
			data: { brand: selectedBrand },
			success: function (data) {
				let $range = $('#filter-range');
				$range.empty();
				$range.append('<option value="">-- Choose --</option>');

				$.each(data, function (index, item) {
					$range.append($('<option>', {
						value: item.kode,   // bisa diganti item.range jika ingin
						text: item.range
					}));
				});

				$range.trigger('change');
			},
			error: function () {
				alert('Gagal mengambil data range');
			}
		});

		$.ajax({
			url: 'api/get_bahan_by_brand.php',
			type: 'POST',
			dataType: 'json',
			data: { brand: selectedBrand },
			success: function (data) {
				let $material = $('#filter-material');
				$material.empty(); // kosongkan opsi sebelumnya

				data.forEach(function (item) {
					let option = new Option(item.text, item.id, false, false);
					$material.append(option);
				});

				$material.trigger('change');
			},
			error: function (xhr, status, error) {
				console.error('Gagal load material: ', error);
			}
		});

		$.ajax({
			url: 'api/get_warna_by_brand.php',
			type: 'POST',
			dataType: 'json',
			data: { brand: selectedBrand },
			success: function (data) {
				let $material = $('#filter-color');
				$material.empty(); // kosongkan opsi sebelumnya

				data.forEach(function (item) {
					let option = new Option(item.text, item.id, false, false);
					$material.append(option);
				});

				$material.trigger('change');
			},
			error: function (xhr, status, error) {
				console.error('Gagal load material: ', error);
			}
		});
	});

	$('#filter-range').on('change', function () {
            let selectedRanges = $(this).val(); // array of range kode (ex: ['5','6'])
            let selectedBrand = $('#filter-merk').val(); // ambil kode brand, contoh: 'A'

            if (!selectedRanges || !selectedBrand) return;

            // Kirim Ajax ke PHP
            $.ajax({
                url: 'api/get_styles_by_range_brand.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    brand: selectedBrand,
                    kode_ranges: selectedRanges
                },
                success: function (data) {
                    let $style = $('#filter-style');
                    $style.empty(); // kosongkan dulu
                    data.forEach(function (item) {
                        let option = new Option(item.text, item.id, false, false);
                        $style.append(option);
                    });
                    $style.trigger('change');
                },
                error: function (xhr, status, error) {
                    alert("Gagal load style: " + error);
                }
            });
        });

        $('#filter-style').on('change', function () {
            let selectedStyle = $(this).val(); // array of range kode (ex: ['5','6'])
            let selectedBrand = $('#filter-merk').val(); // ambil kode brand, contoh: 'A'

            if (!selectedStyle || !selectedBrand) return;

            // Kirim Ajax ke PHP
            $.ajax({
                url: 'api/get_size_by_style_brand.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    brand: selectedBrand,
                    kode_style: selectedStyle
                },
                success: function (data) {
                    let $style = $('#filter-size');
                    $style.empty(); // kosongkan dulu
                    data.forEach(function (item) {
                        let option = new Option(item.text, item.id, false, false);
                        $style.append(option);
                    });
                    $style.trigger('change');
                },
                error: function (xhr, status, error) {
                    alert("Gagal load style: " + error);
                }
            });
        });

		let table = $('#invoiceTable').DataTable({ 
			scrollX: true,
			processing: true,
			ajax: {
				url: 'api/sales-invoice.php',
				type: 'POST',
				data: function (d) {
					d.merk        = $('#filter-merk').val();
					d.range       = $('#filter-range').val();   // multiple
					d.style       = $('#filter-style').val();   // multiple
					d.bahan       = $('#filter-material').val();// multiple
					d.warna       = $('#filter-color').val();   // multiple
					d.size        = $('#filter-size').val();    // multiple
					d.group 	  = $('#filter-group').val();
					d.kode_gudang = $('#filter-gudang').val();
					d.partnumber  = $('#filter-partnumber').val();
					d.start_date  = $('#start_date').val();
					d.end_date    = $('#end_date').val();
				},
				dataSrc: function (json) {
					return json.data || [];
				}
			},
			columns: [
				{
					data: 'NoBukti',
					render: function(data, type, row, meta) {
						return '<a href="http://myemba.co:84/#/sales-invoice/direct/list" ' +
							'class="nobukti-link" ' +
							'data-nobukti="' + data + '" ' +
							'title="Klik Disini!!! Silakan lakukan transaksi di aplikasi MASERP dan langsung Paste NoBukti tanpa harus dicopy terlebih dahulu." ' +
							'target="_blank">' + data + '</a>';
					}
				},


				{ data: 'TglFaktur' },
				{ data: 'Bulan' },
				{ data: 'Gudang' },
				{ data: 'NamaCustomer' },
				{ data: 'KodeItem' },
				{ data: 'NamaBarang' },
				{ data: 'QtyFormatted', className: 'text-end'},
				{ data: 'HargaJual', className: 'text-end' },
				{ data: 'Diskon', className: 'text-end' },
				{ data: 'HargaNet', className: 'text-end' },
				{ data: 'PartNumber' },
				{ data: 'Range' },
				{ data: 'Style' },
				{ data: 'Bahan' },
				{ data: 'Model' },
				{ data: 'Warna' },
				{ data: 'Size' },
				{ data: 'wilayah' },
				{ data: 'area' },
				{ data: 'kota' },
				{ data: 'direktur_operasional' },
				{ data: 'regional_manager' },
				{ data: 'manager' },
				{ data: 'supervisor' }
			],
			initComplete: function () {
                // Tambahkan select kosong di area length
                let $length = $('#invoiceTable_length');
                $length.append('&nbsp;&nbsp;&nbsp;&nbsp;<select class="form-control cilik" id="filter-partnumber"></select>');

                let $select = $('#filter-partnumber');

                // tambahkan placeholder manual
                $select.append('<option value="">-- Pilih PartNumber --</option>');


                // Tambahkan event saat dropdown berubah
                $('#filter-partnumber').on('change', function () {
                    table.draw(); // redraw table, tidak reload ajax
                });
            },
			footerCallback: function (row, data, start, end, display) {
					let api = this.api();

					function parseNum(val) {
						return typeof val === 'string'
							? parseFloat(val.replace(/\./g, '').replace(/,/g, '.')) || 0
							: typeof val === 'number'
							? val
							: 0;
					}
					// Untuk kolom Harga Jual, Diskon, Harga Net, Part Number
					[7,  8, 9, 10].forEach(function (colIdx) {
						let total = api.column(colIdx, { search: 'applied' }).data().reduce((a, b) => parseNum(a) + parseNum(b), 0);
						let formatted = new Intl.NumberFormat('id-ID').format(total);
						$(api.column(colIdx).footer()).html(formatted);
					});
				}
		});

		// JS untuk auto copy NoBukti
		$(document).on('click', '.nobukti-link', function() {
			let noBukti = $(this).data('nobukti');
			let tempInput = document.createElement('input');
			tempInput.value = noBukti;
			document.body.appendChild(tempInput);
			tempInput.select();
			document.execCommand('copy');
			document.body.removeChild(tempInput);

			alert('NoBukti ' + noBukti + ' berhasil dicopy!');
		});

		

        // setiap kali tabel selesai render, isi ulang select
        table.on('draw.dt', function () {
            let data = table.rows().data().toArray();
            let partNumbers = [...new Set(data.map(r => r.PartNumber))];

            // urutkan ASC
            partNumbers.sort((a, b) => {
                if (a < b) return -1;
                if (a > b) return 1;
                return 0;
            });

            let $select = $('#filter-partnumber');
            let currentVal = $select.val(); // simpan pilihan user

            $select.empty().append('<option value="">-- Semua PartNumber --</option>');
            partNumbers.forEach(pn => {
                if (pn) {
                    $select.append('<option value="' + pn + '">' + pn + '</option>');
                }
            });

            // kembalikan pilihan user kalau masih ada di opsi
            if (currentVal && partNumbers.includes(currentVal)) {
                $select.val(currentVal);
            }
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            let selected = $('#filter-partnumber').val(); // array dari select2
            if (!selected || selected.length === 0) {
                return true; // tidak ada filter → tampil semua
            }

            // Ambil PartNumber dari kolom ke-2 (index 2)
            let partNumber = data[10]; 

            // Jika partnumber row ada di pilihan, tampilkan
            return selected.includes(partNumber);
        });

		// 🔹 Tombol Retrieve untuk reload tabel
		$('#btn-retrieve').on('click', function () {
			//alert($('#filter-group').val());
			table.ajax.reload();
		});

		$('#buatPivot').on('click', function () {
            const data = dataTableToObjects($('#invoiceTable').DataTable());

            if (data.length === 0) {
                alert('Tidak ada data untuk ditampilkan.');
                return;
        }

		

        var numberFormat = $.pivotUtilities.numberFormat;
		var intFormat = numberFormat({
			digitsAfterDecimal: 3,   // 3 angka dibelakang koma
			thousandsSep: ".",       // pemisah ribuan
			decimalSep: ","          // pemisah desimal
		});

		$('#pivotContainer').pivotUI(data, {
			rows: ["wilayah", "area", "direktur_operasional","regional_manager", "manager", "supervisor", "Gudang"],
			cols: [],
			vals: ["HargaNet"],
			aggregatorName: "Sum",
			rendererName: "Table",
			aggregators: {
				"Sum": function() {
					return $.pivotUtilities.aggregatorTemplates.sum(intFormat)(["HargaNet"]);
				}
			}
		});


        });

        // Fungsi konversi datatable ke array of objects
        function dataTableToObjects(dataTable) {
            return dataTable.rows({ search: 'applied' }).data().toArray();
        }

		$('#exportExcelPivot').on('click', function () {
            const table = document.querySelector('.pvtTable');
            if (!table) {
                alert('Tabel Pivot belum tersedia!');
                return;
            }

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            XLSX.utils.book_append_sheet(wb, ws, 'PivotData');
            XLSX.writeFile(wb, 'pivot_invoice.xlsx');
        });
		

		$('#DTexportExcel').on('click', function () {
			// Ambil data dari DataTables
			let data = table.rows({ search: 'applied' }).data().toArray();

			if (data.length === 0) {
				alert("Tidak ada data untuk diexport.");
				return;
			}

			// Ambil header dari definisi kolom DataTables
			let headers = table.settings().init().columns.map(col => col.data);

			// Buat array 2D [ [header...], [row...] ]
			let exportData = [headers];

			data.forEach(row => {
				exportData.push(headers.map(h => row[h] ?? ""));
			});

			// Buat worksheet dan workbook
			let ws = XLSX.utils.aoa_to_sheet(exportData);
			let wb = XLSX.utils.book_new();
			XLSX.utils.book_append_sheet(wb, ws, "Invoice");

			// Custom nama file → misalnya pakai tanggal hari ini
			let today = new Date().toISOString().slice(0,10); // YYYY-MM-DD
			let filename = "invoice_" + today + ".xlsx";

			// Export ke file
			XLSX.writeFile(wb, filename);
		});

		// event delegation karena DataTables bisa reload



});
</script>
