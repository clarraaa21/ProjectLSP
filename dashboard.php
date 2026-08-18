<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

// Jika yang login adalah admin, arahkan ke dashboard admin
if (is_admin()) {
    redirect('/admin/index.php');
}

$userId = $_SESSION['user_id'];
$toast  = get_toast();

// Ambil statistik user
$totalBooking = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE user_id=?");
$totalBooking->execute([$userId]); $totalBooking = $totalBooking->fetchColumn();

$selesai = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE user_id=? AND status_pendakian='selesai'");
$selesai->execute([$userId]); $selesai = $selesai->fetchColumn();

$akanDatang = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE user_id=? AND status_pendakian='akan_datang'");
$akanDatang->execute([$userId]); $akanDatang = $akanDatang->fetchColumn();

$reviewDibuat = $pdo->prepare("SELECT COUNT(*) FROM review WHERE user_id=?");
$reviewDibuat->execute([$userId]); $reviewDibuat = $reviewDibuat->fetchColumn();

// Booking terbaru
$stmt = $pdo->prepare("SELECT b.*, g.nama_gunung, p.nama_paket FROM booking b
    JOIN gunung g ON g.id=b.gunung_id JOIN paket p ON p.id=b.paket_id
    WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$bookingTerbaru = $stmt->fetchAll();

$page_title = 'Dashboard - Muncak.Kuy';
$hide_public_nav = true;
$dashboard_page = true;
require_once __DIR__ . '/includes/header.php';
?>

<div class="dash-layout">
  <?php
  $active = 'beranda';

  if (is_admin()) {
      require __DIR__ . '/admin/sidebar_admin.php';
  } else {
      require __DIR__ . '/includes/sidebar_user.php';
  }
  ?>
  <main class="dash-main">
    <div class="dash-topbar">
      <div>
        <button class="sidebar-toggle btn btn-sm btn-outline" style="display:none;">☰</button>
        <h1>Halo, <?= clean($_SESSION['nama']) ?>! 👋</h1>
      </div>
      <div class="user-chip">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=1b5e3a&color=fff" alt="avatar">
        <span><?= clean($_SESSION['nama']) ?></span>
      </div>
    </div>

    <div class="stat-grid">
      <div class="stat-card reveal"><div class="icon green"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div><div><div class="num"><?= $totalBooking ?></div><div class="label">Total Booking</div></div></div>
      <div class="stat-card reveal reveal-delay-1"><div class="icon gold"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div><div class="num"><?= $selesai ?></div><div class="label">Pendakian Selesai</div></div></div>
      <div class="stat-card reveal reveal-delay-2"><div class="icon blue"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div><div><div class="num"><?= $akanDatang ?></div><div class="label">Akan Datang</div></div></div>
      <div class="stat-card reveal reveal-delay-3"><div class="icon rose"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg></div><div><div class="num"><?= $reviewDibuat ?></div><div class="label">Review Dibuat</div></div></div>
    </div>

    <div class="panel reveal">
      <div class="panel-head">
        <h3>Booking Terbaru</h3>
        <a href="<?= BASE_URL ?>/booking_saya.php" class="btn btn-sm btn-outline">Lihat Semua</a>
      </div>
      <?php if (empty($bookingTerbaru)): ?>
        <p class="text-muted">Kamu belum memiliki booking. <a href="<?= BASE_URL ?>/search.php">Cari gunung sekarang &rarr;</a></p>
      <?php else: ?>
      <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Gunung</th><th>Tanggal</th><th>Paket</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($bookingTerbaru as $b):
            $map = ['akan_datang'=>['info','Akan Datang'],'berlangsung'=>['warning','Berlangsung'],'selesai'=>['success','Selesai'],'batal'=>['danger','Batal']];
            [$cls,$lbl] = $map[$b['status_pendakian']];
          ?>
          <tr>
            <td><strong><?= clean($b['nama_gunung']) ?></strong></td>
            <td><?= tanggal_indo($b['tanggal_pendakian']) ?></td>
            <td><?= clean($b['nama_paket']) ?></td>
            <td><span class="status-pill <?= $cls ?>"><?= $lbl ?></span></td>
            <td><a href="<?= BASE_URL ?>/eticket.php?kode=<?= clean($b['kode_booking']) ?>" class="btn btn-sm btn-dark">Lihat</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
