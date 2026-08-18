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
    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| VALIDASI CSRF
|--------------------------------------------------------------------------
*/

if (!csrf_verify($_POST['csrf_token'] ?? '')) {

    set_toast(
        'error',
        'Sesi tidak valid. Silakan coba lagi.'
    );

    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA
|--------------------------------------------------------------------------
*/

$userId = (int)($_SESSION['user_id'] ?? 0);

$kodeBooking = trim(
    $_POST['kode_booking'] ?? ''
);

$metodePembayaran = trim(
    $_POST['metode_pembayaran'] ?? ''
);


/*
|--------------------------------------------------------------------------
| VALIDASI USER
|--------------------------------------------------------------------------
*/

if ($userId <= 0) {

    set_toast(
        'error',
        'Sesi pengguna tidak ditemukan. Silakan login kembali.'
    );

    redirect('/login.php');
}


/*
|--------------------------------------------------------------------------
| VALIDASI KODE BOOKING
|--------------------------------------------------------------------------
*/

if ($kodeBooking === '') {

    set_toast(
        'error',
        'Kode booking tidak ditemukan.'
    );

    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| VALIDASI METODE PEMBAYARAN
|--------------------------------------------------------------------------
*/

$allowedMethods = [
    'Transfer Bank',
    'QRIS',
    'E-Wallet'
];

if (!in_array($metodePembayaran, $allowedMethods, true)) {

    set_toast(
        'error',
        'Metode pembayaran tidak valid.'
    );

    redirect(
        '/payment.php?kode=' . urlencode($kodeBooking)
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL BOOKING
|--------------------------------------------------------------------------
|
| Pastikan booking memang milik user yang sedang login.
|
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM booking
    WHERE kode_booking = ?
      AND user_id = ?
    LIMIT 1
");

$stmt->execute([
    $kodeBooking,
    $userId
]);

$booking = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| BOOKING TIDAK DITEMUKAN
|--------------------------------------------------------------------------
*/

if (!$booking) {

    set_toast(
        'error',
        'Booking tidak ditemukan.'
    );

    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| CEK STATUS PEMBAYARAN
|--------------------------------------------------------------------------
*/

if ($booking['status_pembayaran'] === 'lunas') {

    set_toast(
        'success',
        'Booking ini sudah dibayar.'
    );

    redirect(
        '/eticket.php?kode=' . urlencode($kodeBooking)
    );
}


/*
|--------------------------------------------------------------------------
| CEK STATUS BOOKING
|--------------------------------------------------------------------------
*/

if ($booking['status_pendakian'] === 'batal') {

    set_toast(
        'error',
        'Booking ini sudah dibatalkan dan tidak dapat dibayar.'
    );

    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| SIMPAN PEMBAYARAN
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS PEMBAYARAN
    |--------------------------------------------------------------------------
    |
    | Karena sistem ini belum menggunakan payment gateway,
    | konfirmasi tombol dianggap sebagai pembayaran berhasil.
    |
    */

    $stmt = $pdo->prepare("
        UPDATE booking
        SET
            metode_pembayaran = ?,
            status_pembayaran = 'lunas'
        WHERE id = ?
          AND user_id = ?
    ");

    $stmt->execute([
        $metodePembayaran,
        $booking['id'],
        $userId
    ]);


    /*
    |--------------------------------------------------------------------------
    | LOG AKTIVITAS
    |--------------------------------------------------------------------------
    */

    log_activity(
        $pdo,
        $userId,
        "Pembayaran berhasil: {$booking['kode_booking']} - {$metodePembayaran}"
    );


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | REDIRECT KE E-TIKET
    |--------------------------------------------------------------------------
    */

    set_toast(
        'success',
        'Pembayaran berhasil dikonfirmasi. E-Tiket kamu sudah tersedia.'
    );

    redirect(
        '/eticket.php?kode=' . urlencode($booking['kode_booking'])
    );


} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    /*
    |--------------------------------------------------------------------------
    | LOG ERROR
    |--------------------------------------------------------------------------
    */

    error_log(
        'Gagal memproses pembayaran ' .
        $kodeBooking .
        ': ' .
        $e->getMessage()
    );


    /*
    |--------------------------------------------------------------------------
    | PESAN ERROR
    |--------------------------------------------------------------------------
    */

    set_toast(
        'error',
        'Pembayaran gagal diproses. Silakan coba lagi.'
    );


    redirect(
        '/payment.php?kode=' . urlencode($kodeBooking)
    );
}
