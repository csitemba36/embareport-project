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

   /* ===============================
   INPUT & SELECT2 VERSI COMPACT
    ================================ */

    /* Base height lebih kecil */
    .form-control,
    .select2-container .select2-selection--single {
        height: 32px !important;
        min-height: 32px !important;
        font-size: 13px;
    }

    /* Input date padding diperkecil */
    input[type="date"].form-control {
        padding: 4px 8px;
    }

    /* Select2 align vertical center */
    .select2-container--default
    .select2-selection--single {
        display: flex;
        align-items: center;
        padding: 0 6px;
    }

    /* Text select2 */
    .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 30px;
        padding-left: 4px;
        padding-right: 4px;
    }

    /* Arrow select2 ikut kecil */
    .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        height: 30px;
    }

    /* Lebar penuh */
    .select2-container {
        width: 100% !important;
    }

    /* Label sedikit diperkecil biar proporsional */
    .form-group label,
    label {
        font-size: 12px;
        margin-bottom: 4px;
    }

    #salesTable thead .filter-row th {
        padding: 4px;
    }

    #salesTable thead .filter-row input {
        height: 28px;
        font-size: 12px;
        padding: 2px 6px;
    }


</style>


<div class="row g-4">

    <div class="col-lg-12">
        <div class="card shadow-sm h-100 border-0">

            <!-- ================= CARD HEADER ================= -->
            <div class="card-header bg-primary-subtle fw-semibold text-primary d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-receipt me-1"></i> Sales Invoice
                </div>

                <div class="d-flex gap-2">
                    <button id="btnGetToken" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-key me-1"></i> Get API Token
                    </button>

                    <button id="btnGetStore" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-shop me-1"></i> Get Store
                    </button>
                </div>
            </div>

            <!-- ================= TABS ================= -->
            <div class="card-body">

                <ul class="nav nav-tabs mb-3" id="salesTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                                id="data-sales-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#data-sales"
                                type="button"
                                role="tab">
                            <i class="bi bi-table me-1"></i> Data Sales
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                id="history-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#history"
                                type="button"
                                role="tab">
                            <i class="bi bi-clock-history me-1"></i> History
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- ================= TAB : DATA SALES ================= -->
                    <div class="tab-pane fade show active" id="data-sales" role="tabpanel">

                        <!-- FILTER -->
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Merk</label>
                                <select id="filter-merk" class="form-control select2 filter-input">
                                    <option value="">-- Choose --</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Periode Awal</label>
                                <input type="date"
                                       id="start_date"
                                       class="form-control"
                                       value="<?= date('Y-m-01') ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Periode Akhir</label>
                                <input type="date"
                                       id="end_date"
                                       class="form-control"
                                       value="<?= date('Y-m-t') ?>">
                            </div>

                            <div class="col-md-2">
                                <button id="btn-filter" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i> Terapkan
                                </button>
                            </div>
                        </div>

                        <hr class="mt-1 mb-3">

                        <!-- TABLE -->
                        <div class="table-responsive">
                            <table id="salesTable"
                                   class="table table-bordered table-hover table-striped nowrap align-middle mb-0 w-100">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Push</th>
                                        <th>Dept</th>
                                        <th>Process Date</th>
                                        <th>Transact Date</th>
                                        <th>Store No</th>
                                        <th>Store Name</th>
                                        <th>POS No</th>
                                        <th>Transact No</th>
                                        <th>Total Qty</th>
                                        <th>Total Gross</th>
                                        <th>Total Discount</th>
                                        <th>Total Net</th>
                                        <th>Referensi File</th>
                                        <th>User Upload</th>
                                        <th>Tgl Upload</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>

                    <!-- ================= TAB : HISTORY ================= -->
                    <div class="tab-pane fade" id="history" role="tabpanel">

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            History proses (token, store, push sales, dsb).
                        </div>

                        <div class="table-responsive">
                            <table id="historyTable"
                                   class="table table-bordered table-hover table-striped align-middle w-100">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Aktivitas</th>
                                        <th>Deskripsi</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- isi via ajax -->
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

</div>


