<?php
/**
 * Header global — dipakai di semua halaman publik.
 * Variabel opsional yang bisa di-set sebelum include:
 *   $page_title  (string)  -> judul tab browser
 *   $active_menu (string)  -> 'beranda' | 'gunung' | 'paket' | 'tentang' | 'kontak'
 */
$page_title      = $page_title ?? 'Muncak.Kuy - Booking Pendakian Gunung Terpercaya';
$active_menu     = $active_menu ?? '';
$hide_public_nav = $hide_public_nav ?? false;
$toast           = get_toast();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= clean($page_title) ?></title>
<meta name="description" content="Muncak.Kuy - Platform booking pendakian gunung terpercaya dan mudah digunakan.">
<link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="<?= !empty($dashboard_page) ? 'dashboard-page' : '' ?>">

<?php if (!$hide_public_nav): ?>
<div id="preloader">
  <div class="loader-mountain"></div>
  <p>Menyiapkan petualanganmu...</p>
</div>
<?php endif; ?>

<?php if (!$hide_public_nav): ?>

<nav class="navbar">
  <div class="container">
    <a href="<?= BASE_URL ?>/index.php" class="navbar-brand">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21l4-14 4 14M3 21h18M6 21l4-8M18 21l-4-8"/></svg>
      <span>Muncak.Kuy<span class="tagline">Temukan. Booking. Muncak!</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="<?= BASE_URL ?>/index.php" class="<?= $active_menu==='beranda'?'active':'' ?>">Beranda</a></li>
      <li><a href="<?= BASE_URL ?>/search.php" class="<?= $active_menu==='gunung'?'active':'' ?>">Gunung</a></li>
      <li><a href="<?= BASE_URL ?>/search.php?tab=paket" class="<?= $active_menu==='paket'?'active':'' ?>">Paket</a></li>
      <li><a href="<?= BASE_URL ?>/tentang.php" class="<?= $active_menu==='tentang'?'active':'' ?>">Tentang</a></li>
      <li><a href="<?= BASE_URL ?>/kontak.php" class="<?= $active_menu==='kontak'?'active':'' ?>">Kontak</a></li>
    </ul>
    <div class="nav-actions">
      <?php if (is_logged_in()): ?>
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-primary btn-sm">Dashboard</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm">Masuk</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Daftar</a>
      <?php endif; ?>
      <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
  </div>
</nav>

<?php endif; ?>

<script>
  window.BASE_URL = "<?= BASE_URL ?>";
  <?php if ($toast): ?>
  window.__TOAST__ = { type: "<?= clean($toast['type']) ?>", message: "<?= clean($toast['message']) ?>" };
  <?php endif; ?>
</script>
