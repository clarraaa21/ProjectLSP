<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$userId = (int)($_SESSION['user_id'] ?? 0);
$kodeBooking = trim($_GET['kode'] ?? '');

if ($kodeBooking === '') {
    set_toast('error', 'Kode booking tidak ditemukan.');
    redirect('/booking_saya.php');
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA BOOKING
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        b.*,
        g.nama_gunung,
        g.lokasi,
        g.foto_utama,
        p.nama_paket
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    WHERE b.kode_booking = ?
      AND b.user_id = ?
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
    set_toast('error', 'Booking tidak ditemukan.');
    redirect('/booking_saya.php');
}

/*
|--------------------------------------------------------------------------
| JIKA SUDAH LUNAS
|--------------------------------------------------------------------------
*/

if ($booking['status_pembayaran'] === 'lunas') {
    set_toast('success', 'Booking ini sudah dibayar.');

    redirect(
        '/eticket.php?kode=' . urlencode($booking['kode_booking'])
    );
}

/*
|--------------------------------------------------------------------------
| JIKA BOOKING DIBATALKAN
|--------------------------------------------------------------------------
*/

if ($booking['status_pendakian'] === 'batal') {
    set_toast('error', 'Booking ini sudah dibatalkan.');
    redirect('/booking_saya.php');
}


$page_title = 'Pembayaran - Muncak.Kuy';

require_once __DIR__ . '/includes/header.php';
?>

