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
</style>
<div class="card mb-4 shadow-sm">
    <div class="card-header" style="background-color:#e0eeff;">
        <h6 class="mb-0">Filter Data</h6>
    </div>
    <div class="card-body"> 
        <form id="filterForm" class="row g-3">
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

			<div class="col-md-2">
				<div class="form-group mb-2">
					<label for="filter-gudang">Gudang / Toko</label>
					<select id="filter-gudang" class="form-control select2 filter-input">
						<option value="">-- Choose --</option>
					</select>
				</div>
			</div>
            <div class="col-md-2">
                	<label class="">Tanggal Mulai</label>
               		<input type="date" name="start_date" id="start_date" class="form-control cilik filter-input" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-md-2">
                <label class="">Tanggal Selesai</label>
                <input type="date" name="end_date" id="end_date" class="form-control cilik mt-6" value="<?= date('Y-m-t') ?>">
            </div>
           
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100 mb-2">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Data Sales Invoice</h6>
    </div>
    <div class="card-body">

	<!-- Tabel -->
	<div class="table-responsive">
		<table id="invoiceTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%">
			<thead class="table-primary">
				<tr>
					<th>Gudang</th>
					<th>Group</th>
					<th class="text-end">Total Qty</th>
					<th class="text-end">Diskon Total</th>
					<th class="text-end">Harga Total</th>
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
					<th class="text-end">Total:</th>
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
				</tr>
			</tfoot>
		</table>
	</div>
		</div>
	</div>


<script>
var table;

function loadData(startDate, endDate, brand, group, kodegudang) {
    if (table) table.destroy();

    table = $('#invoiceTable').DataTable({
        scrollX: true,
        dom: '<"row align-items-center mb-3"<"col-md-6 d-flex"lB><"col-md-6"f>>tip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            // Kolom angka: Qty, Diskon, Harga Net
                            if ([2, 3, 4].includes(column)) {
                                return data.replace(/\./g, '').replace(/,/g, '.');
                            }
                            return data;
                        }
                    }
                },
                customize: function (xlsx) {
                    let sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row c[r]', sheet).each(function () {
                        let attr = $(this).attr('r');
                        if (attr) {
                            if (attr.match(/^C/) || attr.match(/^D/) || attr.match(/^E/)) {
                                $(this).attr('s', '49'); // Style teks di Excel
                            }
                        }
                    });
                }
            }
        ],
        ajax: {
            url: 'api/resume-sales.php',
            type: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                brand: brand,
                group: group,
                kodegudang: kodegudang
            },
            dataSrc: 'data'
        },
        columns: [
            { data: "Gudang" },          // tampil: C-C0020 - NAMA
            { data: "NamaCustomer" },    // Nama customer
            { data: "TotalQty" },        // Total qty
            { data: "TotalDiskon" },     // Total diskon
            { data: "HargaNet" },        // Harga net
            { data: "wilayah" },
            { data: "area" },
            { data: "kota" },
            { data: "direktur_operasional" },
            { data: "regional_manager" },
            { data: "manager" },
            { data: "supervisor" }
        ],
        columnDefs: [
            { targets: [2, 3, 4], className: 'text-end' }
        ],
        language: {
            url: "assets/id.json"
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

            [2, 3, 4].forEach(function (colIdx) {
                let total = api.column(colIdx, { search: 'applied' }).data()
                    .reduce((a, b) => parseNum(a) + parseNum(b), 0);
                let formatted = new Intl.NumberFormat('id-ID').format(total);
                $(api.column(colIdx).footer()).html(formatted);
            });
        }
    });
}


$('#filterForm').on('submit', function (e) {
    e.preventDefault();
    const start = $('#start_date').val();
    const end = $('#end_date').val();
	const brand = $('#filter-merk').val();
	const group = $('#filter-group').val();
	const kodegudang = $('#filter-gudang').val();

	//alert($('#filter-gudang').val());

	if (!brand) {
        alert('Merk harus dipilih!');
        return 0;
    }
	
    loadData(start, end, brand, group, kodegudang);
});

// Manual search pakai input
$('#cari_dalam_table').on('keyup', function () {
	table.search(this.value).draw();
});

// Tombol Export Excel
$('#exportExcel').on('click', function () {
	table.button('.buttons-excel').trigger();
});

//menampilkan gudang
$("#customer").select2({
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
		

	// load pertama kali
	//$(document).ready(function () {
    //loadData($('#start_date').val(), $('#end_date').val(), $('#customer').val(), $('#brand').val());

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

});
</script>