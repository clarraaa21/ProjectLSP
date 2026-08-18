<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$kode = trim($_GET['kode'] ?? '');
$stmt = $pdo->prepare("SELECT b.*, g.nama_gunung, g.lokasi, g.provinsi, g.foto_utama, p.nama_paket, u.nama_lengkap
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    JOIN users u ON u.id = b.user_id
    WHERE b.kode_booking = ? LIMIT 1");
$stmt->execute([$kode]);
$tiket = $stmt->fetch();

// Hanya pemilik booking atau admin yang boleh lihat
if (!$tiket || ($tiket['user_id'] != $_SESSION['user_id'] && !is_admin())) {
    set_toast('error', 'E-Tiket tidak ditemukan.');
    redirect('/dashboard.php');
}

$page_title = 'E-Tiket ' . $tiket['kode_booking'] . ' - Muncak.Kuy';
require_once __DIR__ . '/includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<section class="section" style="padding-top:130px;">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:30px;">
      <span class="eyebrow">E-Tiket Pendakian</span>
      <h2>Tiket kamu sudah siap!</h2>
      <p>Simpan atau cetak tiket ini dan tunjukkan saat check-in di basecamp.</p>
    </div>

    <div class="eticket reveal-scale in-view" id="eticketCard">
      <div class="eticket-header" style="background-image:url('<?= $tiket['foto_utama'] ? BASE_URL.'/uploads/gunung/'.clean($tiket['foto_utama']) : 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=900&auto=format&fit=crop' ?>');">
        <div class="brand">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f2b705" stroke-width="2"><path d="M8 21l4-14 4 14M3 21h18M6 21l4-8M18 21l-4-8"/></svg>
          Muncak.Kuy
        </div>
        <div class="title"><h2>E-TIKET PENDAKIAN</h2></div>
      </div>
      <div class="eticket-body">
        <div class="eticket-info">
          <div class="row"><span>Nama</span><span><?= clean($tiket['nama_lengkap']) ?></span></div>
          <div class="row"><span>Gunung</span><span><?= clean($tiket['nama_gunung']) ?></span></div>
          <div class="row"><span>Lokasi</span><span><?= clean($tiket['lokasi']) ?>, <?= clean($tiket['provinsi']) ?></span></div>
          <div class="row"><span>Tanggal</span><span><?= tanggal_indo($tiket['tanggal_pendakian']) ?></span></div>
          <div class="row"><span>Paket</span><span>Paket <?= clean($tiket['nama_paket']) ?></span></div>
          <div class="row"><span>Jumlah Peserta</span><span><?= (int)$tiket['jumlah_peserta'] ?> Orang</span></div>
          <div class="row"><span>Total Bayar</span><span><?= rupiah($tiket['total_harga']) ?></span></div>
          <div class="row"><span>Kode Booking</span><span><strong><?= clean($tiket['kode_booking']) ?></strong></span></div>
          <div class="row"><span>Status</span><span><span class="status-pill success">Lunas</span></span></div>
        </div>
        <div class="eticket-qr">
          <div id="qrcode"></div>
          <small>Tunjukkan QR ini saat check-in<br>di basecamp pendakian</small>
        </div>
      </div>
      <div class="eticket-actions">
        <a href="<?= BASE_URL ?>/eticket_pdf.php?kode=<?= clean($tiket['kode_booking']) ?>" class="btn btn-dark" target="_blank">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Unduh PDF
        </a>
        <button type="button" class="btn btn-outline" onclick="simpanKeHP()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg> Simpan ke HP
        </button>
        <button type="button" class="btn btn-outline" onclick="window.print()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg> Cetak
        </button>
        <a href="<?= BASE_URL ?>/eticket_excel.php?kode=<?= clean($tiket['kode_booking']) ?>" class="btn btn-outline">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Export Excel
        </a>
      </div>
    </div>
  </div>
</section>

<script>
  // Generate QR code asli berisi data booking (bisa di-scan untuk verifikasi)
  const qrPayload = JSON.stringify({
    kode: "<?= clean($tiket['kode_booking']) ?>",
    nama: "<?= clean($tiket['nama_lengkap']) ?>",
    gunung: "<?= clean($tiket['nama_gunung']) ?>",
    tanggal: "<?= clean($tiket['tanggal_pendakian']) ?>",
    peserta: <?= (int)$tiket['jumlah_peserta'] ?>
  });
  new QRCode(document.getElementById("qrcode"), {
    text: qrPayload,
    width: 130,
    height: 130,
    colorDark: "#0d3320",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
  });

  function simpanKeHP() {
    // Arahkan ke PDF (format paling universal untuk disimpan/dibagikan di HP)
    window.location.href = "<?= BASE_URL ?>/eticket_pdf.php?kode=<?= clean($tiket['kode_booking']) ?>&download=1";
    showToast('success', 'Tiket sedang diunduh ke perangkatmu...');
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
