<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();



$totalUser    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalGunung  = $pdo->query("SELECT COUNT(*) FROM gunung")->fetchColumn();
$totalBooking = $pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
$totalPendapatan = $pdo->query("SELECT COALESCE(SUM(total_harga),0) FROM booking WHERE status_pembayaran='lunas'")->fetchColumn();

$bookingTerbaru = $pdo->query("SELECT b.*, g.nama_gunung, u.nama_lengkap FROM booking b
    JOIN gunung g ON g.id=b.gunung_id JOIN users u ON u.id=b.user_id
    ORDER BY b.created_at DESC LIMIT 8")->fetchAll();

// Data untuk grafik booking per bulan (6 bulan terakhir)
$chartRaw = $pdo->query("SELECT DATE_FORMAT(tanggal_pendakian,'%Y-%m') ym, COUNT(*) total
    FROM booking WHERE tanggal_pendakian >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym ORDER BY ym")->fetchAll();

$page_title = 'Dashboard Admin - Muncak.Kuy';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash-layout">
  <?php $active = 'dashboard'; require __DIR__ . '/sidebar_admin.php'; ?>
  <main class="dash-main">
    <div class="dash-topbar">
      <h1>Dashboard Admin</h1>
      <div class="user-chip">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']) ?>&background=f2b705&color=0d3320" alt="avatar">
        <span><?= clean($_SESSION['nama']) ?></span>
      </div>
    </div>

    <div class="stat-grid">
      <div class="stat-card reveal"><div class="icon blue"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div><div class="num"><?= $totalUser ?></div><div class="label">Total Pengguna</div></div></div>
      <div class="stat-card reveal reveal-delay-1"><div class="icon green"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21l4-14 4 14M3 21h18"/></svg></div><div><div class="num"><?= $totalGunung ?></div><div class="label">Total Gunung</div></div></div>
      <div class="stat-card reveal reveal-delay-2"><div class="icon gold"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div><div><div class="num"><?= $totalBooking ?></div><div class="label">Total Booking</div></div></div>
      <div class="stat-card reveal reveal-delay-3"><div class="icon rose"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div><div><div class="num" style="font-size:17px;"><?= rupiah($totalPendapatan) ?></div><div class="label">Total Pendapatan</div></div></div>
    </div>

    <div class="panel reveal" style="margin-bottom:24px;">
      <div class="panel-head"><h3>Tren Booking (6 Bulan Terakhir)</h3></div>
      <canvas id="chartBooking" height="80"></canvas>
    </div>

    <div class="panel reveal">
      <div class="panel-head">
        <h3>Booking Terbaru</h3>
        <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn btn-sm btn-outline">Lihat Semua</a>
      </div>
      <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Kode</th><th>Pengguna</th><th>Gunung</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($bookingTerbaru as $b):
            $map = ['akan_datang'=>['info','Akan Datang'],'berlangsung'=>['warning','Berlangsung'],'selesai'=>['success','Selesai'],'batal'=>['danger','Batal']];
            [$cls,$lbl] = $map[$b['status_pendakian']];
          ?>
          <tr>
            <td><strong><?= clean($b['kode_booking']) ?></strong></td>
            <td><?= clean($b['nama_lengkap']) ?></td>
            <td><?= clean($b['nama_gunung']) ?></td>
            <td><?= tanggal_indo($b['tanggal_pendakian']) ?></td>
            <td><?= rupiah($b['total_harga']) ?></td>
            <td><span class="status-pill <?= $cls ?>"><?= $lbl ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
  const ctx = document.getElementById('chartBooking');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($chartRaw, 'ym')) ?>,
      datasets: [{
        label: 'Jumlah Booking',
        data: <?= json_encode(array_map('intval', array_column($chartRaw, 'total'))) ?>,
        borderColor: '#1b5e3a',
        backgroundColor: 'rgba(27,94,58,0.12)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: '#f2b705',
        pointRadius: 5,
        borderWidth: 3,
      }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
