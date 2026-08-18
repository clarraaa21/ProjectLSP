<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

/*
|--------------------------------------------------------------------------
| VALIDASI REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/search.php');
}

if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    set_toast('error', 'Sesi tidak valid, silakan coba lagi.');
    redirect('/search.php');
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA DARI FORM
|--------------------------------------------------------------------------
*/

$userId   = (int)($_SESSION['user_id'] ?? 0);
$gunungId = (int)($_POST['gunung_id'] ?? 0);
$paketId  = (int)($_POST['paket_id'] ?? 0);
$tanggal  = trim($_POST['tanggal_pendakian'] ?? '');
$jumlah   = (int)($_POST['jumlah_peserta'] ?? 1);

/*
|--------------------------------------------------------------------------
| VALIDASI DASAR
|--------------------------------------------------------------------------
*/

if ($userId <= 0) {
    set_toast('error', 'Sesi pengguna tidak ditemukan. Silakan login kembali.');
    redirect('/login.php');
}

if ($gunungId <= 0 || $paketId <= 0 || $tanggal === '') {
    set_toast('error', 'Data booking belum lengkap.');
    redirect('/search.php');
}

/*
|--------------------------------------------------------------------------
| BATASI JUMLAH PESERTA
|--------------------------------------------------------------------------
*/

$jumlah = max(1, min($jumlah, 15));

/*
|--------------------------------------------------------------------------
| VALIDASI TANGGAL
|--------------------------------------------------------------------------
|
| Pendakian minimal H+1 dari hari ini.
|
*/

$tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);

if (
    !$tanggalObj ||
    $tanggalObj->format('Y-m-d') !== $tanggal ||
    $tanggalObj < new DateTime('tomorrow')
) {
    set_toast('error', 'Tanggal pendakian tidak valid. Pilih tanggal setelah hari ini.');
    redirect('/booking.php?gunung_id=' . $gunungId);
}

/*
|--------------------------------------------------------------------------
| CEK GUNUNG
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM gunung
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$gunungId]);
$gunung = $stmt->fetch();

if (!$gunung) {
    set_toast('error', 'Gunung tidak ditemukan.');
    redirect('/search.php');
}

/*
|--------------------------------------------------------------------------
| CEK STATUS GUNUNG
|--------------------------------------------------------------------------
*/

if ($gunung['status'] !== 'buka') {
    set_toast('error', 'Gunung sedang ditutup dan tidak dapat dipesan.');
    redirect('/search.php');
}

/*
|--------------------------------------------------------------------------
| VALIDASI PAKET
|--------------------------------------------------------------------------
|
| Paket harus benar-benar milik gunung yang dipilih.
|
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM paket
    WHERE id = ?
      AND gunung_id = ?
    LIMIT 1
");

$stmt->execute([$paketId, $gunungId]);
$paket = $stmt->fetch();

if (!$paket) {
    set_toast('error', 'Paket pendakian tidak valid.');
    redirect('/booking.php?gunung_id=' . $gunungId);
}

/*
|--------------------------------------------------------------------------
| AMBIL HARGA PAKET
|--------------------------------------------------------------------------
*/

$hargaPaket = (float)$paket['harga'];
$totalHarga = $hargaPaket * $jumlah;

/*
|--------------------------------------------------------------------------
| GENERATE KODE BOOKING
|--------------------------------------------------------------------------
*/

$kodeBooking = generate_kode_booking();

/*
|--------------------------------------------------------------------------
| QR CODE DATA
|--------------------------------------------------------------------------
|
| Untuk sementara QR berisi kode booking.
| QR akan digunakan kembali pada halaman E-Tiket.
|
*/

$qrData = $kodeBooking;

/*
|--------------------------------------------------------------------------
| SIMPAN BOOKING
|--------------------------------------------------------------------------
|
| PENTING:
| status_pembayaran sekarang = 'menunggu'
|
| Jadi user belum dianggap membayar.
|
*/

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO booking (
            kode_booking,
            user_id,
            gunung_id,
            paket_id,
            tanggal_pendakian,
            jumlah_peserta,
            total_harga,
            metode_pembayaran,
            status_pembayaran,
            status_pendakian,
            qr_code_data
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            NULL,
            'menunggu',
            'akan_datang',
            ?
        )
    ");

    $stmt->execute([
        $kodeBooking,
        $userId,
        $gunungId,
        $paketId,
        $tanggal,
        $jumlah,
        $totalHarga,
        $qrData
    ]);

    /*
    |--------------------------------------------------------------------------
    | LOG AKTIVITAS
    |--------------------------------------------------------------------------
    */

    log_activity(
        $pdo,
        $userId,
        "Booking baru: $kodeBooking - {$gunung['nama_gunung']}"
    );

    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    /*
    |--------------------------------------------------------------------------
    | ARAHKAN KE HALAMAN PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    set_toast(
        'success',
        'Booking berhasil dibuat. Silakan lanjutkan ke pembayaran.'
    );

    redirect(
        '/payment.php?kode=' . urlencode($kodeBooking)
    );

} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK JIKA ERROR
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    /*
    |--------------------------------------------------------------------------
    | LOG ERROR
    |--------------------------------------------------------------------------
    |
    | Tidak menampilkan detail error database kepada user.
    |
    */

    error_log(
        'Gagal membuat booking ' . $kodeBooking . ': ' . $e->getMessage()
    );

    set_toast(
        'error',
        'Gagal membuat booking. Silakan coba lagi.'
    );

    redirect(
        '/booking.php?gunung_id=' . $gunungId
    );
}