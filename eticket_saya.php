<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT b.*, g.nama_gunung, g.foto_utama FROM booking b
    JOIN gunung g ON g.id=b.gunung_id
    WHERE b.user_id=? AND b.status_pembayaran='lunas' ORDER BY b.tanggal_pendakian DESC");
$stmt->execute([$userId]);
$tikets = $stmt->fetchAll();

$page_title = 'E-Tiket Saya - Muncak.Kuy';
require_once __DIR__ . '/includes/header.php';
?>
<div class="dash-layout" style="padding-top:0;">
  <?php $active = 'etiket'; require __DIR__ . '/includes/sidebar_user.php'; ?>
  <main class="dash-main">
    <div class="dash-topbar"><h1>E-Tiket Saya</h1></div>
    <?php if (empty($tikets)): ?>
      <div class="panel"><p class="text-muted">Belum ada e-tiket.</p></div>
    <?php else: ?>
    <div class="grid-3">
      <?php foreach ($tikets as $t): ?>
      <div class="gunung-card reveal-scale">
        <div class="img-wrap">
          <img src="<?= $t['foto_utama'] ? BASE_URL.'/assets/img/gunung/'.clean($t['foto_utama']) : 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=600&auto=format&fit=crop' ?>" alt="<?= clean($t['nama_gunung']) ?>" onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=600&auto=format&fit=crop'">
        </div>
        <div class="body">
          <h3><?= clean($t['nama_gunung']) ?></h3>
          <p class="text-muted" style="font-size:13px;margin:6px 0 12px;"><?= tanggal_indo($t['tanggal_pendakian']) ?> &middot; <?= clean($t['kode_booking']) ?></p>
          <a href="<?= BASE_URL ?>/eticket.php?kode=<?= clean($t['kode_booking']) ?>" class="btn btn-primary btn-block btn-sm">Lihat E-Tiket</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
