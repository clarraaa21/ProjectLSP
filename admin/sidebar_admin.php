<?php $active = $active ?? ''; ?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21l4-14 4 14M3 21h18M6 21l4-8M18 21l-4-8"/></svg>
    Muncak.Kuy <span class="badge-role">ADMIN</span>
  </div>
  <a href="<?= BASE_URL ?>/admin/index.php" class="<?= $active==='dashboard'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg> Dashboard
  </a>
  <a href="<?= BASE_URL ?>/admin/bookings.php" class="<?= $active==='booking'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Kelola Booking
  </a>
  <a href="<?= BASE_URL ?>/admin/paket.php" class="<?= $active==='paket'?'active':'' ?>">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
    <line x1="12" y1="22.08" x2="12" y2="12"/>
  </svg>
  Kelola Paket
</a>
  <a href="<?= BASE_URL ?>/admin/gunung.php" class="<?= $active==='gunung'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21l4-14 4 14M3 21h18M6 21l4-8M18 21l-4-8"/></svg> Kelola Gunung
  </a>
  <a href="<?= BASE_URL ?>/admin/users.php" class="<?= $active==='users'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg> Kelola Pengguna
  </a>
    <a href="<?= BASE_URL ?>/admin/reviews.php" class="<?= $active==='reviews'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
    </svg>
    Kelola Review
  </a>
  
<a href="<?= BASE_URL ?>/admin/kontak.php" class="<?= $active==='kontak'?'active':'' ?>">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <rect x="3" y="5" width="18" height="14" rx="2"/>
    <polyline points="3 7 12 13 21 7"/>
  </svg>
  Pesan Kontak
</a>


  <a href="<?= BASE_URL ?>/admin/export_excel.php" class="<?= $active==='export'?'active':'' ?>">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Export Laporan
  </a>
  <a href="<?= BASE_URL ?>/logout.php" class="logout-link">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Keluar
  </a>
</aside>
