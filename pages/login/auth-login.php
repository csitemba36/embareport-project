<?php
// Jika sudah login (cookie sudah ada), langsung arahkan ke index
if (isset($_COOKIE['username']) && isset($_COOKIE['email'])) {
    header("Location: ../../index");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | EMBA GROUP</title>
  <link rel="icon" type="image/png" href="../../assets/fav.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
		html, body {
		  height: 100%;
		  margin: 0;
		  padding: 0;
		}

		.background-radial-gradient {
		  background-color: hsl(218, 41%, 15%);
		  background-image: radial-gradient(650px circle at 0% 0%,
			  hsl(218, 41%, 35%) 15%,
			  hsl(218, 41%, 30%) 35%,
			  hsl(218, 41%, 20%) 75%,
			  hsl(218, 41%, 19%) 80%,
			  transparent 100%),
			radial-gradient(1250px circle at 100% 100%,
			  hsl(218, 41%, 45%) 15%,
			  hsl(218, 41%, 30%) 35%,
			  hsl(218, 41%, 20%) 75%,
			  hsl(218, 41%, 19%) 80%,
			  transparent 100%);
		  position: relative;
		  overflow: hidden;
		}

		#radius-shape-1,
		#radius-shape-2 {
		  background: radial-gradient(#44006b, #ad1fff);
		  position: absolute;
		  overflow: hidden;
		  z-index: 1;
		}

		#radius-shape-1 {
		  height: 220px;
		  width: 220px;
		  top: -60px;
		  left: -130px;
		  border-radius: 50%;
		}

		#radius-shape-2 {
		  border-radius: 38% 62% 63% 37% / 70% 33% 67% 30%;
		  bottom: -60px;
		  right: -110px;
		  width: 300px;
		  height: 300px;
		}

		.bg-glass {
		  background-color: hsla(0, 0%, 100%, 0.9) !important;
		  backdrop-filter: saturate(200%) blur(25px);
		  border-radius: 1rem;
		  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		}

  </style>
</head>
<body>

<section class="background-radial-gradient min-vh-100 d-flex align-items-center overflow-hidden">
  <div class="container px-4 py-5 px-md-5">
    <div class="row gx-lg-5 align-items-center">
      
      <!-- LEFT COLUMN -->
      <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start" style="z-index: 10">
        <h1 class="my-5 display-5 fw-bold ls-tight" style="color: hsl(218, 81%, 95%)">
          EMBA GROUP<br />
          <span style="color: hsl(218, 81%, 75%)">REPORT</span>
        </h1>
        <p class="mb-4 opacity-70" style="color: hsl(218, 81%, 85%)">
          Kelola pengiriman, pantau status penjualan, dan akses laporan real-time secara efisien.
        </p>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="col-lg-6 position-relative d-flex justify-content-center">
        <!-- Decorative shapes -->
        <div id="radius-shape-1" class="position-absolute rounded-circle shadow-5-strong"></div>
        <div id="radius-shape-2" class="position-absolute shadow-5-strong"></div>

        <!-- Login Card -->
        <div class="card bg-glass w-100" style="max-width: 400px; z-index: 2;">
		
          <div class="card-body px-4 py-5 px-md-5">
			 <div class="text-center mb-4">
				  <img src="../../assets/logo_emba_330x.avif" alt="Logo Perusahaan" class="img-fluid" style="max-width: 150px;">
				</div> <!-- Ganti path sesuai file logo -->
            <form id="loginForm" method="POST" action="">
			<div class="form-outline mb-4">
			<label class="form-label" for="username">Username</label>
			<input type="text" id="username" name="username" class="form-control" required />
			</div>

            <div class="form-outline mb-4 position-relative">
				  <label class="form-label" for="password">Password</label>
				  <input type="password" id="password" name="password" class="form-control pr-5" required />
				  <span class="toggle-password position-absolute" onclick="togglePassword()"
						  style="top: 73%; right: 15px; transform: translateY(-50%); cursor: pointer;">
					  <i id="toggle-icon" class="fas fa-eye"></i>
					</span>
			</div>

			<div class="form-outline mb-4">
				<label class="form-label" for="tahun">Generasi</label>
				<select id="tahun" name="tahun" class="form-select" required>
					<option value="">-- Pilih Tahun --</option>
					<option value="2024">2024</option>
					<option value="2025">2025</option>
					<option value="2026">2026</option>
				</select>
			</div>


			<button type="submit" class="btn btn-primary btn-block w-100 mb-4">
			Login
			</button>

              <?php if (isset($error_message)) { echo '<div class="alert alert-danger">' . $error_message . '</div>'; } ?>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
	function togglePassword() {
		const passwordInput = document.getElementById("password");
		const icon = document.getElementById("toggle-icon");

		if (passwordInput.type === "password") {
		  passwordInput.type = "text";
		  icon.classList.remove("fa-eye");
		  icon.classList.add("fa-eye-slash");
		} else {
		  passwordInput.type = "password";
		  icon.classList.remove("fa-eye-slash");
		  icon.classList.add("fa-eye");
		}
	}
  
	$('#loginForm').on('submit', function (e) {
		e.preventDefault();

		const username = $('#username').val().trim();
		const password = $('#password').val().trim();
		const tahun    = $('#tahun').val();

		// =========================
		// Validasi client-side
		// =========================
		if (!username || !password) {
			alert('Username dan password wajib diisi.');
			return;
		}

		if (!tahun) {
			alert('Silakan pilih tahun data.');
			return;
		}

		$.ajax({
			url: '../../api/exe_login.php',
			type: 'POST',
			dataType: 'json',
			data: {
				username: username,
				password: password,
				tahun: tahun
			},
			success: function (response) {
				if (response.status === 'success') {
					window.location.href = '../../index';
				} else if (response.status === 'wrong_password') {
					alert('Password salah.');
				} else if (response.status === 'invalid_year') {
					alert('Tahun data tidak valid.');
				} else {
					alert('Username tidak ditemukan.');
				}
			},
			error: function (xhr, status, error) {
				console.error(error);
				alert('Terjadi kesalahan saat proses login.');
			}
		});
	});

  
</script>