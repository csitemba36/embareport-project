<style>
/* ==== CSS kamu tetap, tidak diubah ==== */
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
.highlight { background-color: #fff3cd !important; }
.dataTables_length { margin-right: 10px; display: flex; align-items: center; }
.dt-buttons { margin-left: 10px; display: flex; gap: 5px; }
.card-body { font-size: 12px; }
.card-body table th, .card-body table td { padding: 4px 8px; }
.card-body .btn { font-size: 12px; padding: 5px 6px; }
.card-body .pagination { font-size: 12px; }
.card-body .page-item { margin: 0 2px; }
.card-body .page-link { padding: 2px 6px; font-size: 12px; }
tr.filters input { font-size: 12px; }
.card-bg-kuning { background-color: #fff3cd !important; }
.cilik { font-size: 12px; }
#pimTable th, #pimTable td { white-space: nowrap; }
td.dt-control { text-align: center; cursor: pointer; }
td.dt-control::before { font-size: 14px; color: #0d6efd; margin-right: 0; }
.expander { cursor: pointer; font-size: 14px; display: inline-block; color: #0d6efd; }


.badge-same {
    background-color: #198754;
    color: #fff;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 11px;
}

.badge-diff {
    background-color: #dc3545;
    color: #fff;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 11px;
}
</style>


<div class="card mb-2 shadow-sm">
  <div class="card-body">

    <small class="text-muted">Informasi data dibawah ini sumber dari <strong>MASERP</strong>.</small>
    <br /><br />

    <div class="table-responsive">
        <!-- ================= FILTER BARIS 1 ================= -->
        <div class="row mb-2">
            <div class="col-md-3">
                <label class="form-label cilik">Brand</label>
                <select id="filterBrand" class="form-select select2">
                    <option value="">-- Semua Brand --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label cilik">Bukti ID</label>
                <input type="text" id="filterBukti"
                    class="form-control form-control-sm"
                    placeholder="Bukti ID">
            </div>

            <div class="col-md-3">
                <label class="form-label cilik">Customer</label>
                <input type="text" id="filterCustomer"
                    class="form-control form-control-sm"
                    placeholder="GdgTarget">
            </div>

           <!-- <div class="col-md-3">
                <label class="form-label cilik">Initial Store</label>
                <input type="text" id="filterInitial"
                    class="form-control form-control-sm"
                    placeholder="Initial">
            </div> -->
        </div>

        <!-- ================= FILTER BARIS 2 ================= -->
        <div class="row mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label cilik">Tanggal Dari</label>
                <input type="date" id="dateFrom"
                    class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label cilik">Tanggal Sampai</label>
                <input type="date" id="dateTo"
                    class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <button id="btnFilterBrand"
                        class="btn btn-primary btn-sm mt-3 w-100">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </div>
        <div id="infoHint" class="alert alert-info py-2 mb-2">
            <i class="bi bi-info-circle me-1"></i>
            ℹ️ Silakan pilih Brand lalu klik <b>Tampilkan</b> untuk memuat data
        </div>
        <ul class="nav nav-tabs mb-2" id="postTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    data-posted="0"
                    type="button">
                    ⏳ Belum Dipost
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    data-posted="1"
                    type="button">
                    ✅ Sudah Dipost
                </button>
            </li>
        </ul>
      <table id="tspdo1Tabledua" class="table table-striped table-hover table-bordered table-sm nowrap" style="width:100%">
          <thead class="table-light">
              <tr>
                  <th></th>
                  <th>Bukti ID</th>
                  <th>Tanggal</th>
                  <th>Customer</th>
                  <th>Brand</th>
		          <th>Initial Store CIMS</th>
              </tr>
          </thead>
      </table>
    </div>
  </div>
</div>



<script>

// ================= GLOBAL STORAGE =================

if (!window.detailItemsByBukti) {
    window.detailItemsByBukti = {};
}
window.postedBukti = window.postedBukti || {};
window.filterPosted = 0; // default: Belum Dipost

$('#btnFilterBrand').on('click', function () {

    let brand = $('#filterBrand').val();

    let table = $('#tspdo1Tabledua').DataTable();

    let bukti  = $('#filterBukti').val();

    let from = $('#dateFrom').val();
    let to   = $('#dateTo').val();

    if (from && to && from > to) {
        Swal.fire({
            icon: 'warning',
            title: 'Tanggal Tidak Valid',
            text: 'Tanggal dari tidak boleh lebih besar dari tanggal sampai'
        });
        return;
    }

    if (!brand && !bukti) {
        Swal.fire({
            icon: 'info',
            title: 'Filter Brand Kosong',
            text: 'Pilih Brand terlebih dahulu untuk menampilkan data'
        });
        return;
    }
    $('#infoHint').fadeOut();
    // set parameter tambahan ke ajax
    table.ajax.reload(null, false);
});

$('#postTab').on('click', '.nav-link', function () {
    // ⛔ WAJIB PILIH BRAND
    if (!$('#filterBrand').val()) {
        Swal.fire({
            icon: 'info',
            title: 'Pilih Brand',
            text: 'Pilih Brand terlebih dahulu'
        });
        return;
    }

    $('#postTab .nav-link').removeClass('active');
    $(this).addClass('active');

    window.filterPosted = $(this).data('posted');

    let table = $('#tspdo1Tabledua').DataTable();
    table.ajax.reload(null, false);
});

$(document).ready(function() {
    
    let today = new Date().toISOString().slice(0,10);
    $('#dateFrom').val(today);
    $('#dateTo').val(today);

    // LOAD BRAND
    $.ajax({
        url: 'api/get-brands.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {

            let select = $('#filterBrand');
            select.empty();
            select.append('<option value="">Semua Brand</option>');

            res.data.forEach(function (b) {

                let text = `${b.kodemerk} - ${b.brand_name}`;

                select.append(
                    new Option(text, b.kodemerk, false, false)
                );
            });
            
            $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Brand',
            allowClear: true
        });

        }
    });

    var table = $('#tspdo1Tabledua').DataTable({
        processing: true,
        serverSide: true,
        searching: false,   // ⬅️ INI
        // 🔥 DEFAULT SORT
        order: [[2, 'desc']],   // kolom Tanggal DESC
        deferLoading: 0, // ⛔ STOP AUTO LOAD
        pageLength: 10,       // ⬅ WAJIB
        lengthMenu: [10, 25, 50],
        deferRender: true,   // ⬅ PENTING
        ajax:   {
                    url: "api/db-processing-transfer-dua.php",
                    data: function (d) {
                        d.brand = $('#filterBrand').val(); // ⬅️ kirim brand
                        d.buktiId = $('#filterBukti').val();
                        d.customer = $('#filterCustomer').val();
                        //d.initial  = $('#filterInitial').val();
                        d.dateFrom = $('#dateFrom').val();
                        d.dateTo   = $('#dateTo').val();
                        d.isPosted  = window.filterPosted; // 🔥 TAMBAHAN
                    }
                },
        scrollX: true,
        language: {
        emptyTable: `
            <div class="text-center py-4">
                <i class="bi bi-info-circle fs-4 text-primary"></i><br>
                <span class="fw-semibold">
                    ℹ️ Silakan pilih Brand lalu klik <b>Tampilkan</b> untuk memuat data
                </span>
            </div>
        `
    },
        columns: [
            {
                className: 'details-control text-center',
                orderable: false,
                data: null,
                defaultContent: '<span class="expander">&#9654;</span>',
                width: "30px"
            },
            { data: "BuktiID" },
            { data: "Tanggal" },
            { data: "Customer" },
            { data: "BRAND" },
            { data: "InitialStore" }
        ]
    });

    // === CLICK EXPAND ===
    $('#tspdo1Tabledua tbody').on('click', 'td.details-control', function () {

        var tr   = $(this).closest('tr');
        var row  = table.row(tr);
        var icon = $(this).find('.expander');

        if (row.child.isShown()) {
            row.child.hide();
            icon.html('&#9654;');
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            icon.html('&#9660;');
            tr.addClass('shown');
        }
    });

});

function renderItemTable(row, items, safeID) {

    let tbody   = $(`.item-table-${safeID} tbody`);
    let btnPost = $(`.btn-post-transaction[data-bukti="${row.BuktiID}"]`);

    tbody.empty();

    let initialOK = row.InitialStore && row.InitialStore.trim() !== '';

    if (!items || items.length === 0) {
        tbody.append(`<tr><td colspan="7">Tidak ada item</td></tr>`);
        return;
    }

    let no = 1;
    let hasMismatch = false;

    items.forEach(item => {

        let hargaJual = parseInt(item.HargaJual.replace(/\./g, ''));
        let hargaPO   = item.PricePowerOne
            ? parseInt(item.PricePowerOne.replace(/\./g, ''))
            : null;

        let same = hargaPO !== null && hargaJual === hargaPO;
        if (!same) hasMismatch = true;

        tbody.append(`
            <tr>
                <td>${item.KodeMerk}</td>
                <td class="text-center">${no++}</td>
                <td>${item.KodeItem}</td>
                <td class="text-end">${item.QtyJual}</td>
                <td class="text-end">${item.HargaJual}</td>
                <td class="text-end">${item.PricePowerOne ?? ''}</td>
                <td class="text-center ${same ? 'text-success' : 'text-danger'} fw-bold">
                    ${same ? '✔ Harga Sama' : '✖ Harga Tidak Sama'}
                </td>
            </tr>
        `);
    });

    // ===== FINAL STATUS BUTTON =====
    const syncBtn = $(`.item-table-${safeID}`)
        .parents('div:first')
        .find('.btn-sync-sku');

    // ❌ Initial Store kosong
    if (!initialOK) {
        btnPost
            .prop('disabled', true)
            .removeClass('btn-primary')
            .addClass('btn-secondary')
            .html(`<i class="bi bi-x-circle me-1"></i> Initial Store Kosong`);

        syncBtn
            .prop('disabled', true)
            .removeClass('btn-info btn-secondary')
            .addClass('btn-secondary')
            .html(`<i class="bi bi-cloud-arrow-up me-1"></i> Sync SKU to CIMS`);

        return;
    }

    // ❌ ADA HARGA BEDA → SYNC AKTIF
    if (hasMismatch) {
        btnPost
            .prop('disabled', true)
            .removeClass('btn-primary')
            .addClass('btn-secondary')
            .html(`<i class="bi bi-x-circle me-1"></i> Harga Tidak Sama`);

        syncBtn
            .prop('disabled', false)
            .removeClass('btn-secondary')
            .addClass('btn-primary')
            .html(`<i class="bi bi-cloud-arrow-up me-1"></i> Sync SKU to CIMS`);

    }
    // ✅ SEMUA HARGA SAMA → SYNC DISABLE + BIRU
    else {
        btnPost
            .prop('disabled', false)
            .removeClass('btn-secondary')
            .addClass('btn-primary')
            .html(`<i class="bi bi-send me-1"></i> Post Transaction (${row.BuktiID})`);

        syncBtn
            .prop('disabled', true)
            //.removeClass('btn-secondary')
            //.addClass('btn-info') // 🔵 BIRU
            .html(`<i class="bi bi-check-circle me-1"></i> Sudah sync`);
    }
}


function setupSyncButton(row, items, safeID) {

    const $syncBtn = $(`.item-table-${safeID}`)
        .parents('div:first')
        .find('.btn-sync-sku');

    if (!items || items.length === 0) {
        $syncBtn.prop('disabled', true);
        return;
    }

    $syncBtn
        .attr('data-brand', row.BRAND)
        .attr('data-initial', row.InitialStore ?? '')
        .attr(
            'data-skus',
            JSON.stringify(items.map(i => i.KodeItem))
        );
}

// === CHILD ROW FORMAT ===
function format(row) {

    let safeID = row.BuktiID.replace(/[^a-zA-Z0-9_-]/g, "_");
    let isPosted = row.IsPosted === true;

    let html = `
        <div style="padding:10px 20px;">
            <h6>Detail Transaksi</h6>
            <table class="table table-sm table-bordered">
                <tr><th>Bukti ID</th><td>${row.BuktiID}</td></tr>
                <tr><th>Tanggal</th><td>${row.Tanggal}</td></tr>
                <tr><th>Customer</th><td>${row.Customer}</td></tr>
                <tr><th>Initial Store</th><td>${row.InitialStore ?? ''}</td></tr>
                <tr><th>Brand</th><td>${row.BRAND}</td></tr>
                <tr>
                    <th>Status</th>
                    <td>
                        ${
                            isPosted
                            ? `<span class="badge bg-success">Sudah Dipost</span>`
                            : `<span class="badge bg-warning text-dark">Belum Dipost</span>`
                        }
                    </td>
                </tr>
            </table>

            ${
                isPosted
                ? `
                    <div class="alert alert-success mt-2 mb-0">
                        ✔ Transaksi ini sudah dipost ke CIMS
                    </div>
                `
                : `
                    <h6>Item Detail</h6>
                    <table class="table table-sm table-bordered item-table-${safeID}">
                        <thead>
                            <tr>
                                <th>Brand Code</th>
                                <th>Urut</th>
                                <th>KD_SKU</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga Jual MASERP</th>
                                <th class="text-end">Harga Jual CIMS</th>
                                <th class="text-center">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="7">Loading...</td></tr>
                        </tbody>
                    </table>

                    

                    <div class="action-buttons mt-2">
                        

                        <button
                            class="btn btn-sm btn-primary btn-post-transaction"
                            data-bukti="${row.BuktiID}"
                            data-brand="${row.BRAND}"
                            data-initial="${row.InitialStore ?? ''}">
                            <i class="bi bi-send me-1"></i>
                            Post Transaction
                        </button>

                        <button
                            class="btn btn-sm btn-secondary btn-sync-sku ms-2"
                            data-brand=""
                            data-initial=""
                            data-skus=""
                            disabled>
                            <i class="bi bi-cloud-arrow-up me-1"></i>
                            Sync SKU to CIMS
                        </button>
                    </div>
                `
            }
        </div>
    `;

    // 🚫 JIKA SUDAH POST → STOP
    if (isPosted) {
        return html;
    }

    // 🔥 RENDER SETELAH DOM TERPASANG
    setTimeout(() => {

        // Jika sudah pernah load → render ulang
        if (Array.isArray(window.detailItemsByBukti[row.BuktiID])) {
            //renderItemTable(row, window.detailItemsByBukti[row.BuktiID], safeID);
            //return;
            const items = window.detailItemsByBukti[row.BuktiID];

            renderItemTable(row, items, safeID);
            setupSyncButton(row, items, safeID); // 🔥 WAJIB

            return;
        }

        // Jika sedang loading → skip
        if (window.detailItemsByBukti[row.BuktiID] === 'loading') {
            return;
        }

        // Tandai loading
        window.detailItemsByBukti[row.BuktiID] = 'loading';

        // Load API
        $.getJSON(
            `api/db-processing-transfer-detail-dua.php?buktiID=${encodeURIComponent(row.BuktiID)}`,
            function (items) {
                window.detailItemsByBukti[row.BuktiID] = items;
                renderItemTable(row, items, safeID);
                setupSyncButton(row, items, safeID); // 🔥 WAJIB
                //const $child = row.child();
                //const $syncBtn = $child.find('.btn-sync-sku');
                //const $detailWrapper = $(`.item-table-${safeID}`).closest('div');
                //const $syncBtn = $detailWrapper.find('.btn-sync-sku');
                const $syncBtn = $(`.item-table-${safeID}`)
                    .parents('div:first')
                    .find('.btn-sync-sku');
                console.log('SYNC BTN FOUND:', $syncBtn.length);
                if (items.length > 0) {
                    $syncBtn
                        .attr('data-brand', row.BRAND)               // ⬅️ dari header
                        .attr('data-initial', row.InitialStore ?? '') // ⬅️ dari header
                        .attr(
                            'data-skus',
                            JSON.stringify(items.map(i => i.KodeItem)) // ⬅️ sesuai tabel item
                        )
                        //.prop('disabled', false);

                console.log('SYNC SKU DATA:', {
                    brand: row.BRAND,
                    initial: row.InitialStore,
                    skus: items.map(i => i.KodeItem)
                });
                } else {
                    $syncBtn.prop('disabled', true);
                }
            }
        );

    }, 0);

    return html;
}

$('#tspdo1Tabledua').on('click', '.btn-sync-sku', function () {

    const $btn = $(this);

    // ⛔ cegah klik saat disabled
    if ($btn.prop('disabled')) return;

    const brand   = $btn.data('brand');
    const initial = $btn.data('initial');
    const skusRaw = $btn.attr('data-skus');

    if (!brand || !skusRaw) {
        Swal.fire('Info', 'Data SKU belum siap', 'info');
        return;
    }

    let skuArr;
    try {
        skuArr = JSON.parse(skusRaw);
    } catch (e) {
        Swal.fire('Error', 'Format SKU tidak valid', 'error');
        return;
    }

    // 🔄 loading state
    $btn.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span> Syncing...');

    $.ajax({
        url: "api/db-sync-sku-dua.php",
        type: "POST",
        data: { brand, initial, skus: skuArr },
        dataType: "json"
    })
    .done(function (res) {

        if (!res.details || res.details.length === 0) {
            Swal.fire('Info', 'Tidak ada data ditemukan', 'info');
            return;
        }

        // 🔹 build table
        let tableHtml = `
            <table class="table table-sm table-bordered table-hover small" style="font-size:12px">
                <thead class="table-primary">
                    <tr>
                        <th>Brand</th>
                        <th>Style Code</th>
                        <th>Supplier Barcode</th>
                        <th>Art Desc</th>
                        <th>Price</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Model</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
        `;

        res.details.forEach(d => {
            tableHtml += `
                <tr>
                    <td>${d.brand}</td>
                    <td>${d.style_code}</td>
                    <td>${d.supplier_barcode}</td>
                    <td>${d.art_desc}</td>
                    <td>${Number(d.price).toLocaleString('id-ID')}</td>
                    <td>${d.color}</td>
                    <td>${d.size}</td>
                    <td>${d.model}</td>
                    <td>${d.group}</td>
                </tr>
            `;
        });

        tableHtml += '</tbody></table>';

        // === CEK APAKAH ADA PRICE > 1 ===
        const hasValidPrice = res.details.every(d => Number(d.price) > 1);

        return Swal.fire({
            title: 'Update PIM to CIMS',
            html: tableHtml,
            width: '80%',
            showCancelButton: hasValidPrice,
            showConfirmButton: hasValidPrice,
            confirmButtonText: 'UPDATE PIM to CIMS',
            cancelButtonText: 'Cancel',
            icon: hasValidPrice ? 'question' : 'warning',

             // 🔥 LOGIKA TOMBOL
            showConfirmButton: hasValidPrice,
            confirmButtonText: 'UPDATE PIM to CIMS',

            showCancelButton: true,
            cancelButtonText: hasValidPrice ? 'Cancel' : 'Close',

            // ⛔ disable klik luar & ESC biar user sadar
            allowOutsideClick: false,
            allowEscapeKey: false,

            footer: !hasValidPrice
                ? '<span class="text-danger fw-semibold">⚠ Sync tidak diizinkan, Periksa kembali Data SKU</span>'
                : '<span class="text-warning fw-semibold">⚠ Note : Setelah update berhasil tunggu 1 - 5 menit untuk get/sync Master </span>',
            preConfirm: () => {
                return $.ajax({
                    url: "api/yogya-api-service/yogya-api-pim-post.php",
                    type: "POST",
                    data: { details: res.details },
                    dataType: "json"
                }).then(response => {
                    if (!response || response.error) {
                        Swal.showValidationMessage(
                            `Gagal: ${response.message || 'unknown error'}`
                        );
                    }
                    return response;
                }).catch(() => {
                    Swal.showValidationMessage('Gagal kirim ke API');
                });
            }
        });

    })
    .then(result => {
        if (result && result.isConfirmed) {
            Swal.fire('Sukses', 'Data berhasil dikirim ke CIMS', 'success');
            $btn
                .removeClass('btn-secondary')
                .addClass('btn-success')
                .prop('disabled', true)
                .html('<i class="bi bi-check-circle me-1"></i> Synced');
        }
    })
    .fail(function () {
        Swal.fire('Error', 'Gagal sync ke CIMS', 'error');
    })
    .always(function () {

        // kalau tombol sudah jadi "Synced", jangan diubah lagi
        if ($btn.hasClass('btn-success')) return;

        $btn
            .prop('disabled', false)
            .removeClass('btn-success')
            .addClass('btn-primary')
            .html('<i class="bi bi-cloud-arrow-up me-1"></i> Sync SKU to CIMS');
    });
});


// ===============================
// EVENT POST TRANSACTION (FINAL)
// ===============================
$(document).on('click', '.btn-post-transaction', function () {

    let btn     = $(this);
    let buktiID = btn.data('bukti');

    // ===== AMBIL ITEM DARI STORAGE (GLOBAL) =====
    let items = detailItemsByBukti[buktiID];

    // Header info (dari DOM)
    //let container   = btn.closest('div');
    //let headerTable = container.find('table:first');

    //let initial = headerTable.find('tr:eq(3) td').text().trim();
    //let brand   = headerTable.find('tr:eq(4) td').text().trim();

    let initial = btn.data('initial');
    let brand   = btn.data('brand');

    // ===== BLOK JIKA DISABLED =====
    if (btn.prop('disabled')) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Bisa Dipost',
            text: 'Initial Store kosong atau masih ada harga tidak sama'
        });
        return;
    }

    // ===== VALIDASI STORAGE =====
    if (!items || items.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Detail item kosong'
        });
        return;
    }

    console.log({
        buktiID,
        brand,
        initial,
        items
    });
    // ===== KONFIRMASI =====
    Swal.fire({
        title: 'Post Transaction?',
        html: `
            <div class="text-start">
                <b>Brand:</b> ${brand}<br>
                <b>Bukti ID:</b> ${buktiID}<br>
                <b>Initial Store CIMS:</b> ${initial}
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i> Yes, Post',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {

        if (!result.isConfirmed) return;

        // ===== SUSUN DETAIL =====
        let details = items.map(item => ({
            invoice_number: buktiID,
            invoice_date: new Date().toISOString().split('T')[0],
            supplier_barcode: item.KodeItem, // sudah karakter ke-2
            qty_posting: parseInt(item.QtyJual) || 0
        })).filter(d => d.qty_posting > 0);

        if (details.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Detail item kosong'
            });
            return;
        }

        // ===== BUAT PAYLOAD (INI YANG TADI HILANG) =====
        let payload = {
            initial_store: initial,
            invoice_number: buktiID,
            brand: brand,
            details: details
        };

        // ===== DEBUG CONSOLE =====
        console.group('POST TRANSACTION PAYLOAD');
        console.log('Payload Object:', payload);
        console.log('Details JSON:', JSON.stringify(details, null, 2));
        console.groupEnd();

        // ===== LOADING =====
        Swal.fire({
            title: 'Posting...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // ===== AJAX POST =====
        $.ajax({
            url: 'api/yogya-api-service/db-post-receiving-dua.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),

            success: function (res) {

                let response = (typeof res === 'string') ? JSON.parse(res) : res;

                if (response.id === 200) {

                    // === SIMPAN STATUS POSTED ===
                    window.postedBukti[buktiID] = true;

                    Swal.fire({
                        icon: 'success',
                        title: 'Posted!',
                        text: response.message || 'Transaction berhasil dikirim',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    btn
                        .prop('disabled', true)
                        .removeClass('btn-primary')
                        .addClass('btn-secondary')
                        .html(`<i class="bi bi-check-circle me-1"></i> Sudah Dipost`);
                } else if (response.id === 409) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Invoice',
                        text: response.message
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Posting Failed',
                        text: response.message || 'Terjadi kesalahan'
                    });
                }
            },
            error: function (err) {
                console.error('POST ERROR:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengirim data ke server'
                });
            }
        });
    });
});



</script>