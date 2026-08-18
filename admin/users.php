<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id=? AND role='user'");
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    if ($u) {
        $new = $u['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new, $id]);
        set_toast('success', 'Status pengguna berhasil diubah.');
    }
    redirect('/admin/users.php');
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM booking b WHERE b.user_id=u.id) AS total_booking
    FROM users u WHERE role='user' ORDER BY created_at DESC")->fetchAll();

$page_title = 'Kelola Pengguna - Admin Muncak.Kuy';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dash-layout">
  <?php $active = 'users'; require __DIR__ . '/sidebar_admin.php'; ?>
  <main class="dash-main">
    <div class="dash-topbar"><h1>Kelola Pengguna</h1></div>
    <div class="panel reveal">
      <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Nama</th><th>Email</th><th>No. Telepon</th><th>Total Booking</th><th>Bergabung</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><strong><?= clean($u['nama_lengkap']) ?></strong></td>
            <td><?= clean($u['email']) ?></td>
            <td><?= clean($u['no_telepon'] ?? '-') ?></td>
            <td><?= (int)$u['total_booking'] ?></td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td><span class="status-pill <?= $u['status']==='aktif'?'success':'danger' ?>"><?= ucfirst($u['status']) ?></span></td>
            <td><a href="?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline" onclick="return confirm('Ubah status pengguna ini?')"><?= $u['status']==='aktif'?'Nonaktifkan':'Aktifkan' ?></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
