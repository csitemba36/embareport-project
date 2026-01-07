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

	/*pivot*/
	.pvtTable th {
		vertical-align: top !important;
	}

    /* Wrapper detail tabel */
    .detail-wrapper {
        background-color: #f8f9fa !important;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 1rem;
        margin: 0.5rem 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }

    /* Judul di atas tabel detail */
    .detail-wrapper h6 {
        font-weight: 600;
        color: #0d6efd;
        margin-bottom: 0.75rem;
    }

    /* Efek highlight pada baris utama */
    .row-highlight {
        background-color: #e8f7e8 !important;
        transition: background-color 0.3s ease;
    }

</style>



<div class="card mb-2 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h6 class="mb-0">Filter Data</h6>
	</div>
    <div class="card-body">
  <form id="filterForm" class="row g-2 align-items-center">

    <div class="col-auto">
      <select id="brandSelect" class="form-select form-select-sm" style="min-width: 160px;">
        
        <option value="emba_jeans">EMBA DENIM</option>
        <option value="bbg_twist">BBG TWIST</option>
      </select>
    </div>

    <div class="col-auto">
      <button id="btn-retrieve" type="button" class="btn btn-primary btn-sm" style="font-size: 0.75rem;">
        Retrieve
      </button>
    </div>

  </form>
</div>

</div>


<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Approval OD & OL</h6> <button class="btn btn-sm btn-success me-2" id="DTexportExcel"  style="font-size:0.8rem; line-height:1.2;">Datatable Export Excel</button>
    </div>
    <div class="card-body">
	<!-- Tabel -->
        <div class="table-responsive">
            <table id="tbl_tjual1" class="table table-bordered table-hover table-sm table-striped nowrap" style="width:100%">
                <thead>
                    <tr>
                    <th>Detail</th>
                    <th>Bukti ID</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Bukti Rekap</th>
                    <th>Tipe Transaksi</th>
                    <th>-</th>
                    
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>


