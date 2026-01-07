<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Copy Inventory Stocks</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      background: linear-gradient(to right, #e8f5f9, #d1f2eb);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <h3 class="mb-4 text-center">Duplikasi Kode Item ke Gudang Lain</h3>
          <form id="copyForm">
            <div class="mb-3">
              <label for="gudang_sumber" class="form-label">Gudang Sumber</label>
              <input type="text" class="form-control" name="gudang_sumber" id="gudang_sumber" required>
            </div>

            <div class="mb-3">
              <label for="gudang_target" class="form-label">Gudang Target</label>
              <input type="text" class="form-control" name="gudang_target" id="gudang_target" required>
            </div>

            <div class="mb-3">
              <label for="kode_merk" class="form-label">Kode Merk</label>
              <input type="text" class="form-control" name="kode_merk" id="kode_merk" value="D" required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg">Proses</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    $('#copyForm').on('submit', function(e) {
      e.preventDefault();

      Swal.fire({
        title: 'Memproses...',
        text: 'Sedang menyalin data, harap tunggu...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: '../api/copy_stock_exec.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
          Swal.close();
          let res = {};
          try {
            res = JSON.parse(response);
          } catch (e) {
            res = { success: false, message: 'Respon tidak valid dari server.' };
          }

          if (res.success) {
            Swal.fire('Sukses', res.message, 'success');
          } else {
            Swal.fire('Gagal', res.message, 'error');
          }
        },
        error: function() {
          Swal.close();
          Swal.fire('Error', 'Gagal menghubungi server.', 'error');
        }
      });
    });
  </script>

</body>
</html>
