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

    <div id="collapseStockCurrent" class="collapse show">
    <div class="card-header d-flex align-items-center" style="background-color:#e0eeff;">
    <h6 class="mb-0">Resume Stock Current Tables</h6>

   <div class="ms-auto">

        <button class="btn btn-sm btn-success" 
                id="DTexportExcelPerBrand" 
                style="font-size:0.8rem; line-height:1.2;"
                data-bs-toggle="modal" 
                data-bs-target="#modalBrandDownload">
            Download Resume
        </button>
    </div>
    </div>

        <div class="card-body">
			<div class="col-3">

			<label for="filter-merk">Merk</label>
            <select id="brandFilter" class="form-control select2 filter-input">
				<option value="">-- Choose --</option>
			</select>
			</div>

			<br /> 
			<table id="stockTable" class="display table table-bordered table-hover table-striped nowrap" style="width:100%">
				<thead>
					<tr>
						<th>Kode Item</th>
						<th>Stok Gudang (G-0001)</th>
						<th>Qty Sales Order</th>
						<th>Qty Stock Request</th>
						<th>Qty Ready</th>
					</tr>
				</thead>
			</table>

        </div>
    </div>
</div>

<div class="modal fade bg-dark" id="modalBrandDownload" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Choose Brand (Resume)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <label class="form-label">Merk</label>
        <select id="selectBrand" class="form-select">
            <option value="">-- Choose Merk --</option>
        </select>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>

        <button type="button" class="btn btn-success" id="btnDownloadBrand">
          Download
        </button>
      </div>

    </div>
  </div>
</div>

<script>
$(function () {
    let table = $('#stockTable').DataTable({
		processing: true,
		serverSide: true,
		order: [[0, 'asc']], // ✅ KodeItem ASC
		ajax: {
			url: "api/stock-resume.php",
			type: "POST",
			data: function (d) {
				d.brand = $('#brandFilter').val();
			}
		},
		columns: [
			{ data: "KodeItem", orderable: true }, // index 0
			{ data: "StokGudang", className: "text-right", orderable: false },
			{ data: "QtySalesOrder", className: "text-right", orderable: false },
			{ data: "QtyStockRequest", className: "text-right", orderable: false },
			{ data: "QtyReady", className: "text-right", orderable: false }
		]
	});


    $('#brandFilter').change(function () {
        table.ajax.reload();
    });
	
	$('#btnDownloadBrand').on('click', function () {
            let brand = $('#selectBrand').val();

            if (!brand) {
                alert("Pilih brand terlebih dahulu!");
                return;
            }

            // Redirect ke file PHP untuk generate Excel
            window.location.href = 'api/stock-resume-download-brand.php?brand=' + encodeURIComponent(brand);
        });
	
	$(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: '-- Choose --',
            allowClear: true
        });

        $('#brandFilter').select2({
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
	});

});
</script>
