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

.card-bg-kuning {
    background-color: #fff3cd !important;
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
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-merk">Merk</label>
                            <select id="filter-merk" class="form-control select2 filter-input">
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-group">Group</label>
                            <select id="filter-group" class="form-control select2 filter-input">
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label for="filter-gudang">Gudang / Toko</label>
                            <select id="filter-gudang" class="form-control select2 filter-input">
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-range">Range</label>
                            <select id="filter-range" class="form-control select2 filter-input" multiple>
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-style">Fitting</label> 
                            <select id="filter-style" class="form-control select2 filter-input" multiple>
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-material">Bahan</label>
                            <select id="filter-material" class="form-control select2 filter-input" multiple>
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-color">Warna</label>
                            <select id="filter-color" class="form-control select2 filter-input" multiple>
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-2">
                            <label for="filter-size">Ukuran</label>
                            <select id="filter-size" class="form-control select2 filter-input" multiple>
                                <option value="">-- Choose --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 pt-6">
                        <div class="form-group w-100">
                        <label for="f">&nbsp;</label>
                            <button id="btn-retrieve" type="button" class="btn btn-primary btn-sm py-1 w-100" style="font-size: 0.85rem;">
                                Retrieve
                            </button>
                        </div>
                    </div>


                  
                </div>
            </div>
            </div>
            </div>
            </div>
</div>

<div class="card mb-2 shadow-sm">

    <div id="collapseStockCurrent" class="collapse show">
    <div class="card-header d-flex align-items-center" style="background-color:#e0eeff;">
    <h6 class="mb-0">Stock Current Tables</h6>

    <div class="ms-auto">
        <button class="btn btn-sm btn-success me-2" id="DTexportExcel" style="font-size:0.8rem; line-height:1.2;">
        Datatable Export Excel
        </button>
        <button class="btn btn-sm btn-success" 
                id="DTexportExcelPerBrand" 
                style="font-size:0.8rem; line-height:1.2;"
                data-bs-toggle="modal" 
                data-bs-target="#modalBrandDownload">
            Download Stock Current Per Brand
        </button>
    </div>
    </div>

        <div class="card-body">
            <!-- Tabel -->
            <div class="table-responsive">
				<!-- Filter Select2 per Kolom -->	
                <table id="stock-table" class="table table-bordered table-hover table-striped nowrap" style="width:100%">
                    <thead class="table-primary">
						<tr>
                        <th>Kode_Item</th>
                        <th>Nama_Barang</th>
                        <th>Part_Number</th>
                        <th>Brand</th>
                        <th>Gudang</th>
                        <th>Group</th>
                        <th>Saldo_Awal</th>
                        <th>Terima</th>
                        <th>Keluar</th>
                        <th>SaldoAkhir</th>
                        <th>QtyMax</th>
                        <th>Range</th>
                        <th>Fitting</th>
                        <th>Bahan</th>
                        <th>Model</th>
                        <th>Warna</th>
                        <th>Ukuran</th>
						<th>Berat</th>
                        <th>HargaJual</th>
                        <th>Wilayah</th>
                        <th>Area</th>
                        <th>Kota</th>
                        <th>Dir. Operasional</th>
                        <th>Reg. Manajer</th>
                        <th>Manajer</th>
                        <th>Supervisor</th>
                    </tr>
					</thead>                    
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bg-dark" id="modalBrandDownload" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Pilih Brand (Stock Current)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <label class="form-label">Brand</label>
        <select id="selectBrand" class="form-select">
            <option value="">-- Pilih Brand --</option>
        </select>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Batal
        </button>

        <button type="button" class="btn btn-success" id="btnDownloadBrand">
          Download
        </button>
      </div>

    </div>
  </div>
</div>


<!--<div class="card mb-6 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Pivot Stock Current</h6>
		<div>
			<button class="btn btn-sm btn-primary me-2" id="buatPivot">Tampilkan Pivot</button>
			<button class="btn btn-sm btn-success" id="exportExcel">Export to Excel</button>
		</div>
    </div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <div id="pivotContainer" style="min-width: 600px;">Data belum ada.</div>
        </div>
    </div>
</div>-->


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

             
        $.ajax({
            url: 'api/get_brand.php',
            dataType: 'json',
            success: function (data) {
                $('#selectBrand').empty().append('<option value="">-- Pilih Brand --</option>');
                data.forEach(item => {
                    $('#selectBrand').append(`<option value="${item.id}">${item.text}</option>`);
                });
            }
        });

        $('#btnDownloadBrand').on('click', function () {
            let brand = $('#selectBrand').val();

            if (!brand) {
                alert("Pilih brand terlebih dahulu!");
                return;
            }

            // Redirect ke file PHP untuk generate Excel
            window.location.href = 'api/stock_current_download_brand.php?brand=' + encodeURIComponent(brand);
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
            /*$.ajax({
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
            });*/

            /*$.ajax({
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
            });*/

            /*$.ajax({
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
            });*/
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
 
     

        // Init DataTable
        let table = $('#stock-table').DataTable({ 
            scrollX: true,
            processing: true,
            serverSide: true,
            searching: false,   // ✅ matikan search default
            ajax: {
                url: 'api/get_inventory_serverside.php',
                type: 'POST',
                data: function (d) {
                    d.merk        = $('#filter-merk').val();
                    d.range       = $('#filter-range').val();
                    d.style       = $('#filter-style').val();
                    d.bahan       = $('#filter-material').val();
                    d.warna       = $('#filter-color').val();
                    d.size        = $('#filter-size').val();
                    d.group       = $('#filter-group').val();
                    d.kode_gudang = $('#filter-gudang').val();
                    d.partnumber  = $('#filter-partnumber').val();
                },
                dataSrc: function (json) {
                    return json.data || [];
                }
            },
            columns: [
                { data: 'KodeItem' },
                { data: 'NamaBarang' },
                { data: 'PartNumber' },
                { data: 'NamaBrand' },
                { data: 'Gudang' },
                { data: 'NamaCustomer' },
                { data: 'QtySaldoAwal' },
                { data: 'QtyTerima' },
                { data: 'QtyKeluar' },
                { data: 'SaldoAkhir' },
                { data: 'QtyMax' },
                { data: 'Range' },
                { data: 'Style' },
                { data: 'Bahan' },
                { data: 'Model' },
                { data: 'Warna' },
                { data: 'Size' },
                { data: 'Weight' },
                { 
                    data: 'HargaJual',
                    className: 'text-end'
                },
                { data: 'wilayah' },
                { data: 'area' },
                { data: 'kota' },
                { data: 'direktur_operasional' },
                { data: 'regional_manager' },
                { data: 'manager' },
                { data: 'supervisor' }
            ],
            deferLoading: 0, // ✅ awalnya jangan load data
            initComplete: function () {
                // Tambah filter PartNumber di area length
                let $length = $('#stock-table_length');
                $length.append('&nbsp;&nbsp;&nbsp;&nbsp;<select class="form-control cilik" id="filter-partnumber"></select>');

                let $select = $('#filter-partnumber');
                $select.append('<option value="">-- Pilih PartNumber --</option>');

                // Event filter → manual draw
                $('#filter-merk, #filter-range, #filter-style, #filter-material, #filter-color, #filter-size, #filter-group, #filter-gudang, #filter-partnumber')
                    .on('change', function () {
                        table.draw(); // reload hanya saat user pilih filter
                    });

                // Bisa juga kasih tombol “Cari”
                $('#btn-cari').on('click', function () {
                    table.draw();
                });
            }
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
            let partNumber = data[2]; 

            // Jika partnumber row ada di pilihan, tampilkan
            return selected.includes(partNumber);
        });

        // Cegah request kalau semua filter kosong
        $('#stock-table').on('preXhr.dt', function (e, settings, data) {
            let semuaKosong =
                (!data.merk || data.merk.trim() === '') &&
                (!data.range || data.range.trim() === '') &&
                (!data.style || data.style.trim() === '') &&
                (!data.bahan || data.bahan.trim() === '') &&
                (!data.warna || data.warna.trim() === '') &&
                (!data.size || data.size.trim() === '') &&
                (!data.kode_gudang || data.kode_gudang.trim() === '');

            if (semuaKosong) {
                e.preventDefault(); // stop request
                table.clear().draw(); // kosongkan tabel
                console.log("❌ Request dibatalkan karena semua filter kosong");
            }
        });

        // Tombol Retrieve → reload tabel
        $('#btn-retrieve').on('click', function () {
            table.ajax.reload();
        });

        $('#exportExcel').on('click', function () {
            const table = document.querySelector('.pvtTable');
            if (!table) {
                alert('Tabel Pivot belum tersedia!');
                return;
            }

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            XLSX.utils.book_append_sheet(wb, ws, 'PivotData');
            XLSX.writeFile(wb, 'pivot_stock.xlsx');
        });

        $('#buatPivot').on('click', function () {
        const data = dataTableToObjects($('#stock-table').DataTable());

        if (data.length === 0) {
            alert('Tidak ada data untuk ditampilkan.');
            return;
        }

        $('#pivotContainer').pivotUI(data, {
            rows: ["wilayah", "area", "direktur_operasional","regional_manager", "manager", "supervisor", "Gudang"],
            cols: [],
            vals: ['SaldoAkhir', 'QtySaldoAwal', 'QtyTerima', 'QtyKeluar'],
            aggregatorName: 'Sum',
            rendererName: 'Table'
        });
        });

        // Fungsi konversi datatable ke array of objects
        function dataTableToObjects(dataTable) {
            return dataTable.rows({ search: 'applied' }).data().toArray();
        }

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
			let filename = "stockcurrent_" + today + ".xlsx";

			// Export ke file
			XLSX.writeFile(wb, filename);
		});






    });
</script>
