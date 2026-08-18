<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect('/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_toast('error', 'Sesi tidak valid, silakan coba lagi.');
        redirect('/login.php');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        set_toast('error', 'Email dan password wajib diisi.');
        redirect('/login.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama_lengkap'];
        $_SESSION['role']    = $user['role'];
        log_activity($pdo, $user['id'], 'Login ke sistem');
        set_toast('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
        redirect($user['role'] === 'admin' ? '/admin/index.php' : '/dashboard.php');
    } else {
        set_toast('error', 'Email atau password salah.');
        redirect('/login.php');
    }
}

$page_title = 'Masuk - Muncak.Kuy';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap" style="padding-top:0;">
  <div class="auth-form-side">
    <div class="card-form reveal-scale in-view">
      <h2>Selamat Datang Kembali!</h2>
      <p>Login untuk melanjutkan petualanganmu.</p>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group">
          <label>Email / Username</label>
          <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="form-row-inline">
          <label style="display:flex;align-items:center;gap:6px;font-weight:500;"><input type="checkbox" name="remember"> Ingat saya</label>
          <a href="#">Lupa password?</a>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
      </form>
      <div class="form-foot">Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar</a></div>
    </div>
  </div>
  <div class="auth-visual" style="background-image:url('https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?q=80&w=1200&auto=format&fit=crop');">
    <div class="quote">"Puncak tertinggi dimulai dari langkah pertama yang kau ambil hari ini."</div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
