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
      <small class="text-muted">Informasi data dibawah ini sumber dari <strong>PowerOne</strong>.</small>
      <br /><br />
    </div>
    <div class="table-responsive">
      <table id="tspdo1Table" class="table table-striped table-hover table-bordered table-sm nowrap" style="width:100%">
          <thead class="table-light">
              <tr>
                  <th></th> <!-- tombol expand -->
                  <th>Bukti ID</th>
                  <th>Tanggal</th>
                  <th>Customer</th>
                  <th>Customer Gab.</th>
                  <th>Brand</th>
                  <th>Initial Store CIMS</th>
                  <th>Status</th>
              </tr>
          </thead>
      </table>
    </div>
  </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#tspdo1Table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "api/powerone-api-service/db-processing-transfer.php",
            scrollX: true,
            columns: [
                {
                    className: 'details-control text-center',
                    orderable: false,
                    data: null,
                    defaultContent: '<span class="expander">&#9654;</span>', // ▶ default collapse
                    width: "30px"
                },
                { data: "BUKTI_ID" },
                { data: "TGL" },
                { data: "KD_CUST" },
                { data: "CUST_GAB" },
                { data: "BRAND" },
                { data: "INITIAL" },
                { data: "BRAND" }
            ]
        });

        // klik expand
        $('#tspdo1Table tbody').on('click', 'td.details-control .expander', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var icon = $(this);

            if (row.child.isShown()) {
                // tutup
                row.child.hide();
                icon.html('&#9654;'); // ▶ collapse
            } else {
                var bukti_id = row.data().BUKTI_ID;
                var kd_cust = row.data().KD_CUST;
                var initial = row.data().INITIAL;
                row.child('<div class="p-2 text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</div>').show();
                icon.html('&#9660;'); // ▼ expand

                $.ajax({
                    url: "api/powerone-api-service/db-processing-transfer-detail.php",
                    type: "GET",
                    data: { bukti_id: bukti_id, kd_cust: kd_cust },
                    dataType: "json",
                    success: function(response){
                        if (response.data && response.data.length > 0) {
                            var detailHtml = '<table class="table table-sm table-bordered table-hover mb-0">'+
                                '<thead class="table-primary">'+
                                '<tr>'+
                                '<th>BRAND CODE</th>'+
                                '<th>URUT</th>'+
                                '<th>KD_SKU</th>'+
                                '<th>QTY</th>'+
                                '<th>HRG JUAL POWERONE</th>'+
                                '<th>HRG JUAL CIMS</th>'+
                                '<th>KETERANGAN</th>'+
                                '</tr></thead><tbody>';

                                $.each(response.data, function(i, d){
                                    let harga = parseFloat(d.hrg_jual_rupiah).toLocaleString('id-ID'); 
                                    let harga_cims = parseFloat(d.cims_price).toLocaleString('id-ID');

                                    detailHtml += '<tr>'+   
                                        '<td>'+d.BRAND_CODE+'</td>'+
                                        '<td>'+d.URUT+'</td>'+
                                        '<td>'+d.KD_SKU+'</td>'+
                                        '<td align="right">'+d.QTY_REN_DO+'</td>'+
                                        '<td align="right">'+harga+'</td>'+   // ✅ format rupiah langsung di JS
                                        '<td align="right">'+harga_cims+'</td>'+
                                        '<td>'+d.KETERANGAN+'</td>'+
                                        '</tr>';
                                });

                            detailHtml += '</tbody></table>';

                            // ⬇ Tambahkan tombol Post & Sync
                            detailHtml += '<div class="mt-2 ">'+
                                        '<button class="btn btn-sm btn-primary btn-sync-sku" '+
                                        'data-brand="'+response.data[0].BRAND_CODE+'" '+
                                        'data-initial="'+initial+'" '+
                                        'data-skus=\''+JSON.stringify(response.data.map(d => d.KD_SKU))+'\'>'+
                                        '<i class="bi bi-cloud-arrow-up"></i> Sync SKU to CIMS</button>'+
                                        '&nbsp'+
                                        '<button class="btn btn-sm btn-primary btn-post" '+
                                        'data-bukti="'+bukti_id+'" '+
                                        'data-brand="'+response.data[0].BRAND_CODE+'" '+
                                        'data-initial="'+initial+'">'+
                                        '<i class="bi bi-arrow-repeat"></i> Post Transaction '+bukti_id+' to CIMS</button>'+
                                        '</div>';

                            row.child(detailHtml).show();
                        } else {
                            row.child('<div class="p-2 text-muted">Tidak ada data detail</div>').show();
                        }
                    },
                    error: function() {
                        row.child('<div class="p-2 text-danger">Gagal load detail</div>').show();
                    }
                });

            }
        });

