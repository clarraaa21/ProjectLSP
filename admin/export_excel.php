<?php
/**
 * Export laporan seluruh booking ke Excel (.xls) — untuk admin.
 * Bisa difilter berdasarkan rentang tanggal via query string ?dari=&sampai=
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$doExport = isset($_GET['export']);

$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$sql = "SELECT b.kode_booking, u.nama_lengkap, u.email, g.nama_gunung, p.nama_paket,
        b.tanggal_pendakian, b.jumlah_peserta, b.total_harga, b.status_pembayaran, b.status_pendakian, b.created_at
        FROM booking b
        JOIN users u ON u.id=b.user_id
        JOIN gunung g ON g.id=b.gunung_id
        JOIN paket p ON p.id=b.paket_id WHERE 1=1";
$params = [];
if ($dari !== '')   { $sql .= " AND b.tanggal_pendakian >= ?"; $params[] = $dari; }
if ($sampai !== '') { $sql .= " AND b.tanggal_pendakian <= ?"; $params[] = $sampai; }
$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

if ($doExport) {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=Laporan-Booking-MuncakKuy-" . date('Ymd-His') . ".xls");
    echo "\xEF\xBB\xBF";
    ?>
    <table border="1">
      <tr style="background:#0d3320;color:#fff;">
        <th>Kode Booking</th><th>Nama</th><th>Email</th><th>Gunung</th><th>Paket</th>
        <th>Tanggal Pendakian</th><th>Peserta</th><th>Total Harga</th><th>Status Bayar</th><th>Status Pendakian</th><th>Dibuat</th>
      </tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= clean($r['kode_booking']) ?></td>
        <td><?= clean($r['nama_lengkap']) ?></td>
        <td><?= clean($r['email']) ?></td>
        <td><?= clean($r['nama_gunung']) ?></td>
        <td><?= clean($r['nama_paket']) ?></td>
        <td><?= clean($r['tanggal_pendakian']) ?></td>
        <td><?= (int)$r['jumlah_peserta'] ?></td>
        <td><?= (float)$r['total_harga'] ?></td>
        <td><?= clean(ucfirst($r['status_pembayaran'])) ?></td>
        <td><?= clean(ucfirst(str_replace('_',' ',$r['status_pendakian']))) ?></td>
        <td><?= clean($r['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php
    exit;
}

$page_title = 'Export Laporan - Admin Muncak.Kuy';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash-layout">
  <?php $active = 'export'; require __DIR__ . '/sidebar_admin.php'; ?>
  <main class="dash-main">
    <div class="dash-topbar"><h1>Export Laporan Booking</h1></div>
    <div class="panel reveal" style="max-width:560px;margin-bottom:24px;">
      <form method="GET" target="_blank">
        <input type="hidden" name="export" value="1">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="form-group"><label>Dari Tanggal</label><input type="date" name="dari" class="form-control" value="<?= clean($dari) ?>"></div>
          <div class="form-group"><label>Sampai Tanggal</label><input type="date" name="sampai" class="form-control" value="<?= clean($sampai) ?>"></div>
        </div>
        <button type="submit" class="btn btn-primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Export ke Excel
        </button>
      </form>
    </div>

    <div class="panel reveal">
      <div class="panel-head"><h3>Preview Data (<?= count($rows) ?> baris)</h3></div>
      <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Kode</th><th>Nama</th><th>Gunung</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($rows,0,15) as $r): ?>
          <tr>
            <td><?= clean($r['kode_booking']) ?></td>
            <td><?= clean($r['nama_lengkap']) ?></td>
            <td><?= clean($r['nama_gunung']) ?></td>
            <td><?= tanggal_indo($r['tanggal_pendakian']) ?></td>
            <td><?= rupiah($r['total_harga']) ?></td>
            <td><?= clean(ucfirst($r['status_pembayaran'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
