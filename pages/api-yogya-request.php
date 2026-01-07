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



</style>
</head>

<div class="card mb-2 shadow-sm">
    <div class="card-body">
      <div>
        <small class="text-muted">Informasi endpoint API yang tersedia untuk <strong>staging</strong> dan <strong>production</strong>. Gunakan <code>apikey</code> sesuai environment.</small>
        <br /><br />
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Status</th>
              <th>Endpoint</th>
              <th>API Key</th>
              <th>Source</th>
              <th>Origin</th>
            </tr>
          </thead>
          <tbody id="apiTable">
            <tr>
              <td colspan="6" class="text-center text-muted">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
</div>
<div class="card mb-2 shadow-sm">
    
    <div class="card-body">
    <small class="text-muted">Data yang ditampilkan sesuai dengan data yang ada di <strong>CIMS Yogya Dept. Store</strong></small><br /><br />
    <button id="syncBtn" class="btn btn-sm btn-primary">
            <i class="bi bi-arrow-repeat"></i> Sync From CIMS
          </button><br /><br />
      <!-- Nav Tabs -->
      <ul class="nav nav-tabs" id="apiTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="brand-tab" data-bs-toggle="tab" data-bs-target="#brand" type="button" role="tab">
            Brand
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="store-tab" data-bs-toggle="tab" data-bs-target="#store" type="button" role="tab">
            Store
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pim-tab" data-bs-toggle="tab" data-bs-target="#pim" type="button" role="tab">
            PIM (Product Information Management)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="pim-stock-tab" data-bs-toggle="tab" data-bs-target="#pim-stock" type="button" role="tab">
            PIM Stock
          </button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content pt-3">

        <!-- Brand Tab -->
        <div class="tab-pane fade show active" id="brand" role="tabpanel">

          <table id="brandTable" class="table table-striped table-bordered table-sm" style="width:100%">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Nama Brand</th>
                <th>Kode</th>
                <th>Update Time</th>
              </tr>
            </thead>
          </table>
        </div>

        <!-- Store Tab -->
        <div class="tab-pane fade" id="store" role="tabpanel">
          <table id="storeTable" class="table table-striped table-bordered table-sm" style="width:100%">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Initial</th>
                <th>Alamat</th>
                <th>Update Time</th>
              </tr>
            </thead>
           
          </table>
        </div>

        <!-- PIM Tab -->
        <div class="tab-pane fade" id="pim" role="tabpanel">
            <table id="pimTable" class="table table-striped table-bordered table-sm nowrap teble-responsive" style="width:100%">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Supplier Code</th>
                  <th>Brand Code</th>
                  <th>Brand Desc</th>
                  <th>Style Code</th>
                  <th>Supplier Barcode</th>
                  <th>Art Desc</th> 
                  <th>Price</th>
                  <th>Color</th>
                  <th>Size</th>
                  <th>Model</th>
                  <th>Group</th>
                  <th>Deleted By</th>
                  <th>Deleted Date</th>
                  <th>Updated Date</th> 
                </tr>
              </thead>
            </table>
        </div> 

        <!-- PIM Stock Tab -->
        <div class="tab-pane fade" id="pim-stock" role="tabpanel">
        <table id="pim-stockTable" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Supp Code</th>
              <th>Initial Store</th>
              <th>Name</th>
              <th>Supplier Barcode</th>
              <th>Stock</th>
              <th>Update Time</th>
            </tr>
          </thead>
        </table>
        </div>


    </div>
  </div>


