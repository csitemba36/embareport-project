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
	  padding: 2px 6px; /* Perkecil tombol */
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
</style>
<div class="card mb-4 shadow-sm" hidden>
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
		<h5 class="mb-0">Filter Data</h5>
	</div>
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <!-- Tanggal Mulai -->
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Mulai</label>
                <input type="date" id="tanggal_awal" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>

            <!-- Tanggal Selesai -->
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Selesai</label>
                <input type="date" id="tanggal_akhir" class="form-control" value="<?= date('Y-m-t') ?>">
            </div>

            <!-- Gudang Asal -->
            <div class="col-md-3">
                <label for="kode_gudang" class="form-label">Gudang Asal</label>
                <select id="kode_gudang" class="form-control">
                    <option value="">Pilih Gudang Asal</option>
                </select>
            </div>

            <!-- Gudang Target -->
            <div class="col-md-3">
                <label for="gudang_target" class="form-label">Gudang Target</label>
                <select id="gudang_target" class="form-control">
                    <option value="">Pilih Gudang Target</option>
                </select>
            </div>

            <!-- IsClosed Filter -->
            <div class="col-md-3">
                <label for="isClosedFilter" class="form-label">Status</label>
                <select id="isClosedFilter" class="form-control">
					<option value="2">Pending</option>
                </select>
            </div>

            <!-- Action Button -->
            <div class="col-md-3 align-self-end">
                <button type="button" id="filterButton" class="btn btn-primary w-100">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#e0eeff;">
        <h6 class="mb-0">Data Stock Request</h6> 
		    <div class="ms-auto">
				<button class="btn btn-sm btn-success" 
						id="DTexportExcelPerBrand" 
						style="font-size:0.8rem; line-height:1.2;"
						data-bs-toggle="modal" 
						data-bs-target="#modalBrandDownload">
					Download Resume SR Pending G0001
				</button>
			</div>
    </div>
    <div class="card-body small-table">
		<!-- Tabel -->
		<div class="table-responsive">
			<table id="SRTable" class="table table-bordered table-sm table-hover table-striped nowrap" style="width:100%">
				<thead class="table-primary">
					<tr>
						<th>Action</th>
						<th>Brand</th>
						<th>No Request</th>
						<th>Tgl Request</th>
						<th>Tgl Entry</th>
						<th>Gudang Sumber</th>
						<th>Gudang Target</th>
						<th>Alamat Gudang Target</th>
						<th>Parent Transaction</th>
						<!--<th>Keterangan</th>-->
						<th>Status</th>
					</tr>
				</thead>
			</table>
		</div>
    </div>
</div>

<div class="modal fade bg-dark" id="modalBrandDownload" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Pilih Brand (SR Pending)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <label class="form-label">Brand</label>
        <select id="selectBrand" class="form-select">
            <option value="">-- Pilih Brand --</option>
        </select>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Batal
        </button>

        <button type="button" class="btn btn-success" id="btnDownloadBrand">
          Download
        </button>
      </div>

    </div>
  </div>
</div>




