<?php
/**
 * ==========================================
 * MUNCAK.KUY - Fungsi Helper Global
 * ==========================================
 */

/** Bersihkan input dari user */
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/** Format Rupiah, contoh: Rp 650.000 */
function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/** Format tanggal Indonesia, contoh: 25 Juli 2025 */
function tanggal_indo($tanggal) {
    $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Generate kode booking unik, contoh: MKY123456 */
function generate_kode_booking() {
    return 'MKY' . strtoupper(substr(uniqid(), -6));
}

/** Cek apakah user sudah login */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/** Cek apakah user adalah admin */
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Redirect helper */
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

/** Wajib login, kalau tidak redirect ke halaman login */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Silakan login terlebih dahulu.'];
        redirect('/login.php');
    }
}

/** Wajib admin */
function require_admin() {
    if (!is_admin()) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Akses ditolak. Khusus admin.'];
        redirect('/login.php');
    }
}

/**
 * Set pesan toast notifikasi yang akan ditampilkan di halaman berikutnya
 * (menggantikan alert() bawaan browser dengan toast custom yang cantik)
 */
function set_toast($type, $message) {
    // $type: 'success' | 'error' | 'warning' | 'info'
    $_SESSION['toast'] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil & hapus toast dari session (dipanggil sekali di header.php)
 * Mengembalikan array asosiatif atau null
 */
function get_toast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/** Log aktivitas ke tabel activity_log (opsional, untuk audit admin) */
function log_activity($pdo, $user_id, $aktivitas) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, aktivitas, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $aktivitas, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {
        // silent fail, jangan ganggu alur utama
    }
}

/** Validasi CSRF token sederhana */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
