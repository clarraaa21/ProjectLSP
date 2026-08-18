<?php
/**
 * Export detail E-Tiket ke file Excel (.xls).
 * Menggunakan teknik HTML table + header Excel (ringan, tanpa dependency,
 * dan terbuka sempurna di Microsoft Excel maupun Google Sheets).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$kode = trim($_GET['kode'] ?? '');
$stmt = $pdo->prepare("SELECT b.*, g.nama_gunung, g.lokasi, g.provinsi, p.nama_paket, u.nama_lengkap, u.email
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    JOIN users u ON u.id = b.user_id
    WHERE b.kode_booking = ? LIMIT 1");
$stmt->execute([$kode]);
$t = $stmt->fetch();

if (!$t || ($t['user_id'] != $_SESSION['user_id'] && !is_admin())) {
    http_response_code(404);
    die('Tiket tidak ditemukan.');
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=ETiket-{$t['kode_booking']}.xls");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; // BOM agar karakter UTF-8 terbaca benar di Excel
?>
<table border="1">
  <tr><th colspan="2" style="background:#0d3320;color:#ffffff;font-size:14px;">MUNCAK.KUY - DETAIL E-TIKET PENDAKIAN</th></tr>
  <tr><td><b>Kode Booking</b></td><td><?= clean($t['kode_booking']) ?></td></tr>
  <tr><td><b>Nama Peserta</b></td><td><?= clean($t['nama_lengkap']) ?></td></tr>
  <tr><td><b>Email</b></td><td><?= clean($t['email']) ?></td></tr>
  <tr><td><b>Gunung</b></td><td><?= clean($t['nama_gunung']) ?></td></tr>
  <tr><td><b>Lokasi</b></td><td><?= clean($t['lokasi']) ?>, <?= clean($t['provinsi']) ?></td></tr>
  <tr><td><b>Tanggal Pendakian</b></td><td><?= tanggal_indo($t['tanggal_pendakian']) ?></td></tr>
  <tr><td><b>Paket</b></td><td><?= clean($t['nama_paket']) ?></td></tr>
  <tr><td><b>Jumlah Peserta</b></td><td><?= (int)$t['jumlah_peserta'] ?></td></tr>
  <tr><td><b>Total Harga</b></td><td>Rp <?= number_format($t['total_harga'],0,',','.') ?></td></tr>
  <tr><td><b>Status Pembayaran</b></td><td><?= clean(ucfirst($t['status_pembayaran'])) ?></td></tr>
  <tr><td><b>Status Pendakian</b></td><td><?= clean(ucfirst(str_replace('_',' ',$t['status_pendakian']))) ?></td></tr>
  <tr><td><b>Tanggal Booking Dibuat</b></td><td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td></tr>
</table>
