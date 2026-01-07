<?php
// Cek apakah cookie sudah ada (misalnya: username dan email)
if (!isset($_COOKIE['username']) || !isset($_COOKIE['email'])) {
    // Belum login, redirect ke halaman login
    header("Location: pages/login/auth-login");
    exit;
}

// Jika cookie ada → lanjut tampilkan isi index
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel</title>
    <link rel="icon" type="image/png" href="assets/fav.png" />
	

	<!-- CSS Libraries -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
	<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
	<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet"/>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
	<!-- jQuery & jQuery UI -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
	<!-- PivotTable -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.css">
	<script src="https://cdn.jsdelivr.net/npm/pivottable@2.23.0/dist/pivot.min.js"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>



  <style>
    body {
	  background-color: #f4f8ff;
	  font-family: "Segoe UI", sans-serif;
	  font-size: 12px; 
	}

    .sidebar {
	  font-size: 13px;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      width: 230px;
      background: linear-gradient(to bottom, #e5f0ff, #b0d4ff);
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      z-index: 1040;
      transition: transform 0.3s ease;
	  overflow-y: auto;
      height: 100vh;
    }

    .sidebar .brand {
      text-align: center;
      padding: 13px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .sidebar .brand span {
      display: block;
      font-weight: bold;
      color: #0a2c5d;
      margin-top: 5px;
    }

    .sidebar .nav-link {
      color: #0a2c5d;
      padding: 8px 16px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color:rgb(169, 207, 253);
      color: #003366;
      border-left: 4px solid #0078d7;
    }

    .navbar {
      margin-left: 230px;
      background-color: #0078d7;
      z-index: 1041;
    }

    .navbar-brand, .navbar .dropdown-toggle {
      color: white;
      font-weight: 600;
    }

    .navbar .dropdown-menu {
      right: 0;
      left: auto;
    }

    .content {
      margin-left: 230px;
      padding: 20px;
    }

    /* Sidebar collapsed (desktop) */
    body.sidebar-collapsed .sidebar {
      transform: translateX(-100%);
    }

    body.sidebar-collapsed .content,
    body.sidebar-collapsed .navbar {
      margin-left: 0 !important;
      width: 100% !important;
    }

    /* Sidebar hidden (mobile) */
    @media (max-width: 991.98px) {
      .sidebar {
        transform: translateX(-100%);
      }

      body.sidebar-open .sidebar {
        transform: translateX(0);
      }

      .navbar, .content {
        margin-left: 0 !important;
      }
    }
  </style>
  <style>
  /* Bubble Chat */
  .online-bubble {
    position: fixed;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    background: #1877f2;
    color: #fff;
    padding: 6px 10px;                /* lebih kecil */
    border-radius: 20px 0 0 20px;
    font-size: 12px;                  /* font diperkecil */
    font-weight: 500;
    display: flex;
    align-items: center;
    box-shadow: -4px 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    cursor: pointer;
    white-space: nowrap;

    width: 48px;                      /* versi kecil lebih ramping */
    overflow: hidden;
    transition: width 0.25s ease, padding 0.25s ease;
}

/* expanded */
.online-bubble.expanded {
    width: 170px;
    padding: 6px 12px;                /* sedikit lebih lega saat expand */
}

/* hide/show */
.expanded-content {
    display: none;
    margin-left: 6px;
}

.online-bubble.expanded .expanded-content {
    display: inline-flex;
}

.online-bubble.expanded .compact {
    display: none;
}

/* Dot indikator online */
.online-bubble .dot {
    width: 8px;
    height: 8px;
    background: #00ff4c;
    border-radius: 50%;
    margin-right: 6px;
    box-shadow: 0 0 5px rgba(0,255,0,0.5);

    transform: translateY(-1px);   /* naikkan sedikit */
}

/* angka default */
.compact {
    font-size: 13px;
    font-weight: 600;
}




 .online-list {
    position: fixed;
    right: 65px;
    top: 50%;
    transform: translateY(-50%);
    width: 290px;
    background: #fff;
    color: #333;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.2);
    z-index: 99999;
    display: none;
}

.close-online {
    position: absolute;
    top: 6px;
    right: 8px;
    background: #eee;
    border: none;
    font-size: 16px;
    font-weight: bold;
    width: 22px;
    height: 22px;
    padding: 0;
    line-height: 20px;
    border-radius: 50%;
    cursor: pointer;
    transition: 0.2s;
	color:rgb(215, 0, 0);
}

.close-online:hover {
    background: #ddd;
    transform: scale(1.1);
}


  .online-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 400px;
    overflow-y: auto;
  }

  .online-list ul li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
    font-size: 13px;
    color: #333;
  }

  .online-list ul li:last-child {
    border-bottom: none;
  }
  .dot {
	  height: 10px;
	  width: 10px;
	  background-color: #4CAF50; /* Warna hijau */
	  border-radius: 50%;
	  display: inline-block;
	  margin-right: 5px;
	}
	
	.rating-modal {
	  display: none;
	  position: fixed;
	  top: 50%;
	  left: 50%;
	  transform: translate(-50%, -50%);
	  background: linear-gradient(to bottom, #e5f0ff, #b0d4ff); /* Sama seperti sidebar */
	  width: 550px;
	  box-shadow: 0 0 10px rgba(0,0,0,0.2);
	  padding: 20px;
	  border-radius: 8px;
	  z-index: 9999;
	}
	.rating-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .rating-header h5 {
      margin: 0;
    }
    .close-btn {
      cursor: pointer;
      font-size: 20px;
    }
    .rating-stars {
      text-align: center;
      margin: 15px 0;
    }
    .rating-stars span {
      font-size: 2rem;
      cursor: pointer;
    }

 
    .btn-warning {
      background-color: gold;
      color: black;
    }
	
	.modal-backdrop {
	  display: none;
	  position: fixed;
	  top: 0;
	  left: 0;
	  width: 100%;
	  height: 100%;
	  background: rgba(0, 0, 0, 0.5); /* semi-transparan */
	  z-index: 9998; /* pastikan lebih rendah dari modal */
	}

	.btn-rating {
	background: linear-gradient(135deg, #00d2ff, #3a7bd5);
	color: #fff;
	border: none;
	border-radius: 30px;
	padding: 6px 14px;
	font-weight: 500;
	font-size: 0.80rem;
	box-shadow: 0 4px 10px rgba(0,210,255,0.35);
	transition: all 0.25s ease-in-out;
}


	.btn-rating i {
	color: #fff;
	font-size: 1rem;
	}

	.btn-rating:hover {
	background: linear-gradient(135deg, #ffdd44, #ffaa33);
	transform: translateY(-2px) scale(1.05);
	box-shadow: 0 6px 12px rgba(0,0,0,0.2);
	}

	.btn-rating:active {
	transform: scale(0.95);
	}

	
</style>

</head>
<body>
  <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
		<div class="brand">
			<span>EMBA GROUP</span>
		</div>
		
		<!-- Grup: Dashboard -->
		<?php
			require_once('config/connect_db.php');

			// Ambil user_id dari cookie
			$user_id = $_COOKIE['id'] ?? null;

			if (!$user_id) {
				echo "User ID tidak ditemukan di cookie.";
				exit;
			}

			// Ambil menu_id yang diperbolehkan untuk user_id ini
			$menu_ids = [];
			$accessQuery = "SELECT menu_id FROM menu_access WHERE user_id = ?";
			$stmt = $mysqli->prepare($accessQuery);
			$stmt->bind_param("i", $user_id);
			$stmt->execute();
			$result = $stmt->get_result();

			while ($row = $result->fetch_assoc()) {
				$menu_ids[] = $row['menu_id'];
			}

			if (empty($menu_ids)) {
				echo "Tidak ada akses menu untuk user ini.";
				exit;
			}

			// Buat placeholder untuk IN clause
			$placeholders = implode(',', array_fill(0, count($menu_ids), '?'));

			// Ambil menu berdasarkan ID
			$menuQuery = "SELECT `group`, `title`, `url`, `icon_class`,`urut` FROM menu WHERE id IN ($placeholders) ORDER BY urut ASC";
			$stmt = $mysqli->prepare($menuQuery);

			// Bind semua menu_id
			$stmt->bind_param(str_repeat('i', count($menu_ids)), ...$menu_ids);
			$stmt->execute();
			$result = $stmt->get_result();

			$menus = [];

			while ($row = $result->fetch_assoc()) {
				$group = $row['group'];
				$menus[$group][] = $row;
			}
		?>

		<?php foreach ($menus as $group => $items): ?>
			<div class="menu-group text-muted text-uppercase small fw-bold mt-3 mb-2 ps-2">
				<?= htmlspecialchars($group) ?>
				</div>
			<?php foreach ($items as $item): 
				$url = $item['url'] ? $item['url'] : '#';
				$titleSlug = strtolower(str_replace(' ', '-', $item['title']));
			?>
					<a href="#" class="nav-link menu-item"
					data-page="<?= htmlspecialchars($url) ?>">
						<i class="<?= htmlspecialchars($item['icon_class']) ?>"></i>
						<?= htmlspecialchars($item['title']) ?><span id="badge-<?= htmlspecialchars($item['urut']) ?>" class="badge bg-danger ms-2"></span>
					</a>

			<?php endforeach; ?>
		<?php endforeach; ?>


		
	</div>

  	<!-- Navbar -->
	<nav class="navbar navbar-expand-lg navbar-dark px-3">
	<button class="btn text-white d-lg-none me-2" id="toggleSidebar" type="button">
		<i class="bi bi-list" style="font-size: 1.5rem;"></i>
	</button>
	<a class="navbar-brand fs-6" href="#">Admin Panel</a>

	<div class="ms-auto d-flex align-items-center">
		<!-- Tombol Rating -->
		

		<!-- Dropdown User -->
		<div class="dropdown">
		<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
			<i class="bi bi-person-circle"></i>
			<?php if (isset($_COOKIE['fullname'])) { echo htmlspecialchars($_COOKIE['fullname']); } else { echo "Guest"; } ?>
		</a>
		<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
			<li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profil</a></li>
			<li><hr class="dropdown-divider"></li>
			<li><a class="dropdown-item" href="#" id="btnLogout"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
		</ul>
		</div>
	</div>
	</nav>

  <!-- Content -->
<?php
require_once('./config/connect_db.php');
// Ambil cookie dan proses
$userrole = isset($_COOKIE['userrolecode']) ? htmlspecialchars($_COOKIE['userrolecode']) : 'UNKNOWN';
$merk_raw = isset($_COOKIE['aksesmerk']) ? urldecode($_COOKIE['aksesmerk']) : 'ALL';
$merk_display = 'SEMUA MERK';
$merk_nama = [];

if (strtoupper(trim($merk_raw)) !== 'ALL') {
    // 1. Pecah berdasarkan ;
    $kode_array = explode(';', $merk_raw);
    
    // 2. Bersihkan nilai
    $kode_array = array_map('trim', $kode_array);
    
    // 3. Buat format string untuk IN (...)
    $in_list = "'" . implode("','", array_map('addslashes', $kode_array)) . "'";

    // 4. Query ke tabel DepartmentBrands
    $sqlMerk = "SELECT Nama FROM DepartmentBrands WHERE Kode IN ($in_list)";
    $resultMerk = odbc_exec($conn, $sqlMerk);
    
    // 5. Ambil nama-namanya
    while (odbc_fetch_row($resultMerk)) {
        $merk_nama[] = odbc_result($resultMerk, 'Nama');
    }

    // 6. Gabungkan nama merk
    $merk_display = implode(', ', $merk_nama);
}
?>
<div class="content" id="main-content">
	<!-- Tambahkan alert di atas konten utama -->
		<div class="container mt-4">
			<div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
				<i class="bi bi-info-circle-fill me-2 fs-5"></i>
				<div>
					<strong>Info!</strong> Anda login sebagai <strong><?= $userrole ?></strong> dengan akses merk: <strong><?= $merk_display ?></strong>.
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		</div>
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-2">Selamat Datang di <span class="text-primary">EMBA Group Dashboard</span></h3>
            <p class="text-muted fs-5">Pantau aktivitas gudang, sales, dan transaksi dengan lebih cepat dan mudah.</p>
        </div>
		

        <div class="row g-4">
            <!-- Card 1 -->
            <div class="col-md-4">
                <div class="card dashboard-card shadow-sm border-0 rounded-4 h-100 text-center p-4">
                    <div class="card-body">
                        <div class="icon-container mb-3">
                            <i class="bi bi-box-seam fs-1 text-primary"></i>
                        </div>
                        <h5 class="card-title">Stock Request</h5>
                        <p class="card-text text-muted">Kelola permintaan stok antar gudang.</p>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-4">
                <div class="card dashboard-card shadow-sm border-0 rounded-4 h-100 text-center p-4 position-relative">
                    <div class="card-body">
                        <div class="icon-container mb-3">
                            <i class="bi bi-clock-history fs-1 text-warning"></i>
                        </div>
                        <h5 class="card-title">Pending SR</h5>
                        <p class="card-text text-muted">Lihat permintaan stok yang belum diproses.</p>
                        
                        <!-- Badge count -->
                        <span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle p-2" id="badge-pending">
                            0
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-md-4">
                <div class="card dashboard-card shadow-sm border-0 rounded-4 h-100 text-center p-4">
                    <div class="card-body">
                        <div class="icon-container mb-3">
                            <i class="bi bi-receipt fs-1 text-success"></i>
                        </div>
                        <h5 class="card-title">Sales Invoice</h5>
                        <p class="card-text text-muted">Pantau invoice penjualan dengan lebih cepat.</p>
                    </div>
                </div>
            </div>

			<div class="col-md-12">
				<button class="btn-rating w-100 me-3 d-flex align-items-center justify-content-center text-center"
						id="btnRating" onclick="openModal()">
					<i class="bi bi-star-fill me-1"></i> Beri Rating
				</button>

            </div>

        </div>
    </div>
</div>

<!-- Bubble Chat -->
<div class="online-bubble" id="onlineBubble">
  
  <!-- Mode kecil (default) -->
  <span class="compact" id="compactUser">
  <span class="dot"></span><span id="total_user">0</span>
  </span>

  <!-- Mode full saat expand -->
  <span class="expanded-content" id="expandedContent">
    User Online Klik Disini
  </span>

</div>


<div class="online-list" id="onlineList">
  <button class="close-online" onclick="closeOnlineList()">×</button>
  <h6>User Online Hari Ini</h6>
  <ul id="listUsers">
    <li>Loading...</li>
  </ul>
</div>


<!-- Modal -->
<div class="modal-backdrop" id="modalBackdrop"></div>
	<div class="rating-modal" id="ratingModal">
	  <div class="rating-header">
		<h5>Beri Rating & Ulasan</h5>
		<!--<span class="close-btn" onclick="closeModal()">×</span>-->
	  </div>
	  <p style="margin-top: 10px; text-align: center; font-size: 14px;">
		Apakah aplikasi ini membantu dalam menunjang kinerja Anda? Berikan rating dan ulasan Anda di bawah ini.
	  </p>
	  <div class="rating-stars">
		<span class="star" data-value="1">☆</span>
		<span class="star" data-value="2">☆</span>
		<span class="star" data-value="3">☆</span>
		<span class="star" data-value="4">☆</span>
		<span class="star" data-value="5">☆</span>
	  </div>
	  <textarea id="comment" class="form-control" rows="3" placeholder="Berikan komentar..."></textarea>
	  <input type="hidden" id="ratingValue" value="0"><br />
	  <div style="text-align: right;">
		<button class="btn btn-primary" onclick="submitRating()">Submit</button>
	  </div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="ratingsModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ratingModalLabel">Data Rating</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
	  
	 
	  <center><canvas id="ratingDonutChart" class="center" style="width: 350px; height: 350px;"></canvas></center><br />
      <div class="modal-body">
		  <div id="reviewContainer" class="list-group" style="max-height: 400px; overflow-y: auto;"></div>
		</div>
	 
    </div>
  </div>
</div>



  <!-- JS Libraries
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
	    $(document).keydown(function(e) {
			
		  if (e.ctrlKey && e.key === 'Enter') {

			$.ajax({
				  url: 'api/get-rate.php',
				  method: 'GET',
				  dataType: 'json',
				  success: function(data) {
					let content = '';
					data.forEach(function(item) {
					  content +=
						'<div class="list-group-item mb-2">' +
						  '<div class="d-flex justify-content-between align-items-center">' +
							'<strong>' + item.user_id + '</strong>' +
							'<small class="text-muted">' + item.created_at + '</small>' +
						  '</div>' +
						  '<div class="mb-1">' + getStars(item.rating) + '</div>' +
						  '<div>' + item.comment + '</div>' +
						'</div>';
					});
					$('#reviewContainer').html(content);
					$('#ratingsModal').modal('show');
				  },
				  error: function() {
					alert('Gagal mengambil data rating');
				  }
				});

		  }
		});
  
		function getStars(rating) {
		  rating = Math.round(rating);
		  let stars = '';
		  for (let i = 1; i <= 5; i++) {
			stars += i <= rating
			  ? '<span style="color:orange;">&#9733;</span>'
			  : '<span style="color:lightgray;">&#9733;</span>';
		  }
		  return stars;
		}

	  function openModal() {
		  document.getElementById('ratingModal').style.display = 'block';
		  document.getElementById('modalBackdrop').style.display = 'block';
		}

		function closeModal() {
		  document.getElementById('ratingModal').style.display = 'none';
		  document.getElementById('modalBackdrop').style.display = 'none';
		}

	  document.querySelectorAll('.star').forEach(star => {
		star.addEventListener('click', function () {
		  let value = this.getAttribute('data-value');
		  document.getElementById('ratingValue').value = value;
		  document.querySelectorAll('.star').forEach(s => {
			s.textContent = s.getAttribute('data-value') <= value ? '★' : '☆';
		  });
		});
	  });

	  function submitRating() {
			const rating = $('#ratingValue').val();
			const comment = $('#comment').val().trim();

			// Validasi input
			if (!rating || rating === '0') {
				alert('Isikan Rating dengan Klik Bintang');
				return;
			}

			if (!comment) {
				alert('Isikan Saran dan kritik pada form.');
				return;
			}

			if (comment.length < 25) {
				alert('Saran dan kritik minimal 25 karakter.');
				return;
			}

			// Kirim data ke server
			$.ajax({
				url: 'api/save-rate.php',
				type: 'POST',
				data: {
				rating: rating,
				comment: comment
				},
				success: function(response) {
				Swal.fire({
					icon: 'success',
					title: 'Berhasil!',
					text: response,
					confirmButtonText: 'OK'
				});
				closeModal();
				},
				error: function(xhr, status, error) {
				console.error(error);
				alert('Gagal menyimpan rating.');
				}
			});
			}


	</script>

  <!-- Custom Script -->
  <script>
	  window.onload = function() {
		//document.documentElement.style.zoom = "80%";
	};
	</script>

  <script>
    $(document).ready(function () {
		checkrate();
	  // Bubble toggle
	  $('#onlineBubble').on('click', function () {
		$('#onlineList').fadeToggle();
	  });
	  
	  //load jumlah user login
	  loadOnlineUsers();
	  loadCountUserLogin();
	  
	  
      const isSmallDevice = () => window.matchMedia("(max-width: 991.98px)").matches;

        // Load konten halaman
		$('.menu-item').on('click', function (e) {
		  e.preventDefault();

		  // Aktifkan menu yang diklik
		  $('.menu-item').removeClass('active');
		  $(this).addClass('active');

		  // Ambil nama page
		  const page = $(this).data('page');
		  const menuName = $(this).text().trim(); // Ambil nama menu yang diklik

		  // Load halaman konten
		  $('#main-content').load('pages/' + page);

		  // Ganti judul navbar
		  $('.navbar-brand').text(menuName);

		  // Tutup sidebar di mobile
		  if (isSmallDevice()) {
			$('body').removeClass('sidebar-open');
		  }
		});

      // Toggle sidebar
      $('#toggleSidebar').on('click', function () {
        if (isSmallDevice()) {
          $('body').toggleClass('sidebar-open');
        } else {
          $('body').toggleClass('sidebar-collapsed');
        }
      });

      // Tutup sidebar saat pertama load jika mobile
      if (isSmallDevice()) {
        $('body').removeClass('sidebar-open sidebar-collapsed');
      }

      // Logout alert
      $('#logout').on('click', function (e) {
        e.preventDefault();
        window.location.href = "logout.php";
      });
	  
	  loadBadgePending();
	  loadBadgeDelivery()
    });
	
	function loadBadgePending() {
		$('#badge-sr-pending').html('<i class="fas fa-spinner fa-spin"></i>'); // Munculin spinner saat loading

		$.ajax({
			url: 'api/count-stock-request-pending.php',
			method: 'GET',
			dataType: 'json',
			success: function(response) {
				$('#badge-2').text(response.total); // Tampilkan jumlah
				$('#badge-pending').text(response.total);
			},
			error: function() {
				$('#badge-2').text('Err'); // Kalau error
			}
		});
	}
	
	function loadBadgeDelivery() {
		$('#badge-sr-pending').html('<i class="fas fa-spinner fa-spin"></i>'); // Munculin spinner saat loading

		$.ajax({
			url: 'api/count-stock-request-delivery.php',
			method: 'GET',
			dataType: 'json',
			success: function(response) {
				$('#badge-3').text(response.total); // Tampilkan jumlah
				//$('#badge-delivery').text(response.total);
			},
			error: function() {
				//$('#badge-sr-pending').text('Err'); // Kalau error
			}
		});
	}
	
	function loadCountUserLogin() {
		//$('#badge-sr-pending').html('<i class="fas fa-spinner fa-spin"></i>'); // Munculin spinner saat loading

		$.ajax({
			url: 'api/get-count-user-login.php',
			method: 'GET',
			dataType: 'json',
			success: function(response) {
				$('#total_user').text(response[0].total);
				//$('#badge-delivery').text(response.total);
			},
			error: function() {
				//$('#badge-sr-pending').text('Err'); // Kalau error
			}
		});
	}
	
	// Load user online
	function loadOnlineUsers() {
	  $.ajax({
		url: 'api/get-user-login.php',  // <-- ganti ke API kamu
		method: 'GET',
		dataType: 'json',
		success: function (response) {
		  let html = '';
		  if (response.length > 0) {
			response.forEach(user => {
			   html += '<li><span class="dot"></span> ' + user.fullname + 
						'<span style="float: right; color: gray; font-size: 0.9em;">' + user.login_time + '</span></li>';

			});
		  } else {
			html = '<li>Tidak ada yang online</li>';
		  }
		  $('#listUsers').html(html);
		},
		error: function () {
		  $('#listUsers').html('<li>Gagal load user</li>');
		}
	  });
	}
	
	function checkrate(){
		$.ajax({
		  url: 'api/check-rate.php',
		  method: 'GET',
		  dataType: 'json',
		  success: function(response) {
			if (response.hasRated) {
			  // User sudah pernah isi rating, jangan tampilkan modal
			  console.log("User sudah memberikan rating.");
			} else {
			  // User belum isi rating, tampilkan modal
			  openModal();
			}
		  },
		  error: function() {
			// Kalau error cek rating, kamu bisa tetap buka modal atau tidak
			openModal();
		  }
		});
	}
  </script>
  
  <script>
	  $(document).ready(function () {
		$.getJSON('api/get-feedback-summary.php', function(data) {
		  const rating = data.rata_rating;

		  new Chart(document.getElementById('ratingDonutChart'), {
			type: 'doughnut',
			data: {
			  labels: ['Puas', 'Kurang Puas'],
			  datasets: [{
				data: [rating, 5 - rating],
				backgroundColor: ['#0078d7', '#8499aa']
			  }]
			},
			options: {
				responsive: false,
				maintainAspectRatio: false,
			  plugins: {
				title: {
				  display: true,
				  text: `Rata-Rata Rating: ${rating}`
				}
			  }
			}
		  });
		});
	  });
	  
	   $('#btnLogout').on('click', function () {
            $.ajax({
                url: 'api/exe_logout.php',
                type: 'POST',
                success: function (res) {
                    let result;
                    try {
                        result = JSON.parse(res);
                    } catch (e) {
                        alert("Logout gagal: response tidak valid.");
                        return;
                    }

                    if (result.status === "success") {
                        // Redirect ke halaman login
                        window.location.href = "pages/login/auth-login";
                    } else {
                        alert("Logout gagal.");
                    }
                },
                error: function () {
                    alert('Terjadi kesalahan saat logout.');
                }
            });
        });

		const bubble = document.getElementById("onlineBubble");

		// buka saat hover
		bubble.addEventListener("mouseenter", function() {
			bubble.classList.add("expanded");
		});

		// tutup saat mouse keluar
		bubble.addEventListener("mouseleave", function() {
			bubble.classList.remove("expanded");
		});

		// buka/tutup saat di-klik
		bubble.addEventListener("click", function() {
			bubble.classList.toggle("expanded");
		});

		function closeOnlineList() {
			document.getElementById("onlineList").style.display = "none";
		}

   </script>
  

</body>
</html>
