<?php
ob_start(); // --- Tambahkan untuk cegah output sebelum PDF

require(__DIR__ . '/../../assets/fpdf/fpdf.php');

class PDF extends FPDF
{
    // Header
    function Header()
    {
        // ... (Bagian Kiri Atas & Kanan Atas tetap sama)
        $this->SetFont('Courier', 'B', 10); 
        $this->Cell(0, 5, 'PT. KASIH KARUNIA SEJATI ( PRINT TES IT )', 0, 1, 'L');
        $this->SetFont('Courier', '', 9); 
        $this->Cell(0, 4, 'Jl. Bandulan Barat No.36', 0, 1, 'L');
        $this->Cell(0, 4, 'Telp. 0341 - 552427        Fax. MALANG,JAWA TIMUR', 0, 1, 'L');
        $this->Ln(2); 

        // Date and Page
        $this->SetY(8); 
        $this->SetX(150); 
        $this->SetFont('Courier', '', 9); 
        $this->Cell(0, 4, 'Page 1 of 1', 0, 1, 'R');
        $this->SetX(150);
        $this->Cell(0, 4, '29 Oct 2025 14:52:46', 0, 1, 'R');
        $this->SetX(150);
        $this->Cell(0, 4, 'Print :           2 x', 0, 1, 'R');
        $this->Ln(5); 

        // Faktur Penjualan
        $this->SetFont('Courier', 'B', 14); 
        $this->Cell(0, 7, 'Faktur Penjualan', 0, 1, 'L');
        $this->Ln(1); 

        // --- POSISI PENTING: Penyesuaian Detail Transaksi dan Pelanggan ---
        $start_y = $this->GetY();
        $col_label_width = 20;
        $col_data_width = 50;
        
        // Transaction Details
        $this->SetFont('Courier', '', 9); 
        $this->Cell($col_label_width, 5, 'No. Bukti  :', 0, 0, 'L');
        $this->SetFont('Courier', 'B', 9); 
        $this->Cell($col_data_width, 5, '  101-2510-PTP-00293', 0, 1, 'L');

        $this->SetFont('Courier', '', 9); 
        $this->Cell($col_label_width, 5, 'Tanggal    :', 0, 0, 'L');
        $this->Cell($col_data_width, 5, '  29-Oct-2025', 0, 1, 'L');
        
        $this->Cell($col_label_width, 5, 'Salesman   :', 0, 0, 'L');
        $this->Cell($col_data_width, 5, '  80580', 0, 1, 'L');

        $this->Cell($col_label_width, 5, 'Order Reff :', 0, 0, 'L');
        $this->Cell($col_data_width, 5, '  0000', 0, 1, 'L');

        $this->SetFont('Courier', 'B', 9); 
        $this->Cell(40, 5, 'SPECIAL PRICE', 1, 1, 'L'); 
        
        $last_y = $this->GetY(); 

        // Customer Details - Sisi Kanan
        $this->SetY($start_y); 
        $this->SetX(100); 

        $block_width = 100;

        $rect_start_y = $this->GetY();
        
        $this->SetFont('Courier', '', 9); 
        $this->Cell($block_width, 4, 'Kepada Yth.', 0, 1, 'L');
        $this->SetX(100);

        $this->SetFont('Courier', 'B', 9); 
        $this->Cell($block_width, 5, 'TOKO ULINZA BUKITTINGGI/NON COUNTER - DE', 0, 1, 'L');
        $this->SetX(100);

        $this->SetFont('Courier', '', 9); 
        $alamat_teks = 'KOMP AURI NO.47 PASAR AUR KUNING BUKITTINGGI, EKSP : ANDALAN CARGO JL.KH MAS MASNYUR NO 25, TANAH ABANG, JAKPUS, 081399320003';
        $this->MultiCell($block_width, 4, $alamat_teks, 0, 'L');
        $this->SetX(100); 

        $this->SetFont('Courier', '', 9); 
        $this->Cell($block_width / 2, 4, 'JAKARTA', 0, 0, 'L'); 
        
        $this->SetFont('Courier', 'B', 9); 
        $this->Cell($block_width / 2, 4, 'ULINZA BKT', 0, 1, 'R'); 

        $rect_end_y = $this->GetY();
        $this->Rect(100, $rect_start_y, $block_width, $rect_end_y - $rect_start_y);
        $this->SetY($rect_end_y);

        // Menarik Posisi Y kembali ke yang terendah
        $this->SetY(max($last_y, $this->GetY()));
        $this->Ln(3); 

        // --- Mulai Header Tabel Sesuai Gambar ---

        // 1. Garis Penuh ATAS Tabel
        $line_y = $this->GetY();
        $this->Line(10, $line_y, 200, $line_y); // Garis penuh dari X=10 ke X=200
        $this->Ln(1); // Jeda kecil setelah garis

        // 2. Table Header - Tanpa Border Cell (Border = 0)
        $this->SetFont('Courier', 'B', 9); 
        $this->Cell(30, 7, 'Model', 0, 0, 'L');
        $this->Cell(45, 7, 'Warna & Harga', 0, 0, 'L');
        $this->Cell(60, 7, 'Ukuran & Qty Kirim', 0, 0, 'C');
        $this->Cell(20, 7, 'Total', 0, 0, 'R');
        $this->Cell(20, 7, '   Nilai', 0, 1, 'R'); // Nilai rata kanan, pindah baris

        // 3. Garis Penuh BAWAH Tabel
        $line_y = $this->GetY();
        $this->Line(10, $line_y, 200, $line_y); // Garis penuh dari X=10 ke X=200
        $this->Ln(1); // Jeda kecil setelah garis

        // --- Akhir Header Tabel ---
    }

    // Footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Courier', 'I', 8); 
    }

    function InvoiceTable($data)
    {
        $this->SetFont('Courier', '', 9); 
        $this->SetFillColor(255, 255, 255); // Putih
        $fill = false; 
        $lebar_nilai_mepet = 35; 
    
        $is_model_row = false;
        $is_total_row = false;
    
        foreach ($data as $index => $item) {
            // --- 1. LOGIKA GARIS PEMISAH MODEL ---
            
            $is_model_row = !empty($item['model']) && !empty($item['warna_harga']); // Cek baris model (misal: '20542401', '139,300')
            $is_total_row = !empty($item['model']) && empty($item['warna_harga']); // Cek baris total (misal: 'POLO/T-SHIRT WANGKY', '')
    
            // Tambahkan garis sebelum baris model baru (kecuali baris pertama di tabel)
            if ($index > 0 && $is_model_row) {
                $line_y = $this->GetY();
                $this->Line(10, $line_y, 200, $line_y);
            }
    
            // --- 2. CETAK BARIS DATA ---
            
            // Cek baris total atau baris model utama untuk menentukan apakah harus BOLD
            if ($is_total_row || $is_model_row) {
                $this->SetFont('Courier', 'B', 9);
            } else {
                $this->SetFont('Courier', '', 9);
            }
            
            $this->Cell(30, 6, $item['model'], 0, 0, 'L', $fill);
            $this->Cell(45, 6, $item['warna_harga'], 0, 0, 'L', $fill);
    
            // Ukuran & Qty Kirim
            $x_start_qty = $this->GetX();
            $this->SetX($x_start_qty + 2);
            
            // Logika Qty Kirim
            $qty_cells = 0;
            foreach ($item['qty_kirim'] as $qty) {
                $this->Cell(9.66, 6, $qty, 0, 0, 'C', $fill); 
                $qty_cells++;
            }
            // Jika jumlah Cell kurang dari 6 (misal untuk model 23042001 yang hanya punya 5),
            // paksa kursor ke posisi akhir kolom 60mm
            if ($qty_cells < 6) {
                 $this->SetX($x_start_qty + 60);
            } else {
                 $this->SetX($x_start_qty + 60);
            }
    
    
            // Pastikan numeric sebelum format
            $total = is_numeric($item['total']) ? number_format($item['total'], 0, ',', '.') : '';
            $nilai = is_numeric($item['nilai']) ? number_format($item['nilai'], 0, ',', '.') : '';
    
            // Kolom Total (lebar 20mm)
            $this->Cell(20, 6, $total, 0, 0, 'R', $fill); 
            
            // Kolom Nilai (lebar 35mm, rata kanan mepet margin)
            $this->Cell($lebar_nilai_mepet, 6, $nilai, 0, 1, 'R', $fill); 
    
            // --- 3. LOGIKA GARIS BAWAH TOTAL ---
            
            // Tambahkan garis setelah baris total model
            if ($is_total_row) {
                $line_y = $this->GetY();
                $this->Line(10, $line_y, 200, $line_y);
            }
        }
        // Kembalikan font ke normal setelah loop (jika ada teks tambahan)
        $this->SetFont('Courier', '', 9);
    }
}

// === Generate PDF ===
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Data tabel
$data = [
    ['model'=>'20542401','warna_harga'=>'139,300','qty_kirim'=>['01','02','03','04','05','06'],'total'=>'','nilai'=>''],
    ['model'=>'','warna_harga'=>'(02) BLACK','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1671600],
    ['model'=>'','warna_harga'=>'(39) GREY','qty_kirim'=>['',6,4,2,'',''],'total'=>12,'nilai'=>1671600],
    ['model'=>'','warna_harga'=>'(45) OFF WHITE','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1671600],
    ['model'=>'','warna_harga'=>'(92) NAVY','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1671600],
    ['model'=>'POLO/T-SHIRT WANGKY','warna_harga'=>'','qty_kirim'=>[18,22,8,'','',''],'total'=>48,'nilai'=>6686400],
    ['model'=>'23042001','warna_harga'=>'118,300','qty_kirim'=>['02','03','04','05','06',''],'total'=>'','nilai'=>''], 
    ['model'=>'','warna_harga'=>'(01) WHITE','qty_kirim'=>['',5,5,2,'',''],'total'=>12,'nilai'=>1419600],
    ['model'=>'','warna_harga'=>'(02) BLACK','qty_kirim'=>['',5,2,5,'',''],'total'=>12,'nilai'=>1419600],
    ['model'=>'','warna_harga'=>'(03) KHAKI','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1419600],
    ['model'=>'','warna_harga'=>'(11) DARK GREY','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1419600],
    ['model'=>'','warna_harga'=>'(35) CREAM','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1419600],
    ['model'=>'','warna_harga'=>'(92) NAVY','qty_kirim'=>['',4,6,2,'',''],'total'=>12,'nilai'=>1419600],
    ['model'=>'/FITTING LBH BSR DR 213','warna_harga'=>'','qty_kirim'=>[26,31,15,'','',''],'total'=>72,'nilai'=>8517600]
];

$pdf->InvoiceTable($data);

// Output PDF
$pdf->Output('Faktur_Penjualan.pdf', 'I');

ob_end_flush();
?>