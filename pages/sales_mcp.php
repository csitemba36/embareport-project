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
    <!-- ================= LEFT : GOOGLE DRIVE ================= -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">

            <div class="card-header bg-success-subtle fw-semibold text-success">
                <i class="bi bi-google me-1"></i> Source Google Drive
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="fileTable"
                           class="table table-bordered table-hover table-striped text-nowrap align-middle mb-0 w-100">
                        <thead class="table-success">
                            <tr>
                                <th style="width:50px">No</th>
                                <th style="width:60px" class="text-center">Aksi</th>
                                <th>Nama File</th>
                                <th class="text-end">Ukuran</th>
                                <th>Tanggal Download</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
$(document).ready(function () {

    // ================= LOAD GDRIVE =================
    loadGDriveFiles();

    function loadGDriveFiles() {

        if ($.fn.DataTable.isDataTable('#fileTable')) {
            $('#fileTable').DataTable().clear().destroy();
        }

        $('#fileTable').DataTable({
            processing: true,
            serverSide: false,
            scrollX: true,
            ajax: {
                url: "api/gdrive/listfile_gdrive_mcp.php",
                dataSrc: ""
            },
            columns: [
                {
                    data: null,
                    className: "text-center",
                    render: (d,t,r,m) => m.row + 1
                },
                {
                    data: "name",
                    className: "text-center",
                    orderable: false,
                    render: function (data, type, row) {
                        if (row.is_success) {
                            return `
                                <span class="text-success fw-bold">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>
                            `;
                        }
                        return `
                            <i class="bi bi-arrow-up-square-fill text-primary btn-upload"
                               data-filename="${data}"
                               style="cursor:pointer;font-size:0.9rem;">
                            </i>
                        `;
                    }
                },
                { data: "name" },
                {
                    data: "size",
                    className: "text-end",
                    render: d => (d / 1024).toFixed(1) + " KB"
                },
                { data: "date" },
                { data: "is_success" }
            ],
            rowCallback: function (row, data) {
                if (data.is_success) $(row).addClass('table-success');
            }
        });
    }

    // ================= UPLOAD =================
    $(document).on("click", ".btn-upload", function () {

        let filename = $(this).data("filename");

        Swal.fire({
            title: 'Upload file?',
            text: `File "${filename}" akan diproses`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, upload'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "api/gdrive/upload_sales_from_gdrive.php",
                type: "POST",
                dataType: "json",
                data: { filename },
                success: function (res) {
                    Swal.close();
                    if (res.status) {
                        Swal.fire('Berhasil', 'Upload selesai', 'success');
                        loadGDriveFiles();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            });
        });
    });


});
</script>