<script>
$(document).ready(function () {
  
  $.ajax({
    url: "api/get-yogya-api-service.php",
    type: "GET",
    dataType: "json",
    success: function (data) {
      let rows = "";
      if (data.length > 0) {
        $.each(data, function (i, item) {
          let badge = (item.status.toLowerCase() === "production")
            ? "<span class='badge bg-success'>Production</span>"
            : "<span class='badge bg-warning text-dark'>Staging</span>";

          let apiKeyShort = item.apikey.length > 16 ? item.apikey.substring(0, 16) + "..." : item.apikey;

          rows += "<tr>" +
            "<td>" + item.id + "</td>" +
            "<td>" + badge + "</td>" +
            "<td><a href='" + item.endpoint + "' target='_blank'>" + item.endpoint + "</a></td>" +
            "<td><code>" + apiKeyShort + "</code></td>" +
            "<td>" + item.source + "</td>" +
            "<td><code>" + item.origin + "</code></td>" +
          "</tr>";
        });
      } else {
        rows = "<tr><td colspan='6' class='text-center text-danger'>Tidak ada data</td></tr>";
      }
      $("#apiTable").html(rows);
    },
    error: function (xhr, status, error) {
      $("#apiTable").html("<tr><td colspan='6' class='text-center text-danger'>Error: " + error + "</td></tr>");
      }
    });

    // Inisialisasi DataTables
    //$("#brandTable").DataTable();
    //$("#storeTable").DataTable();
    //$("#pimTable").DataTable();

    // Fix DataTables ketika tab diganti
    $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function () {
      $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });

    let brandTable = $('#brandTable').DataTable({
      "ajax": {
        "url": "api/get-yogya-brand.php", // PHP API
        "dataSrc": "data"
      },
      "columns": [
        { "data": "id" },
        { "data": "brand_name" },
        { "data": "brand_code" },
        { "data": "update_time" }
      ],
      "pageLength": 5,
      "lengthMenu": [5, 10, 25, 50],
      "language": {
        "emptyTable": "Belum ada data brand di database"
      }
    });

    let storeTable = $('#storeTable').DataTable({
      "ajax": {
        "url": "api/get-yogya-store.php", // PHP API
        "dataSrc": "data"
      },
      "columns": [
        { "data": "id" },
        { "data": "name" },
        { "data": "initial" },
        { "data": "address" },
        { "data": "update_time" }
      ],
      "pageLength": 5,
      "lengthMenu": [5, 10, 25, 50],
      "language": {
        "emptyTable": "Belum ada data store di database"
      }
    });

    let pimTable = $('#pimTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "api/get-yogya-pim.php",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "supp_code" },
            { "data": "brand_code" },
            { "data": "brand_desc" },
            { "data": "style_code" },
            { "data": "supplier_barcode" },
            { "data": "art_desc" },
            { "data": "price" },
            { "data": "color" },
            { "data": "size" },
            { "data": "model" },
            { "data": "group" },
            { "data": "deleted_by" },
            { "data": "deleted_date" },
            { "data": "updated_date" }
        ],
        "scrollX": true,
        "autoWidth": true,   // biar DataTables hitung otomatis
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "language": {
            "processing": "Loading...",
            "emptyTable": "Belum ada data PIM di database"
        },
        initComplete: function () {
            this.api().columns.adjust(); // langsung adjust saat init
        }         
    });

    let pimStockTable = $('#pim-stockTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "api/get-yogya-pim-stock.php",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "supp_code" },
            { "data": "initial_store" },
            { "data": "name" },
            { "data": "supplier_barcode" },
            { "data": "stock" },
            { "data": "update_time" }
        ],
        "scrollX": true,
        "autoWidth": false,
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100],
        "language": {
            "processing": "Loading...",
            "emptyTable": "Belum ada data stock di database"
        },
        initComplete: function () {
            this.api().columns.adjust();
        }
    });


      // Klik tombol Sync
    $('#syncBtn').on('click', function() {
      // cari tab aktif
      let activeTab = $('.tab-pane.active').attr('id');
      let apiUrl = "";

      if (activeTab === "brand") apiUrl = "api/yogya-api-service/yogya-api-brand.php";
      else if (activeTab === "store") apiUrl = "api/yogya-api-service/yogya-api-store.php";
      else if (activeTab === "pim") apiUrl = "api/yogya-api-service/yogya-api-pim.php";
      else if (activeTab === "pim-stock") apiUrl = "api/yogya-api-service/yogya-api-pim-stock.php";

      Swal.fire({
        title: "Sinkronisasi Data",
        text: "Apakah Anda yakin ingin sync data " + activeTab + "?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, Sync",
        cancelButtonText: "Batal"
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: apiUrl,
            method: "GET",
            beforeSend: function() {
              Swal.fire({
                title: "Sedang Sinkronisasi...",
                text: "Mohon tunggu sebentar",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
              });
            },
            success: function(res) {
              Swal.fire("Berhasil!", "Data " + activeTab + " berhasil disinkronisasi.", "success");
              if (activeTab === "brand") brandTable.ajax.reload();
              if (activeTab === "store") storeTable.ajax.reload();
              if (activeTab === "pim") pimTable.ajax.reload();
              if (activeTab === "pim-stock") pimStockTable.ajax.reload();
              // nanti tambah reload untuk storeTable & pimTable juga
            },
            error: function(err) {
              Swal.fire("Error!", "Gagal sync data " + activeTab, "error");
            }
          });
        }
      });
    });
});
</script>