<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock me-1"></i> MCP API Token Response
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light small">
                <!-- response API tampil di sini -->
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function () {
    // ================= FILTER =================
    $(document).on("click", "#btn-filter", function () {
        loadSalesTable();
    });

    // ================= SALES TABLE =================
    $('#salesTable').DataTable({

    });
        
    function loadSalesTable() {

        if ($.fn.DataTable.isDataTable('#salesTable')) {
            $('#salesTable').DataTable().clear().destroy();
        }

        let table = $('#salesTable').DataTable({
            processing: true,
            serverSide: false,
            scrollX: true,
            responsive: false,
            pageLength: 10,

            ajax: {
                url: "api/get_sales_mcp.php",
                type: "GET",
                data: function (d) {
                    d.merk       = $('#filter-merk').val();
                    d.start_date = $('#start_date').val();
                    d.end_date   = $('#end_date').val();
                },
                dataSrc: "data"
            },

            columns: [
                {
                    className: 'details-control text-center',
                    orderable: false,
                    data: "TRANSACT_NO",
                    render: () => '<span class="expander">&#9654;</span>',
                    width: "30px"
                },
                { data: "DEPT" },
                { data: "PROCESS_DATE" },
                { data: "TRANSACT_DATE" },
                { data: "STORE_NO" },
                { data: "STORE_NAME" },
                { data: "POS_NO" },
                { data: "TRANSACT_NO" },
                { data: "TOTAL_QTY", className: "text-end" },
                { data: "TOTAL_GROSS", className: "text-end" },
                { data: "TOTAL_DISCOUNT", className: "text-end" },
                { data: "TOTAL_NET", className: "text-end" },
                { data: "REFERENSI_FILE" },
                { data: "USER_UPLOAD" },
                { data: "TGL_UPLOAD" }
            ]
        });


        // ================= EXPAND DETAIL =================
        $('#salesTable tbody').off('click', 'td.details-control');
        $('#salesTable tbody').on('click', 'td.details-control', function () {

            let tr = $(this).closest('tr');
            let row = table.row(tr.hasClass('child') ? tr.prev() : tr);

            if (!row.data()) return;

            let data = row.data();
            let icon = $(this).find('.expander');

            // ================= TOGGLE CLOSE =================
            if (row.child.isShown()) {
                row.child.hide();
                icon.html('&#9654;');
                return;
            }

            icon.html('&#9660;');

            // ================= LOADING =================
            row.child(`
                <div class="p-2 text-center">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    Loading detail...
                </div>
            `).show();

            // ================= PARAM =================
            let param = {
                dept: data.DEPT,
                store_no: data.STORE_NO,
                transact_no: data.TRANSACT_NO,
                pos_no: data.POS_NO
            };

            // ================= AJAX DETAIL =================
            $.ajax({
                url: "api/get_sales_mcp_detail.php",
                type: "GET",
                data: param,
                dataType: "json",
                success: function (res) {

                    if (!res.data || res.data.length === 0) {
                        row.child('<div class="p-2">Tidak ada detail transaksi</div>').show();
                        return;
                    }

                    let html = `
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Item</th>
                                        <th>Barcode</th>
                                        <th>Brand</th>
                                        <th>Colour</th>
                                        <th>Size</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Disc Auto</th>
                                        <th class="text-end">Disc Promo</th>
                                        <th class="text-end">Disc Employee</th>
                                        <th class="text-end">Disc Free Item</th>
                                        <th class="text-end">Disc Struk</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Gross</th>
                                        <th class="text-end">Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    res.data.forEach(d => {
                        html += `
                            <tr>
                                <td>${d.ITEM}</td>
                                <td>${d.BARCODE}</td>
                                <td>${d.BRAND}</td>
                                <td>${d.COLOUR}</td>
                                <td>${d.SIZE}</td>
                                <td class="text-end">${d.QTY}</td>
                                <td class="text-end">${d.DISC_AUTO}</td>
                                <td class="text-end">${d.DISC_PROMO}</td>
                                <td class="text-end">${d.DISC_EMPLOYEE}</td>
                                <td class="text-end">${d.DISC_FREE_ITEM}</td>
                                <td class="text-end">${d.DISC_STRUK}</td>
                                <td class="text-end">${Number(d.ITEM_PRICE).toLocaleString('id-ID')}</td>
                                <td class="text-end">${Number(d.GROSS_AMT).toLocaleString('id-ID')}</td>
                                <td class="text-end fw-bold">${Number(d.NET_AMT).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>

                        <!-- ACTION BAR -->
                        <div class="d-flex justify-content-start mt-3 gap-2">
                            <button class="btn btn-sm btn-success btnPushMasERP"
                                    data-dept="${param.dept}"
                                    data-transact-no="${param.transact_no}"
                                    data-store-name="${data.STORE_NAME}"
                                    data-store-no="${data.STORE_NO}">
                                <i class="bi bi-cloud-upload me-1"></i> Push to MASERP
                            </button>
                        </div>
                    `;

                    row.child(html).show();
                },
                error: function () {
                    row.child('<div class="p-2 text-danger">Gagal memuat detail</div>').show();
                }
            });

        });

    }

    // ================= SELECT2 =================
    $('.select2').select2({
        width: '100%',
        placeholder: '-- Choose --',
        allowClear: true
    });

    $('#filter-merk').select2({
        ajax: {
            url: 'api/get_brand.php',
            dataType: 'json',
            delay: 250,
            data: params => ({ term: params.term }),
            processResults: data => ({ results: data })
        }
    });

    $('#btnGetToken').on('click', function () {

        let btn = $(this);

        Swal.fire({
            title: 'Ambil API Token?',
            html: `
                Token baru akan diminta ke MCP.<br>
                <small class="text-muted">
                    Token lama tetap tersimpan namun tidak digunakan.
                </small>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-key me-1"></i> Ya, Ambil Token',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {

            // ⛔ JIKA BATAL
            if (!result.isConfirmed) return;

            // ===============================
            // LOADING STATE
            // ===============================
            btn.prop('disabled', true)
            .html('<i class="bi bi-arrow-repeat"></i> Requesting...');

            Swal.fire({
                title: 'Processing...',
                text: 'Sedang meminta API Token',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // ===============================
            // AJAX CALL
            // ===============================
            $.ajax({
                url: 'api/mcp/get_token_login.php',
                type: 'GET',
                dataType: 'html', // PENTING karena API echo HTML
                success: function (response) {

                    Swal.close();

                    // Tampilkan hasil ke modal
                    $('#tokenModal .modal-body').html(response);
                    $('#tokenModal').modal('show');
                },
                error: function (xhr, status, error) {

                    Swal.close();

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat mengambil API Token',
                    });
                },
                complete: function () {
                    btn.prop('disabled', false)
                    .html('<i class="bi bi-key me-1"></i> Get API Token');
                }
            });

        });
    });

    $('#btnGetStore').on('click', function () {

        let btn = $(this);

        Swal.fire({
            title: 'Ambil Data Store?',
            text: 'Data store dari MCP akan di-sync ke database.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ambil',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (!result.isConfirmed) return;

            btn.prop('disabled', true)
            .html('<i class="bi bi-arrow-repeat me-1"></i> Loading...');

            $.ajax({
                url: 'api/mcp/get_store.php',
                type: 'GET',
                dataType: 'json',
                success: function (res) {

                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: `
                                    <div class="text-start">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Total Data</span>
                                            <span class="badge bg-secondary">${res.total_api_data}</span>
                                        </div>

                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold text-success">Inserted</span>
                                            <span class="badge bg-success">${res.inserted}</span>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold text-primary">Updated</span>
                                            <span class="badge bg-primary">${res.updated}</span>
                                        </div>
                                    </div>
                            `
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Terjadi kesalahan'
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memanggil API Store'
                    });
                },
                complete: function () {
                    btn.prop('disabled', false)
                    .html('<i class="bi bi-shop me-1"></i> Get Store');
                }
            });

        });

    });

    $(document).on('click', '.btnPushMasERP', function () {

        const transactNo = $(this).data('transact-no');
        const storeNo    = $(this).data('store-no');
        const storeName  = $(this).data('store-name');
        const dept       = $(this).data('dept');

        //alert(dept);

        Swal.fire({
            title: 'Push ke MASERP?',
            icon: 'warning',
            html: `
                <div class="text-center">
                    <div class="fw-semibold mb-1">
                        Transaksi <span class="text-primary">${transactNo}</span>
                    </div>
                    <small class="text-muted">
                        Toko <b>${storeName}</b> akan dikirim ke MASERP
                    </small>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-cloud-upload me-1"></i> Ya, Push',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Processing...',
                text: 'Mengirim data ke MASERP',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: 'api/mcp/push_sales_mcp_to_maserp.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    transact_no: transactNo,
                    store_no: storeNo,
                    dept: dept
                },
                success: function (res) {

                    Swal.fire({
                        icon: res.status ? 'success' : 'error',
                        title: res.status ? 'Push Berhasil' : 'Push Gagal',
                        html: `
                            <div class="text-start">
                                <div class="mb-2">
                                    <b>Message:</b><br>
                                    <span class="${res.status ? 'text-success' : 'text-danger'}">
                                        ${res.message}
                                    </span>
                                </div>

                                <hr class="my-2">

                                <b>Response JSON:</b>
                                <pre style="
                                    background:#f8f9fa;
                                    border:1px solid #dee2e6;
                                    padding:10px;
                                    max-height:250px;
                                    overflow:auto;
                                    font-size:12px;
                                ">${JSON.stringify(res, null, 2)}</pre>
                            </div>
                        `,
                        width: 700,
                        confirmButtonText: 'OK'
                    });

                },
                error: function (xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        html: `
                            <pre style="
                                background:#f8f9fa;
                                border:1px solid #dee2e6;
                                padding:10px;
                                max-height:250px;
                                overflow:auto;
                                font-size:12px;
                            ">${xhr.responseText}</pre>
                        `,
                        width: 700
                    });

                }
            });

        });

    });

});


</script>