<div class="dash-layout" style="padding-top:0;">

    <?php
    $active = 'booking';
    require __DIR__ . '/includes/sidebar_user.php';
    ?>

    <main class="dash-main">

        <div class="dash-topbar">
            <h1>Pembayaran</h1>
        </div>


        <div
            style="
            display:grid;
            grid-template-columns:minmax(0, 1.5fr) minmax(280px, 1fr);
            gap:24px;
            align-items:start;
            "
        >

            <!-- =====================================================
                 INFORMASI PEMBAYARAN
            ====================================================== -->

            <div class="panel reveal">

                <div class="panel-head">

                    <div>
                        <h3>
                            Selesaikan Pembayaran
                        </h3>

                        <p
                            class="text-muted"
                            style="margin-top:5px;"
                        >
                            Silakan pilih metode pembayaran untuk
                            menyelesaikan booking kamu.
                        </p>
                    </div>

                </div>


                <!-- KODE BOOKING -->

                <div
                    style="
                    padding:14px 16px;
                    background:#f8f6ef;
                    border-radius:12px;
                    margin-bottom:20px;
                    "
                >

                    <div
                        class="text-muted"
                        style="font-size:12px;"
                    >
                        Kode Booking
                    </div>

                    <strong
                        style="
                        font-size:20px;
                        letter-spacing:1px;
                        "
                    >
                        <?= clean($booking['kode_booking']) ?>
                    </strong>

                </div>


                <form
                    method="POST"
                    action="<?= BASE_URL ?>/process_payment.php"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= csrf_token() ?>"
                    >

                    <input
                        type="hidden"
                        name="kode_booking"
                        value="<?= clean($booking['kode_booking']) ?>"
                    >


                    <!-- METODE PEMBAYARAN -->

                    <div class="form-group">

                        <label>
                            Metode Pembayaran
                        </label>


                        <div
                            style="
                            display:grid;
                            gap:10px;
                            margin-top:10px;
                            "
                        >

                            <!-- TRANSFER BANK -->

                            <label
                                style="
                                display:flex;
                                align-items:center;
                                gap:12px;
                                padding:15px;
                                border:1.5px solid #e3ddcc;
                                border-radius:12px;
                                cursor:pointer;
                                "
                            >

                                <input
                                    type="radio"
                                    name="metode_pembayaran"
                                    value="Transfer Bank"
                                    required
                                >

                                <div>

                                    <strong>
                                        🏦 Transfer Bank
                                    </strong>

                                    <div
                                        class="text-muted"
                                        style="
                                        font-size:12px;
                                        margin-top:3px;
                                        "
                                    >
                                        BCA / BRI / Mandiri
                                    </div>

                                </div>

                            </label>


                            <!-- QRIS -->

                            <label
                                style="
                                display:flex;
                                align-items:center;
                                gap:12px;
                                padding:15px;
                                border:1.5px solid #e3ddcc;
                                border-radius:12px;
                                cursor:pointer;
                                "
                            >

                                <input
                                    type="radio"
                                    name="metode_pembayaran"
                                    value="QRIS"
                                    required
                                >

                                <div>

                                    <strong>
                                        📱 QRIS
                                    </strong>

                                    <div
                                        class="text-muted"
                                        style="
                                        font-size:12px;
                                        margin-top:3px;
                                        "
                                    >
                                        Pembayaran melalui QRIS
                                    </div>

                                </div>

                            </label>


                            <!-- E-WALLET -->

                            <label
                                style="
                                display:flex;
                                align-items:center;
                                gap:12px;
                                padding:15px;
                                border:1.5px solid #e3ddcc;
                                border-radius:12px;
                                cursor:pointer;
                                "
                            >

                                <input
                                    type="radio"
                                    name="metode_pembayaran"
                                    value="E-Wallet"
                                    required
                                >

                                <div>

                                    <strong>
                                        💳 E-Wallet
                                    </strong>

                                    <div
                                        class="text-muted"
                                        style="
                                        font-size:12px;
                                        margin-top:3px;
                                        "
                                    >
                                        DANA / OVO / GoPay
                                    </div>

                                </div>

                            </label>

                        </div>

                    </div>


                    <!-- INFO REKENING -->

                    <div
                        style="
                        padding:16px;
                        background:#f8f6ef;
                        border-radius:12px;
                        margin-bottom:20px;
                        "
                    >

                        <strong>
                            Informasi Pembayaran
                        </strong>

                        <p
                            class="text-muted"
                            style="
                            margin:8px 0 0;
                            line-height:1.6;
                            "
                        >
                            Untuk keperluan demo, pembayaran dianggap
                            berhasil setelah kamu menekan tombol
                            <strong>Konfirmasi Pembayaran</strong>.
                        </p>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        style="width:100%;"
                    >
                        ✓ Konfirmasi Pembayaran
                    </button>


                    <a
                        href="<?= BASE_URL ?>/booking_saya.php"
                        class="btn btn-outline"
                        style="
                        width:100%;
                        margin-top:8px;
                        "
                    >
                        Kembali ke Booking Saya
                    </a>

                </form>

            </div>


            <!-- =====================================================
                 RINGKASAN BOOKING
            ====================================================== -->

            <div class="panel reveal">

                <div class="panel-head">

                    <h3>
                        Ringkasan Booking
                    </h3>

                </div>


                <?php if (!empty($booking['foto_utama'])): ?>

                    <img
                        src="<?= BASE_URL ?>/uploads/gunung/<?= clean($booking['foto_utama']) ?>"
                        alt="<?= clean($booking['nama_gunung']) ?>"
                        style="
                        width:100%;
                        height:180px;
                        object-fit:cover;
                        border-radius:12px;
                        margin-bottom:16px;
                        "
                        onerror="this.style.display='none'"
                    >

                <?php endif; ?>


                <div style="line-height:1.8;">

                    <div>

                        <span class="text-muted">
                            Gunung
                        </span>

                        <br>

                        <strong>
                            <?= clean($booking['nama_gunung']) ?>
                        </strong>

                    </div>


                    <div style="margin-top:12px;">

                        <span class="text-muted">
                            Lokasi
                        </span>

                        <br>

                        <?= clean($booking['lokasi']) ?>

                    </div>


                    <div style="margin-top:12px;">

                        <span class="text-muted">
                            Paket
                        </span>

                        <br>

                        <?= clean($booking['nama_paket']) ?>

                    </div>


                    <div style="margin-top:12px;">

                        <span class="text-muted">
                            Tanggal Pendakian
                        </span>

                        <br>

                        <?= tanggal_indo($booking['tanggal_pendakian']) ?>

                    </div>


                    <div style="margin-top:12px;">

                        <span class="text-muted">
                            Jumlah Peserta
                        </span>

                        <br>

                        <?= (int)$booking['jumlah_peserta'] ?> orang

                    </div>

                </div>


                <hr
                    style="
                    margin:20px 0;
                    border:0;
                    border-top:1px solid #e8e2d5;
                    "
                >


                <div
                    style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    "
                >

                    <span>
                        Total Pembayaran
                    </span>

                    <strong
                        style="
                        font-size:20px;
                        color:var(--primary);
                        "
                    >
                        <?= rupiah($booking['total_harga']) ?>
                    </strong>

                </div>

            </div>

        </div>

    </main>

</div>


<style>

@media (max-width: 800px) {

    .dash-main > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }

}

</style>


<?php require_once __DIR__ . '/includes/footer.php'; ?>