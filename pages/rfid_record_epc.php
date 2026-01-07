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

td.dt-control {
    text-align: center;
    cursor: pointer;
}

td.dt-control::before {
    font-size: 14px;        /* perbesar panah */
    color: #0d6efd;         /* pakai biru bootstrap */
    margin-right: 0;        /* hilangkan jarak */
}

.expander {
    cursor: pointer;       /* jadi icon tangan */
    font-size: 14px;       /* biar lebih rapi */
    display: inline-block; /* biar konsisten */
    color: #0d6efd;        /* biru bootstrap */
}

</style>

<div class="card mb-2 shadow-sm">
  <div class="card-body">
    <div>
      <small class="text-muted">Informasi data dibawah ini sumber dari <strong>RFID Handheld</strong>.</small>
      <br /><br />
    </div>
    <div class="table-responsive">
      <table id="tspdo1Table" class="table table-striped table-hover table-bordered table-sm nowrap" style="width:100%">
          <thead class="table-light">
              <tr>
                  <th>Brand</th>
                  <th>KODE GUDANG</th>
                  <th>SKU</th>
                  <th>Stok Komputer</th> 
                  <th>Stok Sekarang</th>
                  <th>Selisih</th>
              </tr>
          </thead>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#tspdo1Table').DataTable({
        processing: true,
        ajax: {
            url: "api/get_epc_list.php",
            dataSrc: "data"
        },
        columns: [
            { data: "brand" },
            { data: "kd_gudang" },
            { data: "sku" },
            { data: "stok_komputer" },
            { data: "stok_sekarang" },
            { 
                data: null,
                render: function (data) {
                    let selisih = data.stok_komputer - data.stok_sekarang;
                    return selisih;
                }
            }
        ],
        responsive: true,
        scrollX: true,
        order: [[2, "asc"]]
    });
});
</script>

