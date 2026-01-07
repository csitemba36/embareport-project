<style>
	.select2-container--default .select2-selection--single {
		height: 38px; /* Sesuaikan dengan tinggi input lain */
		padding: 6px 12px;
		border: 1px solid #ced4da; /* Warna border input */
		border-radius: 4px; /* Border radius sama */
		font-size: 14px;
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
	
    .user-info {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        background: white;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .user-info th,
    .user-info td {
    padding: 8px 14px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    }

    .user-info th {
    font-weight: 600;
    color: #334155;
    background: #f8fafc;
    width: 30%;
    }

    .user-info td {
    color: #0f172a;
    }

    .user-info tr:last-child th,
    .user-info tr:last-child td {
    border-bottom: none;
    }

</style>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
                <h6 class="mb-0">List Users</h6>
            </div>
            <div class="card-body">
                <!-- Tabel -->
                <!-- Tombol Sync -->
                <button id="btnSyncUsers" class="btn btn-success btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i> Sync Users
                </button>
                <br /><br />
                <div class="table-responsive">
                    <table id="users-table" class="table table-sm table-hover table-striped nowrap" style="width:100%;cursor:pointer;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Has Access</th>
                                    <th>Role Code</th>
                                    <th>Username</th>
                                    
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Akses Merk</th>
                                    
                                    
                                </tr>
                            </thead>
                        </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6" id="akses-gudang-panel" hidden>
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
                <h6 class="mb-0">User Access</h6>
            </div>
            <div class="card-body">
               
                <table class="user-info">
                    <tbody>
                        <tr>
                        <th>ID</th>
                        <td id="card-id">USR001</td>
                        </tr>
                        <tr>
                        <th>Nama</th>
                        <td id="card-nama">Nama User</td>
                        </tr>
                        <tr>
                        <th>Email</th>
                        <td id="card-email">user@email.com</td>
                        </tr>
                        <tr>
                        <th>Akses Merk</th>
                        <td id="card-akses">Nike, Adidas</td>
                        </tr>
                        <tr>
                        <th>User Role Code</th>
                        <td id="card-role">Nike, Adidas</td>
                        </tr>
                    </tbody>
                </table>
                <br />

                
                <table id="menu-access-table" class="table table-hover display nowrap" width="100%"></table>
                <br />
                <table class="user-info">
                    <tbody>
                        <tr>
                        <th>Warehouses Access</th>
                        <td id="card-aksesgudang" style="white-space:normal; word-wrap:break-word; word-break:break-all; max-width:400px;"></td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    

<script>
$(document).ready(function () {
    var userId = '';


    // DataTable Utama: Users
    const usersTable = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        scrollX: true,
        ajax: {
            url: 'api/get_users.php',
            type: 'POST'
        },
        columns: [
            { data: 'id', title: 'ID' },
            { 
                data: 'has_access', 
                title: 'Has Access',
                className: '',
                render: function(data) {
                    if (data === "✅ Ada" || data === "❌ Belum") {
                        return data;
                    }
                    return data == 1 ? "✅ Ada" : "❌ Belum";
                }
            },
            { data: 'user_role_code', title: 'Role Code' },
            { data: 'username', title: 'Username' },
            { data: 'fullname', title: 'Nama' },
            { data: 'email', title: 'Email' },
            { data: 'akses_merk', title: 'Akses Merk' },
           
            
        ]
    });



    // Saat baris diklik
    $('#users-table tbody').on('click', 'tr', function () {
        const data = usersTable.row(this).data();
        if (!data) return;

        // Isi Card
        userId = data.id;
        $('#card-id').text(data.id);
        $('#card-nama').text(data.fullname);
        $('#card-email').text(data.email);
        $('#card-akses').text(data.akses_merk);
        $('#card-aksesgudang').text(data.akses_gudang);
        $('#card-role').text(data.user_role_code);
       

        $('#akses-gudang-panel').removeAttr('hidden');
        // Load Menu Access berdasarkan user_id
        loadMenuAccess(data.id);
    });

    // Fungsi load akses menu
    function loadMenuAccess(userId) {
        $.ajax({
            url: 'api/get_user_menu_access.php',
            type: 'POST',
            data: { user_id: userId },
            dataType: 'json',
            success: function (res) {
                if ($.fn.DataTable.isDataTable('#menu-access-table')) {
                    $('#menu-access-table').DataTable().destroy();
                }

                $('#menu-access-table').DataTable({
                    data: res.menus,
                    columns: [
                        { data: 'group', title: 'Group' },
                        { data: 'title', title: 'Title' },
                        {
                            data: 'id',
                            title: 'Access',
                            render: function (data, type, row) {
                                const checked = row.has_access ? 'checked' : '';
                                return `<input type="checkbox" class="menu-checkbox" data-menu-id="${data}" ${checked}>`;
                            },
                            orderable: false,
                            searchable: false
                        }
                    ],
                    paging: false,
                    searching: false,
                    info: false
                });
            }
        });
    }

    // Tangani klik checkbox akses menu
    $('#menu-access-table').on('change', '.menu-checkbox', function () {
        const menuId = $(this).data('menu-id');
        const isChecked = $(this).is(':checked');
        const userId = $('#card-id').text().trim(); // pastikan span #card-id diisi sebelumnya

        $.ajax({
            url: 'api/update_menu_access.php',
            type: 'POST',
            data: {
                user_id: userId,
                menu_id: menuId,
                has_access: isChecked ? 1 : 0
            },
            success: function (response) {
                console.log('Update success:', response);
            },
            error: function (xhr, status, error) {
                console.error('Update failed:', error);
                alert('Gagal update akses menu.');
            }
        });
    });

    // Event click tombol Sync
    $('#btnSyncUsers').on('click', function () {
    // kasih konfirmasi dulu
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Yakin ingin melakukan sinkronisasi user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sync!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
        // Jalankan proses sync
        $.ajax({
            url: 'api/sync_users.php',
            type: 'POST',
            beforeSend: function () {
            // kasih indikator loading
            Swal.fire({
                title: 'Sedang Sinkronisasi...',
                text: 'Mohon tunggu beberapa saat',
                allowOutsideClick: false,
                didOpen: () => {
                Swal.showLoading();
                }
            });
            },
            success: function (response) {
            Swal.fire({
                icon: 'success',
                title: 'Sinkronisasi Selesai',
                text: response,
                confirmButtonText: 'OK'
            });
            // kalau ada DataTable bisa refresh
            if (typeof usersTable !== 'undefined') {
                usersTable.ajax.reload();
            }
            },
            error: function (xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat sinkronisasi: ' + error
            });
            }
        });
        }
    });
    });


   




});






</script>
