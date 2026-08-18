<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect('/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_toast('error', 'Sesi tidak valid, silakan coba lagi.');
        redirect('/register.php');
    }

    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi_password'] ?? '';

    if ($nama === '' || $email === '' || $password === '') {
        set_toast('error', 'Semua field wajib diisi.');
        redirect('/register.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_toast('error', 'Format email tidak valid.');
        redirect('/register.php');
    }
    if (strlen($password) < 6) {
        set_toast('error', 'Password minimal 6 karakter.');
        redirect('/register.php');
    }
    if ($password !== $konfirm) {
        set_toast('error', 'Konfirmasi password tidak cocok.');
        redirect('/register.php');
    }

    $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        set_toast('error', 'Email sudah terdaftar. Silakan login.');
        redirect('/register.php');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$nama, $email, $hash]);

    $userId = $pdo->lastInsertId();
    $_SESSION['user_id'] = $userId;
    $_SESSION['nama']    = $nama;
    $_SESSION['role']    = 'user';
    log_activity($pdo, $userId, 'Registrasi akun baru');

    set_toast('success', 'Akun berhasil dibuat! Selamat datang, ' . $nama . '.');
    redirect('/dashboard.php');
}

$page_title = 'Daftar - Muncak.Kuy';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap" style="padding-top:0;">
  <div class="auth-visual" style="background-image:url('https://images.unsplash.com/photo-1454496522488-7a8e488e8606?q=80&w=1200&auto=format&fit=crop');">
    <div class="quote">"Setiap pendaki hebat pernah menjadi pemula. Mulai perjalananmu hari ini."</div>
  </div>
  <div class="auth-form-side">
    <div class="card-form reveal-scale in-view">
      <h2>Buat Akun Baru</h2>
      <p>Daftar dan mulai petualanganmu.</p>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap kamu" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="form-group">
          <label>Konfirmasi Password</label>
          <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Daftar</button>
      </form>
      <div class="form-foot">Sudah punya akun? <a href="<?= BASE_URL ?>/login.php">Masuk</a></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
