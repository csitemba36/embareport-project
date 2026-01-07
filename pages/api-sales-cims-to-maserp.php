<style>
.select2-container--default .select2-selection--single {
    height: 30px;
    padding: 2px 5px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
}
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  top: -3px;
}
.highlight {
    background-color: #fff3cd !important;
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
  font-size: 12px;
}
.card-body table th,
.card-body table td {
  padding: 4px 8px;
}
.card-body .btn {
  font-size: 12px;
  padding: 5px 6px;
}
.card-body .pagination {
  font-size: 12px;
}
.card-body .page-item {
  margin: 0 2px;
}
.card-body .page-link {
  padding: 2px 6px;
  font-size: 12px;
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
  white-space: nowrap;
}
td.dt-control {
    text-align: center;
    cursor: pointer;
}
td.dt-control::before {
    font-size: 14px;
    color: #0d6efd;
    margin-right: 0;
}
.expander {
    cursor: pointer;
    font-size: 14px;
    display: inline-block;
    color: #0d6efd;
}
/* backdrop lebih gelap */
.modal-backdrop.show {
    background-color: rgba(0,0,0,0.8) !important;
}
</style>

<div class="card mb-3 shadow-sm">

    <div class="card-body">

        <!-- NAV TABS -->
        <ul class="nav nav-tabs" id="salesTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-sales-list" data-bs-toggle="tab"
                    data-bs-target="#content-sales-list" type="button" role="tab">
                    Data Sales
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-history" data-bs-toggle="tab"
                    data-bs-target="#content-history" type="button" role="tab">
                    History
                </button>
            </li>

           
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content mt-3" id="salesTabContent">

            <!-- TAB 1: DATA SALES -->
            <div class="tab-pane fade show active" id="content-sales-list" role="tabpanel">

                <div class="table-responsive">
                    <table id="SalesTable" 
                           class="table table-striped table-hover table-bordered table-sm nowrap" 
                           style="width:100%">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Push JSON</th>
                                <th>Supp Code</th>
                                <th>Brand Code</th>
                                <th>Initial Store</th>
                                <th>Store Name</th>
                                <th>Nomor Bon</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Sales Date</th>
                                <th>Cancel Date</th>
                                <th>Cancel Desc</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>

            <!-- TAB 2: HISTORY -->
            <div class="tab-pane fade" id="content-history" role="tabpanel">
                <table id="SalesLogsTable" 
                            class="table table-striped table-hover table-bordered table-sm " 
                            style="width:100%">
                    <thead>
                        <tr>
                            <th>No Bon</th>
                            <th>Status</th>
                            <th>Error Message</th>
                            <th>Response JSON</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                </table>
            </div>

           
        </div>
    </div>
</div>


<!-- Modal Preview JSON -->
<div class="modal fade" id="pushModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- tambah modal-dialog-centered -->
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h6 class="modal-title text-white">Preview JSON Push</h6>
      </div>
      <div class="modal-body">
        <pre id="jsonPreview" style="background:#f8f9fa; padding:10px; border-radius:5px; max-height:400px; overflow:auto;"></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmPush" class="btn btn-success btn-sm">PUSH to MASERP</button>
      </div>
    </div>
  </div>
</div>