<script>    
$(document).ready(function() {

    
      let table;

      table = $('#tbl_tjual1').DataTable({
        
      });

        $('#btn-retrieve').on('click', function () {
        const selectedBrand = $('#brandSelect').val();

        if (table) {
            table.destroy(); // Hancurkan tabel lama agar bisa refresh dengan data baru
        }

        table = $('#tbl_tjual1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
            url: 'api/powerone-api-service/db-processing-sales-order.php',
            type: 'GET',
            data: {
                brand: selectedBrand  // ⬅️ kirim parameter brand ke PHP
            }
            },
            columns: [
                {
                    data: 'detail',
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary btn-detail" data-id="${row.bukti_id}">
                                Detail
                            </button>
                        `;
                    }
                },
            { data: 'bukti_id' },
            { data: 'tgl' },
            { data: 'kd_cust' },
            { data: 'bukti_rekap' },
            {
                data: 'tipe_trans',
                render: function (data, type, row) {
                return data === '02' ? 'KIRIM' : (data || '-');
                }
            },
            { 
                data: 'flag_print_sj',
                render: function (data, type, row) {
                if (row.flag_print_sj == 0) {
                    return `
                    <button class="btn btn-sm btn-primary btn-approve" data-id="${row.bukti_id}">
                        Approve
                    </button>
                    `;
                } else {
                    return `<span class="text-success">✅ Approved</span>`;
                }
                }
            }
            ],
            order: [[1, 'desc']],
            pageLength: 10
        });
        });

      // === Event klik tombol Approve ===
      $('#tbl_tjual1').on('click', '.btn-approve', function () {
            const buktiId = $(this).data('id');
            const brand = $('#brandSelect').val(); // 🔹 ambil nilai brand dari select

            Swal.fire({
                title: 'Konfirmasi Approval',
                text: `Apakah Anda yakin ingin approve nomor bukti "${buktiId}" ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/powerone-api-service/approve_validasi_so.php',
                        type: 'POST',
                        dataType: 'json', // 🔹 biar response JSON otomatis diparse
                        data: {
                            bukti_id: buktiId,
                            brand: brand // 🔹 kirim brand juga ke PHP
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire('Berhasil!', response.message, 'success');
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                            $('#tabel_data').DataTable().ajax.reload(null, false);
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat proses.', 'error');
                        }
                    });
                }
            });
        });


        $('#tbl_tjual1 tbody').on('click', '.btn-detail', function () {
            const selectedBrand = $('#brandSelect').val();
            const tr = $(this).closest('tr');
            const row = table.row(tr);
            const buktiId = $(this).data('id');
            const brand = selectedBrand;

            if (row.child.isShown()) {
                // Jika sudah terbuka → tutup
                row.child().slideUp(200, function () {
                    row.child.hide();
                    tr.removeClass('shown row-highlight');
                });
                $(this).text('Detail');
            } else {
                // Tampilkan loading dulu
                $(this).text('Loading...');

                // Ambil data detail via AJAX
                $.ajax({
                    url: 'api/powerone-api-service/get_tjual2_detail.php',
                    type: 'GET',
                    data: { bukti_id: buktiId, brand: brand },
                    success: function (response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                row.child(formatDetailTable(res.data)).show();
                                const childRow = $(tr).next('tr');
                                childRow.find('td').addClass('p-0');

                                // Tambahkan animasi slide + highlight
                                childRow.find('.detail-wrapper').hide().slideDown(250);
                                tr.addClass('shown row-highlight');

                                // Ganti teks tombol
                                $('.btn-detail[data-id="' + buktiId + '"]').text('Tutup');
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        } catch (err) {
                            Swal.fire('Error', 'Respon tidak valid dari server.', 'error');
                        }
                        $('.btn-detail[data-id="' + buktiId + '"]').text('Detail');
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal mengambil data detail.', 'error');
                        $('.btn-detail[data-id="' + buktiId + '"]').text('Detail');
                    }
                });
            }
        });



        function formatDetailTable(data) {
            let totalQty = 0;
            let totalHarga = 0;

            let html = `
                <div class="detail-wrapper p-3 bg-light border rounded shadow-sm mt-2 mb-2 mx-2">
                    <h6 class="fw-bold text-primary mb-3">
                        📦 Detail Transaksi
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped align-middle mb-0">
                            <thead class="table-secondary">
                                <tr class="text-center">
                                    <th>Urut</th>
                                    <th>Kode SKU</th>
                                    <th>Qty1</th>
                                    <th>Qty2</th>
                                    <th>Harga Jual</th>
                                    <th>Pot Jual</th>
                                    <th>Add Jual</th>
                                    <th>Pot1</th>
                                    <th>Pot2</th>
                                    <th>Kd Acara</th>
                                    <th>Tipe Harga</th>
                                    <th>Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            if (data.length > 0) {
                data.forEach(item => {
                    totalQty += parseFloat(item.qty1 || 0);
                    totalHarga += parseFloat(item.total_harga || 0);

                    html += `
                        <tr>
                            <td class="text-center">${item.urut}</td>
                            <td>${item.kd_sku}</td>
                            <td class="text-end">${Number(item.qty1).toLocaleString()}</td>
                            <td class="text-end">${Number(item.qty2).toLocaleString()}</td>
                            <td class="text-end">${Number(item.hrg_jual).toLocaleString()}</td>
                            <td class="text-end">${Number(item.pot_jual).toLocaleString()}</td>
                            <td class="text-end">${Number(item.add_jual).toLocaleString()}</td>
                            <td class="text-end">${Number(item.pot1).toLocaleString()}</td>
                            <td class="text-end">${Number(item.pot2).toLocaleString()}</td>
                            <td class="text-center">${item.kd_acara ?? '-'}</td>
                            <td class="text-center">${item.tipe_harga ?? '-'}</td>
                            <td class="text-end fw-bold">${Number(item.total_harga).toLocaleString()}</td>
                        </tr>
                    `;
                });
            } else {
                html += `<tr><td colspan="12" class="text-center text-muted">Tidak ada detail</td></tr>`;
            }

            html += `
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">Total</td>
                                    <td class="text-end">${totalQty.toLocaleString()}</td>
                                    <td colspan="8"></td>
                                    <td class="text-end">${totalHarga.toLocaleString()}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            `;

            return html;
        }




});

</script>