// ⬇ Event klik Sync SKU
// ⬇ Event klik Sync SKU
$('#tspdo1Table').on('click', '.btn-sync-sku', function(){
    var brand = $(this).data('brand');
    var initial = $(this).data('initial');
    var skus = $(this).attr('data-skus');
    var skuArr = JSON.parse(skus);

    $.ajax({
        url: "api/powerone-api-service/db-sync-sku.php",
        type: "POST",
        data: { brand: brand, initial: initial, skus: skuArr },
        dataType: "json",
        success: function(res){
            if(res.details && res.details.length > 0){
                let tableHtml = '<table class="table table-sm table-bordered table-hover small" style="font-size:12px">'+
                                '<thead class="table-primary">'+
                                '<tr>'+
                                '<th>Brand</th><th>Style Code</th><th>Supplier Barcode</th>'+
                                '<th>Art Desc</th><th>Price</th><th>Color</th>'+
                                '<th>Size</th><th>Model</th><th>Group</th>'+
                                '</tr></thead><tbody>';

                $.each(res.details, function(i, d){
                    tableHtml += '<tr>'+
                                    '<td>'+d.brand+'</td>'+
                                    '<td>'+d.style_code+'</td>'+
                                    '<td>'+d.supplier_barcode+'</td>'+
                                    '<td>'+d.art_desc+'</td>'+
                                    '<td>'+d.price.toLocaleString("id-ID")+'</td>'+
                                    '<td>'+d.color+'</td>'+
                                    '<td>'+d.size+'</td>'+
                                    '<td>'+d.model+'</td>'+
                                    '<td>'+d.group+'</td>'+
                                 '</tr>';
                });

                tableHtml += '</tbody></table>';

                Swal.fire({
                title: 'Update PIM to CIMS',
                html: tableHtml,
                width: '80%',
                confirmButtonText: 'UPDATE PIM to CIMS',
                preConfirm: () => {
                    return $.ajax({
                        url: "api/yogya-api-service/yogya-api-pim-post.php",  // file PHP baru untuk handle
                        type: "POST",
                        data: { details: res.details }, // kirim array details langsung
                        dataType: "json"
                    }).then(response => {
                        if (!response || response.error) {
                            Swal.showValidationMessage(`Gagal: ${response.message || 'unknown error'}`);
                        }
                        return response;
                    }).catch(() => {
                        Swal.showValidationMessage('Gagal kirim ke API');
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Sukses', 'Data berhasil dikirim ke CIMS', 'success');
                }
            });
            } else {
                Swal.fire('Info','Tidak ada data ditemukan','info');
            }
        },
        error: function(){
            Swal.fire('Error','Gagal sync ke CIMS','error');
        }
    });
});





        // Event tombol Post Transaction
        $('#tspdo1Table tbody').on('click', '.btn-post', function () {
            var bukti_id   = $(this).data('bukti');   // invoice_number
            var initial    = $(this).data('initial'); // initial_store
            var brand_code = $(this).data('brand');

            Swal.fire({
                title: 'Post Transaction?',
                html: '<b>Brand Code:</b> ' + brand_code +
                    '<br><b>Bukti ID:</b> ' + bukti_id +
                    '<br><b>Initial Store CIMS:</b> ' + initial,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Post!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {

                    var details = [];

                    // cari tabel detail di row child dari tombol ini
                    var $table = $(this).closest("div").prev("table");

                    $table.find("tbody tr").each(function() {
                        var row = $(this).find('td');

                        details.push({
                            invoice_number: bukti_id,
                            invoice_date: new Date().toISOString().split('T')[0],
                            supplier_barcode: $(row[2]).text().trim(),   // kolom KD_SKU
                            qty_posting: parseInt($(row[3]).text()) || 0 // kolom QTY_REN_DO
                        });
                    });

                    // kirim ke PHP backend
                    $.ajax({
                        url: 'api/yogya-api-service/db-post-receiving.php',
                        type: 'POST',
                        data: JSON.stringify({
                            initial_store: initial,
                            details: details
                        }),
                        contentType: 'application/json',
                        success: function(res) {
                            try {
                                var response = (typeof res === "string") ? JSON.parse(res) : res;

                                if (response.id === 200) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Posted!',
                                        html: response.message || 'Transaction has been sent successfully.'
                                    });
                                } else if (response.id === 400) {
                                    let errorList = "";
                                    if (response.errors && response.errors.length > 0) {
                                        errorList = "<ul style='text-align:left'>";
                                        $.each(response.errors, function(i, err) {
                                            errorList += "<li>" + err + "</li>";
                                        });
                                        errorList += "</ul>";
                                    }

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Posting Failed',
                                        html: (response.message || 'Error occurred') + "<br>" + errorList
                                    });
                                } else if (response.id === 409) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Duplicate Invoice',
                                        html: response.message
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Unknown Response',
                                        html: JSON.stringify(response)
                                    });
                                }
                            } catch (e) {
                                Swal.fire('Error!', 'Invalid JSON response from server.', 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error!', 'Failed to send transaction.', 'error');
                        }
                    });

                }
            });
        });




    });
</script>
