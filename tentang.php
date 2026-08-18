<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Tentang Kami - Muncak.Kuy';
$active_menu = 'tentang';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top:130px;">
  <div class="container" style="max-width:800px;">
    <div class="section-head reveal">
      <span class="eyebrow">Tentang Kami</span>
      <h2>Muncak.Kuy — Sahabat Pendaki Indonesia</h2>
      <p>Muncak.Kuy adalah platform booking pendakian gunung terpercaya dan mudah digunakan, hadir untuk menghubungkan para pendaki dengan pengelola basecamp di seluruh Indonesia secara aman, transparan, dan efisien.</p>
    </div>
    <div class="grid-3 reveal">
      <div class="panel" style="text-align:center;"><h3 style="font-size:28px;color:var(--gold-600);">50+</h3><p class="text-muted">Gunung Terdaftar</p></div>
      <div class="panel" style="text-align:center;"><h3 style="font-size:28px;color:var(--gold-600);">10K+</h3><p class="text-muted">Pendaki Terlayani</p></div>
      <div class="panel" style="text-align:center;"><h3 style="font-size:28px;color:var(--gold-600);">4.8★</h3><p class="text-muted">Rating Kepuasan</p></div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