<script>

	$('#btnDownloadBrand').on('click', function () {
		let brand = $('#selectBrand').val();

		if (!brand) {
			alert("Pilih brand terlebih dahulu!");
			return;
		}

		// Redirect ke file PHP untuk generate Excel
		window.location.href = 'api/sr-pending-download-brand.php?brand=' + encodeURIComponent(brand);
	});

	$(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: '-- Choose --',
            allowClear: true
        });

        $('#brandFilter').select2({
            placeholder: '-- Choose --',
            allowClear: true,
            ajax: {
                url: 'api/get_brand.php', // ganti dengan path yang benar
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term // kirim keyword pencarian ke server
                    };
                },
                processResults: function (data) {
                    return {
                        results: data // hasil sudah dalam format id & text
                    };
                },
                cache: true
            }
        });
		
		 $.ajax({
            url: 'api/get_brand.php',
            dataType: 'json',
            success: function (data) {
                $('#selectBrand').empty().append('<option value="">-- Pilih Brand --</option>');
                data.forEach(item => {
                    $('#selectBrand').append(`<option value="${item.id}">${item.text}</option>`);
                });
            }
        });
	});
	
	$(document).ready(function() {
		$('#openStockRequestModal').on('click', function() {
			$('#stockRequestModal').modal('show');
			//loadStockRequestData();
		});
  
		$("#kode_gudang").select2({
			placeholder: "Pilih Kode Gudang",
			allowClear: true,
			ajax: {
				url: "api/get_warehouses.php",
				type: "GET",
				dataType: "json",
				delay: 250,
				data: function (params) {
					return {
						term: params.term // Kirim teks pencarian ke server
					};
				},
				processResults: function (data) {
					return { results: data };
				},
				cache: true
			}
		});
		
		$("#gudang_target").select2({
			placeholder: "Pilih Kode Gudang",
			allowClear: true,
			ajax: {
				url: "api/get_warehouses.php",
				type: "GET",
				dataType: "json",
				delay: 250,
				data: function (params) {
					return {
						term: params.term // Kirim teks pencarian ke server
					};
				},
				processResults: function (data) {
					return { results: data };
				},
				cache: true
			}
		});
		    
	});
	
	
	// Kalau klik button filter
	$(document).ready(function() {
		// Ambil default filter dari form
		const tanggalAwal = $('#tanggal_awal').val();
		const tanggalAkhir = $('#tanggal_akhir').val();
		const kodeGudang = $('#kode_gudang').val();
		const gudangTarget = $('#gudang_target').val();
		const isClosed = $('#isClosedFilter').val();

		// Load pertama kali
		loadData(tanggalAwal, tanggalAkhir, kodeGudang, gudangTarget, isClosed);

		// Kalau klik tombol filter
		$('#filterButton').on('click', function() {
			const tanggalAwal = $('#tanggal_awal').val();
			const tanggalAkhir = $('#tanggal_akhir').val();
			const kodeGudang = $('#kode_gudang').val();
			const gudangTarget = $('#gudang_target').val();
			const isClosed = $('#isClosedFilter').val();

			loadData(tanggalAwal, tanggalAkhir, kodeGudang, gudangTarget, isClosed);
		});
		
		$('#SRTable tbody').on('click', '.expand-btn', function() {
			var tr = $(this).closest('tr');
			var row = table.row(tr);

			if (row.child.isShown()) {
				// Child lagi terbuka, kita tutup
				row.child.hide();
				tr.removeClass('shown');
				$(this).text('Show Detail');
			} else {
				var noRequest = row.data().NoRequest;
 
				// Panggil API detail
				$.ajax({
					url: 'api/get_detail_sku.php', // ganti dengan nama file PHP kamu
					type: 'GET',
					data: { noRequest: noRequest },
					success: function(response) {
						row.child(response).show();
						tr.addClass('shown');
						$(tr).find('.expand-btn').text('Hide Detail');

					},
					error: function() {
						alert('Gagal mengambil detail');
					}
				});
			}
		});
		
		/*$('#SRTable tbody').on('click', '.print-btn', function() {
			var tr = $(this).closest('tr');
			var row = table.row(tr);
			var noRequest = row.data().NoRequest;

			// NOTE ALERT pakai SweetAlert
			var noteText = "Print out ini hanya digunakan untuk menyiapkan barang sesuai dengan kode item dan qty yang tertera di SR.<br><br>" +
				"Pastikan Qty barang yang dikirim sesuai dengan Qty SR (jika ada perbedaan mohon informasikan ke Alokator untuk direvisi).<br><br>" +
				"Setelah barang siap lakukan transaksi di aplikasi MASERP di menu Persediaan Barang -> Transaksi persediaan -> Klik tambah -> Masukkan No. Stock Request ...... -> klik simpan.";

			Swal.fire({
				title: 'Perhatian!',
				html: noteText + "<br><br>Apakah Anda yakin ingin mencetak Excel sekarang?",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, Cetak Sekarang!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					// Panggil API detail untuk export
					$.ajax({
						url: 'api/get_detail_sku.php',
						type: 'GET',
						data: { noRequest: noRequest },
						success: function(response) {
							exportToExcel(row.data(), response);
						},
						error: function() {
							Swal.fire('Error', 'Gagal mengambil detail', 'error');
						}
					});
				} else {
					Swal.fire('Dibatalkan', 'Proses cetak Excel dibatalkan.', 'info');
				}
			});
		});*/
		$('#SRTable tbody').on('click', '.print-btn', function() {
			var tr = $(this).closest('tr');
			var row = table.row(tr);
			var noRequest = row.data().NoRequest;

			// NOTE ALERT pakai SweetAlert
			var noteText = "Print out ini hanya digunakan untuk menyiapkan barang sesuai dengan kode item dan qty yang tertera di SR.<br><br>" +
				"Pastikan Qty barang yang dikirim sesuai dengan Qty SR (jika ada perbedaan mohon informasikan ke Alokator untuk direvisi).<br><br>" +
				"Setelah barang siap lakukan transaksi di aplikasi MASERP di menu Persediaan Barang -> Transaksi persediaan -> Klik tambah -> Masukkan No. Stock Request ...... -> klik simpan.";

			Swal.fire({
				title: 'Perhatian!',
				html: noteText + "<br><br>Apakah Anda yakin ingin mencetak Excel sekarang?",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, Cetak Sekarang!',
				cancelButtonText: 'Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					// Panggil API detail untuk export
					$.ajax({
						url: 'api/get_detail_sku_pdf.php',
						type: 'GET',
						data: { noRequest: noRequest },
						success: function(response) {
							exportToPDF(row.data(), response);
						},
						error: function() {
							Swal.fire('Error', 'Gagal mengambil detail', 'error');
						}
					});
				} else {
					Swal.fire('Dibatalkan', 'Proses cetak Excel dibatalkan.', 'info');
				}
			});
		});

	});

	function exportToPDF(headerData, htmlTable) {

const { jsPDF } = window.jspdf;
const doc = new jsPDF("p", "mm", "a4");

// ============================================
// 1. HEADER JUDUL
// ============================================
doc.setFont("helvetica", "bold");
doc.setFontSize(18);
doc.text("STOCK REQUEST", 14, 15);

doc.setLineWidth(0.5);
doc.line(14, 18, 196, 18); // garis bawah judul

// ============================================
// 2. HEADER INFORMASI SR
// ============================================
doc.setFont("helvetica", "normal");
doc.setFontSize(11);

doc.text("No Request        : " + headerData.NoRequest, 14, 30);
doc.text("Tanggal           : " + headerData.TglRequest, 14, 37);
doc.text("Gudang Sumber : " + headerData.KodeGudang + " - " + headerData.GudangAsal, 14, 44);
doc.text("Gudang Target : " + headerData.GdgTarget + " - " + headerData.GudangTarget, 14, 51);

// ============================================
// 3. PERHATIAN (MERAH)
// ============================================
doc.setFont("helvetica", "bold");
doc.setFontSize(12);
doc.setTextColor(255, 0, 0);

doc.text([
	"PERHATIAN !!! Lembar ini Untuk Packing Bukan Untuk Pengiriman",
	"" // baris kosong (enter)
], 14, 60);

// reset ke normal
doc.setTextColor(0, 0, 0);
doc.setFont("helvetica", "normal");

// ============================================
// 4. AMBIL TABEL DARI HTML
// ============================================
let wrapper = document.createElement("div");
wrapper.innerHTML = htmlTable;

let tableElement = wrapper.querySelector("table");
if (!tableElement) {
	Swal.fire("Error", "Tabel tidak ditemukan di response API!", "error");
	return;
}

// posisi tabel dimulai setelah catatan merah
let startTableY = 70;

// ============================================
// 5. AUTOTABLE STYLE PROFESIONAL
// ============================================
doc.autoTable({
	html: tableElement,
	startY: startTableY,
	theme: "grid",
	headStyles: {
		fillColor: [60, 141, 188],
		textColor: 255,
		fontSize: 9,
		halign: "center"
	},
	bodyStyles: {
		fontSize: 8,
		halign: "center"
	},
	alternateRowStyles: {
		fillColor: [245, 245, 245]
	},
	styles: {
		cellPadding: 2,
		lineColor: [200, 200, 200],
		lineWidth: 0.2
	}
});

// ============================================
// 6. FOOTER CATATAN
// ============================================
const endY = doc.lastAutoTable.finalY + 10;

doc.setFont("helvetica", "italic");
doc.setFontSize(9);

doc.text(
	"Print out ini digunakan untuk persiapan barang sesuai kode item & qty.\n" +
	"Pastikan Qty sesuai SR. Jika berbeda, koordinasikan dengan Alokator.",
	14,
	endY
);

// ============================================
// 7. OPEN NEW WINDOW (PRINT PREVIEW)
// ============================================
const pdfBlob = doc.output("blob");
const pdfUrl = URL.createObjectURL(pdfBlob);

const newWin = window.open("", "_blank", "width=900,height=800");

if (newWin) {
	newWin.document.write(`
		<html>
		<head><title>Print Preview</title></head>
		<body style="margin:0;padding:0;overflow:hidden">
			<embed src="${pdfUrl}" type="application/pdf" width="100%" height="100%"></embed>
		</body>
		</html>
	`);
} else {
	alert("Popup diblokir! Harap izinkan popup untuk situs ini.");
}
}





	
	function exportToExcel(headerData, detailHTML) {
		var wb = XLSX.utils.book_new();
		var ws_data = [];

		// Header Summary
		ws_data.push(["NoRequest", "Tanggal Request", "Kode Gudang", "Gudang Target","Keterangan"]);
		ws_data.push([
			headerData.NoRequest,
			headerData.TglRequest,
			headerData.KodeGudang,
			headerData.GdgTarget,
		]);
		
		// Tambah baris baru untuk keterangan panjang, merge 5 kolom
		ws_data.push(["Keterangan : " + headerData.Keterangan, "", "", "", ""]);

		ws_data.push([]); // kosong 1 baris

		// Detail SKU
		var tempDiv = document.createElement('div');
		tempDiv.innerHTML = detailHTML;
		var table = tempDiv.querySelector('table');

		if (table) {
			var rows = table.querySelectorAll('tr');
			rows.forEach((row) => {
				var rowData = [];
				var cells = row.querySelectorAll('th, td');
				cells.forEach((cell) => {
					rowData.push(cell.innerText.trim());
				});
				ws_data.push(rowData);
			});
		} else {
			alert('Tabel detail tidak ditemukan!');
			return;
		}

		var ws = XLSX.utils.aoa_to_sheet(ws_data);
		
		ws['!merges'] = [
			{ s: { r: 2, c: 0 }, e: { r: 2, c: 4 } } // Baris ke-3, kolom A–E
		];

		// Auto width kolom
		var max_widths = ws_data[0].map((_, colIndex) => 
			Math.max(...ws_data.map(row => (row[colIndex] ? row[colIndex].toString().length : 0)))
		);
		ws['!cols'] = max_widths.map(w => ({ wch: w + 5 }));

		XLSX.utils.book_append_sheet(wb, ws, "Stock Request");
		XLSX.writeFile(wb, `StockRequest_${headerData.NoRequest}.xlsx`);
	}

	$('#SRTable').on('click', '.norequest-link', function (e) {
		e.preventDefault();

		const noRequest = $(this).data('norequest');
		const link = this.href;

		// Buat elemen input sementara
		const tempInput = document.createElement('input');
		tempInput.value = noRequest;
		document.body.appendChild(tempInput);
		tempInput.select();
		document.execCommand('copy');
		document.body.removeChild(tempInput);

		console.log('NoRequest copied:', noRequest);

		// Buka tab baru
		window.open(link, '_blank');
	});

	var table;
	function loadData(tanggalAwal, tanggalAkhir, kodeGudang, gudangTarget, isClosed) {
		if (table) table.destroy();

		table = $('#SRTable').DataTable({
			scrollX: true,
			dom: '<"row align-items-center mb-3"<"col-md-6 d-flex"lB><"col-md-6"f>>tip',
			buttons: [
				{
					extend: 'copy',
					text: 'Copy Table',
					className: 'btn btn-success btn-sm'
				},
				{
					extend: 'excel',
					text: 'Export Excel',
					className: 'btn btn-info btn-sm'
				},
				{
					extend: 'pdf',
					text: 'Export PDF',
					className: 'btn btn-danger btn-sm'
				}
			],
			lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
			order: [[4, 'desc']], // <<<<<< ADD INI
			ajax: {
				url: 'api/stock-request-pending.php',
				method: 'GET',
				data: {
					tanggal_awal: tanggalAwal,
					tanggal_akhir: tanggalAkhir,
					kode_gudang: kodeGudang,
					gudang_target: gudangTarget,
					is_closed: isClosed
				},
				dataSrc: 'data'
			},
			columns: [
				{
					data: null,
					defaultContent: '<button class="expand-btn btn btn-sm btn-primary">Show Detail</button>&nbsp<button class="print-btn btn btn-sm btn-success">Print</button>',
					
					orderable: false
				},
				{
					data: 'BrandCode',
					title: 'Brand'
				},
				{
					data: 'NoRequest',
					render: function(data, type, row, meta) {
						return '<a href="http://myemba.co:84/#/inventory/transaction/new" ' +
							   'class="norequest-link" ' +
							   'data-norequest="' + data + '" ' +
							   'title="Klik Disini!!! Silakan lakukan transaksi di aplikasi MASERP dan langsung Paste No Requestnya Tanpa harus dicopy terlebih dahulu." ' +
							   'target="_blank">' + data + '</a>';

					}
				},
				{ data: 'TglRequest' },
				{ data: 'TglEntry' },
				{
					data: null,
					render: function(data, type, row, meta) {
						return row.KodeGudang + ' - ' + row.GudangAsal;
					},
					title: 'Gudang Sumber'
				},
				{
					data: null,
					render: function(data, type, row, meta) {
						return row.GdgTarget + ' - ' + row.GudangTarget;
					},
					title: 'Gudang Target'
				},
				{
					data: null,
					render: function(data, type, row, meta) {
						return row.alamatGudangTarget;
					},
					title: 'Alamat Gudang Target'
				},
				{ data: 'ParentTransaction' },
				//{ data: 'Keterangan' },
				{ 
					data: 'isClosed',
					render: function(data, type, row, meta) {
						if (data == 0) {
							return '<span class="badge bg-danger">Pending</span>';
						} else {
							return '<span class="badge bg-success">On Going</span>';
						}
					}
				},
				
			],
			language: {
				url: 'assets/id.json'
			}
		});

		table.on('draw', function() {
			highlightClosedRows();
		});
	}



	function highlightClosedRows() {
		const noRequestCount = {};

		// Hitung jumlah kemunculan setiap NoRequest
		table.rows({ search: 'applied' }).every(function() {
			const data = this.data();
			const noRequest = data.NoRequest;

			if (noRequest in noRequestCount) {
				noRequestCount[noRequest]++;
			} else {
				noRequestCount[noRequest] = 1;
			}
		});

		// Tandai baris yang NoRequest-nya muncul lebih dari sekali
		table.rows({ search: 'applied' }).every(function() {
			const data = this.data();
			const row = this.node();
			const noRequest = data.NoRequest;

			if (noRequestCount[noRequest] > 1) {
				$(row).addClass('table-success');
			} else {
				$(row).removeClass('table-success');
			}
		});
	}




</script>