<script>
   $(document).ready(function() {

    var table = $('#SalesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "api/db-processing-sales-cims.php",
        scrollX: true,
        autoWidth: false,
        responsive: false,     // <-- WAJIB UNTUK FIX row.data() undefined    
        pageLength: 20,
        columns: [
            {
                className: 'details-control text-center',
                orderable: false,
                data: "nomor_bon",        // <-- WAJIB ADA, JANGAN NULL!
                render: function() {
                    return '<span class="expander">&#9654;</span>';
                },
                width: "30px"
            },
            {
                className: "text-center",
                orderable: false,
                data: "nomor_bon",   // <-- WAJIB ADA biar tidak child-row
                render: function(data, type, row) {
                    return `
                        <i class="bi bi-arrow-up-circle-fill btn-push text-success" 
                        style="cursor: pointer;" 
                        data-row='${JSON.stringify(row)}'></i>
                    `;
                }
            },

            { data: "supp_code" },
            { data: "brand_code" },
            { data: "initial_store" },
            { data: "store_name" },
            { data: "nomor_bon" },
            { data: "created_by" },
            { data: "type" },
            { data: "status" },
            { data: "sales_date" },
            { data: "cancel_date" },
            { data: "cancel_desc" },
            { data: "created_at" },
            { data: "updated_at" }
        ]
    });

    // klik expand
    $('#SalesTable tbody').on('click', 'td.details-control', function () {
        var table = $('#SalesTable').DataTable();

        var tr = $(this).closest('tr');
        var row = table.row(tr).data();

        //alert(row.nomor_bon);
        nomor_bon = row.nomor_bon;

    

       // FIX: kalau row responsif (punya child), pindah ke parent
       if (tr.hasClass('child')) {
                tr = tr.prev(); 
            }

            var row = table.row(tr);

            if (!row.data()) {
                console.warn("ROW DATA TIDAK DITEMUKAN!", tr);
                return;
            }

            var icon = $(this).find('.expander');

            if (row.child.isShown()) {
                row.child.hide();
                icon.html('&#9654;'); // ikon tutup
                return;
            }

            icon.html('&#9660;'); // ikon buka

            row.child(`
                <div class="p-2 text-center">
                    <div class="spinner-border spinner-border-sm text-primary"></div> Loading...
                </div>
            `).show();

            $.ajax({
                url: "api/db-processing-sales-cims-detail.php",
                type: "GET",
                data: { nomor_bon },
                dataType: "json",
                success: function(response){
                    if (response.data?.length > 0) {

                        let html = `
                            <div class="table-responsive">     <!-- WRAPPER SCROLL -->
                                <table class="table table-sm table-bordered table-hover mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>PLU</th>
                                            <th>Promo</th>
                                            <th>Style</th>
                                            <th>Barcode</th>
                                            <th>Deskripsi</th>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                            <th>Bruto</th>
                                            <th>Netto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        response.data.forEach(d => {
                            html += `
                                <tr>
                                    <td>${d.plu_yogya}</td>
                                    <td>${d.promo_name}</td>
                                    <td>${d.style_code}</td>
                                    <td>${d.supplier_barcode}</td>
                                    <td>${d.art_description}</td>
                                    <td>${d.brand_name}</td>
                                    <td>${d.model}</td>
                                    <td>${d.color}</td>
                                    <td>${d.size}</td>
                                    <td align="right">${d.sales_qty}</td>
                                    <td align="right">${parseFloat(d.sales_bruto).toLocaleString('id-ID')}</td>
                                    <td align="right">${parseFloat(d.sales_netto).toLocaleString('id-ID')}</td>
                                </tr>
                            `;
                        });

                        html += `
                                    </tbody>
                                </table>
                            </div>
                        `;

                        row.child(html).show();
                        } else {
                        row.child(`<div class="p-2">Tidak ada detail untuk ${nomor_bon}</div>`).show();
                        }

                }
            });
    });

    // Event tombol push
    $('#SalesTable').on('click', '.btn-push', function () {
            let row = $(this).data('row');   
            let nomorBon = row.nomor_bon;    

            Swal.fire({
                title: 'Push ke MASERP?',
                text: "Nomor Bon: " + nomorBon,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Push!',
                cancelButtonText: 'Batal' 
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang push...',
                        text: 'Tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                            url: 'api/push-json-cims-to-maserp.php',
                            method: 'GET',
                            data: { nomor_bon: nomorBon },
                            dataType: 'json',

                            // Jika status HTTP 200 → success
                            success: function (response) {
                                tampilkanPesan(response);
                            },

                            // Tangkap respon JSON meskipun status 400 / 500
                            statusCode: {
                                400: function (response) {
                                    tampilkanPesan(response.responseJSON);
                                },
                                401: function (response) {
                                    tampilkanPesan(response.responseJSON);
                                },
                                403: function (response) {
                                    tampilkanPesan(response.responseJSON);
                                },
                                404: function (response) {
                                    tampilkanPesan(response.responseJSON);
                                },
                                500: function (response) {
                                    tampilkanPesan(response.responseJSON);
                                }
                            },

                            // Jika AJAX gagal total (server down / timeout)
                            error: function (xhr, status, error) {
                                Swal.fire({
                                    title: 'Error AJAX',
                                    text: 'Tidak dapat menghubungi server: ' + error,
                                    icon: 'error'
                                });
                            }
                        });

                        function tampilkanPesan(response){
                            let pesan = "Tidak ada pesan dari API";

                            if (response?.response?.message) {
                                pesan = response.response.message;
                            } else if (response?.message) {
                                pesan = response.message;
                            } else if (response?.error) {
                                pesan = response.error;
                            }

                            let isLong = pesan.length > 100 || pesan.includes("\n");

                            let htmlPesan = isLong 
                                ? `<pre style="text-align:left; white-space:pre-wrap; max-height:300px; overflow-y:auto;">${pesan}</pre>`
                                : pesan;

                            Swal.fire({
                                title: (response.status == 200 ? 'Sukses' : 'Gagal'),
                                html: htmlPesan,
                                icon: (response.status == 200 ? 'success' : 'error'),
                                confirmButtonText: 'OK',
                                width: isLong ? 600 : undefined
                            });
                        }
                }
            });
        });


        // klik tombol "Kirim ke API"
        $("#confirmPush").on("click", function() {
            let payload = $("#jsonPreview").text();

            $.ajax({
                url: "api/push-to-api.php",
                type: "POST",
                data: { payload: payload },
                success: function(res) {
                    alert("Push berhasil: " + res);
                    var modalEl = document.getElementById('pushModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    table.ajax.reload();
                },
                error: function(err) {
                    alert("Push gagal: " + err.responseText);
                }
            });
        });


        var table = $('#SalesLogsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "api/get_sales_logs.php",
                type: "POST"
            },
            //scrollX: true,
            pageLength: 20,
            columns: [
                { data: "nomor_bon" },
                { 
                    data: "status_code",
                    render: function (data) {
                        if (data == 200) {
                            return '<span class="badge bg-success">200</span>';
                        } else {
                            return '<span class="badge bg-danger">' + data + '</span>';
                        }
                    }
                },
                { data: "error_message" },
                { data: "response_json" },
                { data: "created_at" }
            ]
        });


    });
</script>