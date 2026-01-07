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

        <!-- Select Brand -->

        <div class="col-auto">
        <select id="brandSelect" class="form-select form-select-sm" style="min-width: 160px;">
            <option value="" disabled selected>-- Pilih Merk --</option>
            <option value="emba_jeans">EMBA DENIM</option>
            <option value="bbg_twist">BBG TWIST</option>
        </select>
        </div>

        <!-- Select2 tambahan -->

        <div class="col-auto">
            <select id="buktiSelect" class="form-select form-select-sm" style="min-width:220px;"></select>
        </div>

        <!-- Tombol -->

        <div class="col-auto">
        <button id="btn-retrieve" type="button" class="btn btn-primary btn-sm" style="font-size: 0.75rem;">
            Retrieve
        </button>
        </div>

        </form>
</div>
</div>

<div class="card mb-2 shadow-sm" style="height: calc(100vh - 120px);">
  <div class="card-body position-relative p-0">
    <div id="pdfLoading"
      class="position-absolute top-50 start-50 translate-middle text-center"
      style="display:none; z-index:10;">
      <div class="spinner-border text-primary" role="status"></div>
      <div class="mt-2 small fw-bold text-secondary">Memuat PDF...</div>
    </div>

    <iframe id="pdfFrame"
      src="about:blank"
      style="border:0; width:100%; height:100%;"></iframe>
  </div>
</div>



<script>
$(document).ready(function() {
  // Inisialisasi Select2 tapi disable dulu
  $('#buktiSelect').select2({
    placeholder: 'Pilih Bukti ID',
    disabled: true,
  });

  // Saat brand dipilih
  $('#brandSelect').on('change', function() {
    const brand = $(this).val();

    // Aktifkan Select2 dan load data berdasarkan brand
    $('#buktiSelect').prop('disabled', false).select2({
      placeholder: 'Cari Bukti ID...',
      ajax: {
        url: 'api/powerone-api-service/db-get-no-sj-putus.php',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return {
            brand: brand,
            q: params.term || ''
          };
        },
        processResults: function(data) {
          return data;
        },
        cache: true
      },
      minimumInputLength: 1
    });
  });

 // Klik Retrieve -> tampilkan PDF dengan indikator loading
 $('#btn-retrieve').on('click', function() {
    const brand = $('#brandSelect').val();
    const bukti = $('#buktiSelect').val();

    if (!brand || !bukti) {
      alert('Pilih merk dan bukti ID terlebih dahulu!');
      return;
    }

    const pdfUrl = `pages/page_pdf/faktur-sj-putus.php?brand=${brand}&bukti_id=${bukti}`;
    $('#pdfLoading').show();
    $('#pdfFrame').attr('src', pdfUrl + '#view=FitH');

    // Hilangkan indikator setelah iframe selesai dimuat
    $('#pdfFrame').off('load').on('load', function() {
      $('#pdfLoading').fadeOut();
    });
  });
});

    function resizePDF() {
    const iframe = document.getElementById('pdfFrame');
    const offsetTop = iframe.getBoundingClientRect().top;
    iframe.style.height = (window.innerHeight - offsetTop - 20) + 'px';
    }

    window.addEventListener('load', resizePDF);
    window.addEventListener('resize', resizePDF);

</script>
