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

#pimTable th, #pimTable td {
  white-space: nowrap; /* supaya header ikutin isi */
}

  /* Ukuran kecil untuk select dan text */
  .small-select {
    font-size: 12px !important;
    padding: 2px 6px !important;
    height: 30px !important;
    line-height: 1 !important;
    width: 150px !important;
  }

  label.form-label {
    font-size: 12px !important;
  }

  /* Optional: agar tombol di sampingnya pas */
  #btnRetrieve {
    font-size: 12px !important;
    padding: 4px 10px !important;
    height: 30px !important;
    line-height: 1 !important;
  }
</style>
</head>

<form id="uploadForm" enctype="multipart/form-data">
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <div>
        <small class="text-muted">
          <strong>Informasi !!!</strong> Pastikan data yang akan diupload <strong>benar</strong> sesuai dengan pilihan brand di bawah ini.
        </small>
        <hr>
      </div>

      <!-- Pilihan Brand -->
      <div class="mb-3">
        <label class="form-label fw-bold">Pilih Brand:</label>
        <div class="d-flex flex-wrap gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="brand" id="brand1" value="EMBA DENIM" required>
            <label class="form-check-label" for="brand1">EMBA DENIM</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="brand" id="brand2" value="EMBA CASUAL">
            <label class="form-check-label" for="brand2">EMBA CASUAL</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="brand" id="brand3" value="EMBA LADIES">
            <label class="form-check-label" for="brand3">EMBA LADIES</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="brand" id="brand4" value="USED JEANS">
            <label class="form-check-label" for="brand4">USED JEANS</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="brand" id="brand5" value="MORPHIDAE">
            <label class="form-check-label" for="brand5">MORPHIDAE</label>
          </div>
        </div>
      </div>

      <!-- Input File Excel -->
      <div class="mb-3 d-flex align-items-center gap-2">
        <label for="excelFile" class="form-label fw-bold mb-0" style="white-space: nowrap; font-size: 13px;">
          Upload File Excel:
        </label>
        
        <input class="form-control form-control-sm" type="file" id="excelFile" name="excelFile" 
              accept=".xls,.xlsx" style="max-width: 300px;" required>
        
        <button type="submit" class="btn btn-primary btn-sm" id="btnUpload">
          <i class="bi bi-upload me-1"></i> Upload
        </button>
      </div>

      <!-- Feedback -->
      <div id="uploadStatus" class="mt-3"></div>
    </div>
  </div>
</form>


<!-- === FILTER DAN TABEL DATA === -->
<div class="card mb-2 shadow-sm">
  <div class="card-body">

    <!-- Pilihan Brand untuk Retrieve -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <label for="filterBrand" class="fw-bold me-2">Filter Brand:</label>
        <select id="filterBrand" class="form-select d-inline-block w-auto small-select">
          <option value="">-- Pilih Brand --</option>
          <option value="EMBA DENIM">EMBA DENIM</option>
          <option value="EMBA CASUAL">EMBA CLASSIC</option>
          <option value="EMBA LADIES">EMBA LADIES</option>
          <option value="MORPHIDAE">MORPHIDAE</option>
          <option value="USED JEANS">USED JEANS</option>
        </select>

        <button id="btnRetrieve" class="btn btn-primary">
          <i class="bi bi-arrow-repeat me-1"></i> Retrieve
        </button>
      </div>

     
    </div>

    <!-- Tabel Data -->
    <table id="skuTable" class="display table table-bordered table-hover table-striped nowrap" style="width:100%">
      <thead>
        <tr>
          <th>Barcode</th>
          <th>SKU / Artikel</th>
          <th>Jenis Barang</th>
          <th>Nama Fitting</th>
          <th>Warna</th>
          <th>Komposisi Bahan</th>
          <th>Nama Style</th>
          <th>Size</th>
          <th>Panjang (cm)</th>
          <th>Lebar (cm)</th>
          <th>Qty</th>
          <th>CMT / FOB</th>
          <th>Harga</th>
        </tr>
      </thead>
    </table>
  </div>
</div>


<script>
$(document).ready(function() {

    // Inisialisasi DataTable tanpa load otomatis
    var table = $('#skuTable').DataTable({
    ajax: {
        url: 'api/get_data_jubelio.php',
        data: function(d) {
            d.brand = $('#filterBrand').val(); // kirim filter brand ke PHP
        },
        dataSrc: 'data' // ✅ sesuaikan dengan struktur JSON PHP
    },
    columns: [
        { data: 'barcode' },
        { data: 'artikel' },
        { data: 'keterangan' },
        { data: 'nm_style' },
        { data: 'nm_warna' },
        { data: 'komposisi' },
        { data: 'nm_model' },
        { data: 'size' },
        {
            data: 'p_pakaian_cm',
            render: function (data, type, row) {
                return data ? `Panjang Badan: ${data} cm` : '-';
            }
        },
        {
            data: 'l_pakaian_cm',
            render: function (data, type, row) {
                return data ? `Lebar Dada: ${data} cm` : '-';
            }
        },
        { data: 'qty' },
        { data: 'cmt_or_fob' },
        { data: 'harga' }
    ],
    responsive: true,
    pageLength: 10,
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'excelHtml5',
            title: 'Data SKU',
            text: '📊 Export Excel',
            className: 'btn btn-success'
        },
        {
            extend: 'csvHtml5',
            title: 'Data SKU',
            text: '📄 Export CSV'
        },
        {
            extend: 'print',
            title: 'Data SKU',
            text: '🖨️ Print'
        }
    ],
    language: {
        search: "🔍 Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
        paginate: { previous: "←", next: "→" },
        zeroRecords: "Tidak ada data ditemukan"
    },
    processing: true,
    serverSide: false,
    searching: true,
    ordering: true,
    paging: true,
    info: true,
    deferLoading: 0 // ⛔ jangan load sebelum klik Retrieve
});


    // Tombol Retrieve → reload tabel dengan brand terpilih
    $('#btnRetrieve').click(function() {
        let selectedBrand = $('#filterBrand').val();
        if (selectedBrand === '') {
            alert('⚠️ Silakan pilih brand terlebih dahulu!');
            return;
        }

       
        table.ajax.reload();
    });
});


$('#uploadForm').on('submit', function(e) {
  e.preventDefault();

  let formData = new FormData(this);
  $('#uploadStatus').html('<div class="text-info">⏳ Mengupload dan memproses data...</div>');

  $.ajax({
    url: 'api/upload_jubelio.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      $('#uploadStatus').html('<div class="text-success">' + response + '</div>');
    },
    error: function(xhr, status, error) {
      $('#uploadStatus').html('<div class="text-danger">❌ Terjadi kesalahan: ' + error + '</div>');
    }
  });
});
</script>

