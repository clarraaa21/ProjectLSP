<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT g.* FROM wishlist w JOIN gunung g ON g.id=w.gunung_id WHERE w.user_id=? ORDER BY w.created_at DESC");
$stmt->execute([$userId]);
$list = $stmt->fetchAll();

$page_title = 'Wishlist - Muncak.Kuy';
require_once __DIR__ . '/includes/header.php';
?>
<div class="dash-layout" style="padding-top:0;">
  <?php $active = 'wishlist'; require __DIR__ . '/includes/sidebar_user.php'; ?>
  <main class="dash-main">
    <div class="dash-topbar"><h1>Wishlist Saya</h1></div>
    <?php if (empty($list)): ?>
      <div class="panel"><p class="text-muted">Belum ada gunung di wishlist. Klik ikon hati pada kartu gunung untuk menambahkan.</p></div>
    <?php else: ?>
    <div class="grid-3">
      <?php foreach ($list as $g): ?>
      <div class="gunung-card reveal-scale">
        <div class="img-wrap">
          <img src="<?= $g['foto_utama'] ? BASE_URL.'/assets/img/gunung/'.clean($g['foto_utama']) : 'https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=600&auto=format&fit=crop' ?>" alt="<?= clean($g['nama_gunung']) ?>" onerror="this.src='https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=600&auto=format&fit=crop'">
          <button class="wishlist-btn active" data-gunung-id="<?= $g['id'] ?>" aria-label="Wishlist">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-6.7-4.35-9.3-8.1C.8 9.8 1.8 6 5.2 5c2-.6 3.7.3 4.8 1.8C11.1 5.3 12.8 4.4 14.8 5c3.4 1 4.4 4.8 2.5 7.9C18.7 16.65 12 21 12 21z"/></svg>
          </button>
        </div>
        <div class="body">
          <h3><?= clean($g['nama_gunung']) ?></h3>
          <p class="text-muted" style="font-size:13px;margin:6px 0 12px;"><?= clean($g['lokasi']) ?>, <?= clean($g['provinsi']) ?></p>
          <a href="<?= BASE_URL ?>/booking.php?gunung_id=<?= $g['id'] ?>" class="btn btn-dark btn-block btn-sm">Booking Sekarang</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
