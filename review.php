<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Ambil kode booking
|--------------------------------------------------------------------------
*/
$kodeBooking = trim($_GET['kode'] ?? '');

if ($kodeBooking === '') {
    set_toast('error', 'Kode booking tidak ditemukan.');
    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| Ambil data booking
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        g.nama_gunung,
        g.foto_utama,
        p.nama_paket
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    WHERE b.kode_booking = ?
      AND b.user_id = ?
    LIMIT 1
");

$stmt->execute([$kodeBooking, $userId]);
$booking = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Booking tidak ditemukan
|--------------------------------------------------------------------------
*/
if (!$booking) {
    set_toast('error', 'Booking tidak ditemukan.');
    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| Hanya booking selesai yang boleh direview
|--------------------------------------------------------------------------
*/
if ($booking['status_pendakian'] !== 'selesai') {
    set_toast(
        'error',
        'Review hanya dapat diberikan setelah pendakian selesai.'
    );

    redirect('/booking_saya.php');
}


/*
|--------------------------------------------------------------------------
| Cek apakah sudah pernah review
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT *
    FROM review
    WHERE booking_id = ?
      AND user_id = ?
    LIMIT 1
");

$stmt->execute([
    $booking['id'],
    $userId
]);

$reviewLama = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Simpan review
|--------------------------------------------------------------------------
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'simpan_review'
) {

    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_toast('error', 'Sesi tidak valid.');
        redirect('/review.php?kode=' . urlencode($kodeBooking));
    }

    /*
    |--------------------------------------------------------------------------
    | Jika sudah review
    |--------------------------------------------------------------------------
    */
    if ($reviewLama) {
        set_toast('error', 'Booking ini sudah memiliki review.');
        redirect('/booking_saya.php');
    }


    $rating = (int)($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validasi rating
    |--------------------------------------------------------------------------
    */
    if ($rating < 1 || $rating > 5) {
        set_toast('error', 'Rating harus antara 1 sampai 5.');
        redirect('/review.php?kode=' . urlencode($kodeBooking));
    }


    /*
    |--------------------------------------------------------------------------
    | Validasi komentar
    |--------------------------------------------------------------------------
    */
    if ($komentar === '') {
        set_toast('error', 'Komentar tidak boleh kosong.');
        redirect('/review.php?kode=' . urlencode($kodeBooking));
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan ke database
    |--------------------------------------------------------------------------
    */
    try {

        $stmt = $pdo->prepare("
            INSERT INTO review
            (
                booking_id,
                user_id,
                gunung_id,
                rating,
                komentar
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $booking['id'],
            $userId,
            $booking['gunung_id'],
            $rating,
            $komentar
        ]);


        /*
        |--------------------------------------------------------------------------
        | Update rating gunung
        |--------------------------------------------------------------------------
        |
        | Jika tabel gunung mempunyai kolom rating,
        | kita hitung ulang rating berdasarkan seluruh review.
        |
        */
        $stmtRating = $pdo->prepare("
            SELECT AVG(rating)
            FROM review
            WHERE gunung_id = ?
        ");

        $stmtRating->execute([
            $booking['gunung_id']
        ]);

        $ratingBaru = (float)$stmtRating->fetchColumn();


        /*
        |--------------------------------------------------------------------------
        | Update rating gunung
        |--------------------------------------------------------------------------
        */
        $stmtUpdate = $pdo->prepare("
            UPDATE gunung
            SET rating = ?
            WHERE id = ?
        ");

        $stmtUpdate->execute([
            $ratingBaru,
            $booking['gunung_id']
        ]);


        set_toast(
            'success',
            'Review berhasil dikirim. Terima kasih atas penilaianmu!'
        );

        redirect('/booking_saya.php');

    } catch (Exception $e) {

        set_toast(
            'error',
            'Review gagal disimpan: ' . $e->getMessage()
        );

        redirect('/review.php?kode=' . urlencode($kodeBooking));
    }
}


$page_title = 'Beri Review - Muncak.Kuy';

require_once __DIR__ . '/includes/header.php';
?>

<div class="dash-layout" style="padding-top:0;">

    <?php
    $active = 'booking';
    require __DIR__ . '/includes/sidebar_user.php';
    ?>

    <main class="dash-main">

        <div class="dash-topbar">
            <h1>Beri Review</h1>
        </div>


        <div class="panel reveal" style="max-width:760px;">

            <div class="panel-head">
                <div>
                    <h3>
                        Review Pendakian
                    </h3>

                    <p class="text-muted" style="margin-top:5px;">
                        Bagikan pengalamanmu setelah mendaki
                        <?= clean($booking['nama_gunung']) ?>.
                    </p>
                </div>
            </div>


            <!-- INFO BOOKING -->

            <div
                style="
                display:flex;
                gap:16px;
                align-items:center;
                padding:16px;
                background:#f8f6ef;
                border-radius:12px;
                margin-bottom:24px;
                "
            >

                <?php if (!empty($booking['foto_utama'])): ?>

                    <img
                        src="<?= BASE_URL ?>/uploads/gunung/<?= clean($booking['foto_utama']) ?>"
                        alt="<?= clean($booking['nama_gunung']) ?>"
                        style="
                        width:90px;
                        height:70px;
                        object-fit:cover;
                        border-radius:10px;
                        "
                        onerror="this.style.display='none'"
                    >

                <?php endif; ?>


                <div>

                    <strong style="font-size:17px;">
                        <?= clean($booking['nama_gunung']) ?>
                    </strong>

                    <div
                        class="text-muted"
                        style="margin-top:4px;"
                    >
                        <?= clean($booking['nama_paket']) ?>
                    </div>

                    <div
                        class="text-muted"
                        style="margin-top:3px;"
                    >
                        <?= tanggal_indo($booking['tanggal_pendakian']) ?>
                    </div>

                </div>

            </div>


            <?php if ($reviewLama): ?>

                <!-- SUDAH REVIEW -->

                <div
                    style="
                    padding:20px;
                    background:#f8f6ef;
                    border-radius:12px;
                    "
                >

                    <h4 style="margin-bottom:10px;">
                        Kamu sudah memberikan review
                    </h4>

                    <div
                        style="
                        font-size:24px;
                        margin-bottom:10px;
                        "
                    >
                        <?php for ($i = 1; $i <= 5; $i++): ?>

                            <?= $i <= $reviewLama['rating'] ? '⭐' : '☆' ?>

                        <?php endfor; ?>

                    </div>

                    <p>
                        <?= nl2br(clean($reviewLama['komentar'])) ?>
                    </p>

                </div>


            <?php else: ?>

                <!-- FORM REVIEW -->

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= csrf_token() ?>"
                    >

                    <input
                        type="hidden"
                        name="aksi"
                        value="simpan_review"
                    >


                    <!-- RATING -->

                    <div class="form-group">

                        <label>
                            Rating
                        </label>

                        <div
                            style="
                            display:flex;
                            gap:8px;
                            margin-top:8px;
                            "
                        >

                            <?php for ($i = 1; $i <= 5; $i++): ?>

                                <label
                                    style="
                                    cursor:pointer;
                                    font-size:30px;
                                    "
                                >

                                    <input
                                        type="radio"
                                        name="rating"
                                        value="<?= $i ?>"
                                        style="display:none;"
                                        <?= $i === 5 ? 'checked' : '' ?>
                                    >

                                    <span
                                        class="rating-star"
                                        onclick="pilihRating(<?= $i ?>)"
                                        id="star<?= $i ?>"
                                    >
                                        ☆
                                    </span>

                                </label>

                            <?php endfor; ?>

                        </div>

                        <small
                            class="text-muted"
                            id="ratingText"
                        >
                            Pilih rating
                        </small>

                    </div>


                    <!-- KOMENTAR -->

                    <div class="form-group">

                        <label>
                            Komentar
                        </label>

                        <textarea
                            name="komentar"
                            class="form-control"
                            rows="5"
                            placeholder="Ceritakan pengalamanmu saat melakukan pendakian..."
                            required
                        ></textarea>

                    </div>


                    <div
                        style="
                        display:flex;
                        gap:8px;
                        "
                    >

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            ⭐ Kirim Review
                        </button>

                        <a
                            href="<?= BASE_URL ?>/booking_saya.php"
                            class="btn btn-outline"
                        >
                            Batal
                        </a>

                    </div>

                </form>

            <?php endif; ?>

        </div>

    </main>

</div>


<script>

function pilihRating(rating) {

    const labels = [
        '',
        'Sangat Buruk',
        'Buruk',
        'Cukup',
        'Bagus',
        'Sangat Bagus'
    ];

    document.getElementById('ratingText').textContent =
        labels[rating];

    for (let i = 1; i <= 5; i++) {

        const star = document.getElementById('star' + i);

        if (i <= rating) {
            star.textContent = '⭐';
        } else {
            star.textContent = '☆';
        }
    }

    const radios = document.querySelectorAll(
        'input[name="rating"]'
    );

    radios.forEach(function(radio) {

        radio.checked =
            parseInt(radio.value) === rating;

    });
}

document.addEventListener('DOMContentLoaded', function() {
    pilihRating(5);
});

</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>