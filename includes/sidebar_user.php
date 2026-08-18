<?php $active = $active ?? ''; ?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21l4-14 4 14M3 21h18M6 21l4-8M18 21l-4-8"/></svg>
    Muncak.Kuy
  </div>
  <a href="<?= BASE_URL ?>/dashboard.php" class="<?= $active==='beranda'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg> Beranda
  </a>
  <a href="<?= BASE_URL ?>/search.php" class="<?= $active==='pencarian'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg> Pencarian
  </a>
  <a href="<?= BASE_URL ?>/booking_saya.php" class="<?= $active==='booking'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Booking Saya
  </a>
  <a href="<?= BASE_URL ?>/eticket_saya.php" class="<?= $active==='etiket'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10a2 2 0 002-2V6a2 2 0 00-2-2v0a2 2 0 00-2 2v0M21 10a2 2 0 01-2-2V6a2 2 0 012-2v0a2 2 0 012 2v0"/><rect x="3" y="4" width="18" height="16" rx="2"/></svg> E-Tiket Saya
  </a>
  <a href="<?= BASE_URL ?>/wishlist.php" class="<?= $active==='wishlist'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 000-7.8z"/></svg> Wishlist
  </a>
  <a href="<?= BASE_URL ?>/profil.php" class="<?= $active==='profil'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg> Profil Saya
  </a>
  <a href="<?= BASE_URL ?>/pengaturan.php" class="<?= $active==='pengaturan'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.6V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.6-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.6-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H9a1.7 1.7 0 001-1.6V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.6 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V9a1.7 1.7 0 001.6 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.6 1z"/></svg> Pengaturan
  </a>
  <a href="<?= BASE_URL ?>/logout.php" class="logout-link">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Keluar
  </a>
</aside>
