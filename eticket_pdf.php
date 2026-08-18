<?php
/**
 * Generate PDF E-Tiket dengan layout tiket + QR code asli.
 * Menggunakan FPDF (vendor/fpdf) dan phpqrcode (vendor/phpqrcode).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

require_once __DIR__ . '/vendor/fpdf/fpdf.php';
require_once __DIR__ . '/vendor/phpqrcode/qrlib.php';

$kode = trim($_GET['kode'] ?? '');
$stmt = $pdo->prepare("SELECT b.*, g.nama_gunung, g.lokasi, g.provinsi, p.nama_paket, u.nama_lengkap
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    JOIN users u ON u.id = b.user_id
    WHERE b.kode_booking = ? LIMIT 1");
$stmt->execute([$kode]);
$tiket = $stmt->fetch();

if (!$tiket || ($tiket['user_id'] != $_SESSION['user_id'] && !is_admin())) {
    http_response_code(404);
    die('Tiket tidak ditemukan.');
}

// ---------- Generate QR code sementara sebagai file PNG ----------
$qrPayload = json_encode([
    'kode'    => $tiket['kode_booking'],
    'nama'    => $tiket['nama_lengkap'],
    'gunung'  => $tiket['nama_gunung'],
    'tanggal' => $tiket['tanggal_pendakian'],
    'peserta' => (int)$tiket['jumlah_peserta'],
]);
$qrTmpPath = sys_get_temp_dir() . '/qr_' . preg_replace('/[^A-Za-z0-9]/', '', $tiket['kode_booking']) . '.png';
QRcode::png($qrPayload, $qrTmpPath, QR_ECLEVEL_H, 6, 1);

// ---------- Warna tema (RGB) ----------
$forest  = [13, 51, 32];
$forest2 = [27, 94, 58];
$gold    = [242, 183, 5];
$cream   = [251, 249, 243];
$gray    = [90, 100, 95];

class TicketPDF extends FPDF {
    function Header() {}
    function Footer() {}
}

$pdf = new TicketPDF('P', 'mm', [148, 210]); // ukuran custom, ramping seperti tiket
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(0, 0, 0);

// Header hijau tua
$pdf->SetFillColor($forest[0], $forest[1], $forest[2]);
$pdf->Rect(0, 0, 148, 32, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->SetXY(10, 8);
$pdf->Cell(0, 8, 'MUNCAK.KUY', 0, 1);
$pdf->SetFont('Helvetica', '', 9);
$pdf->SetXY(10, 16);
$pdf->SetTextColor($gold[0], $gold[1], $gold[2]);
$pdf->Cell(0, 6, 'E-TIKET PENDAKIAN RESMI', 0, 1);

// Garis emas
$pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
$pdf->Rect(0, 32, 148, 2, 'F');

// Body: info tiket (kiri)
$pdf->SetTextColor(20, 32, 26);
$y = 44;
$rows = [
    ['Nama Peserta', $tiket['nama_lengkap']],
    ['Gunung', $tiket['nama_gunung']],
    ['Lokasi', $tiket['lokasi'] . ', ' . $tiket['provinsi']],
    ['Tanggal Pendakian', tanggal_indo($tiket['tanggal_pendakian'])],
    ['Paket', 'Paket ' . $tiket['nama_paket']],
    ['Jumlah Peserta', $tiket['jumlah_peserta'] . ' Orang'],
    ['Total Bayar', 'Rp ' . number_format($tiket['total_harga'], 0, ',', '.')],
    ['Kode Booking', $tiket['kode_booking']],
    ['Status', 'LUNAS'],
];
foreach ($rows as $r) {
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
    $pdf->SetXY(10, $y);
    $pdf->Cell(45, 6, $r[0]);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->SetTextColor(20, 32, 26);
    $pdf->SetXY(55, $y);
    $pdf->Cell(60, 6, iconv('UTF-8', 'windows-1252//TRANSLIT', $r[1]));
    $y += 9;
}

// Garis putus-putus pemisah
$pdf->SetDrawColor(220, 212, 189);
$pdf->SetLineWidth(0.4);
for ($dy = 32; $dy < 210; $dy += 3) {
    $pdf->Line(96, $dy, 96, $dy + 1.5);
}

// QR Code (kanan)
$pdf->Image($qrTmpPath, 104, 45, 36, 36);
$pdf->SetFont('Helvetica', '', 7);
$pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
$pdf->SetXY(98, 84);
$pdf->MultiCell(44, 4, 'Tunjukkan QR ini saat check-in di basecamp pendakian', 0, 'C');

// Footer catatan
$pdf->SetFont('Helvetica', 'I', 7.5);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY(10, 195);
$pdf->Cell(0, 5, 'Tiket ini sah tanpa tanda tangan. Dicetak otomatis oleh sistem Muncak.Kuy.', 0, 1);

@unlink($qrTmpPath);

$downloadMode = isset($_GET['download']) ? 'D' : 'I';
$pdf->Output($downloadMode, 'ETiket-' . $tiket['kode_booking'] . '.pdf');
