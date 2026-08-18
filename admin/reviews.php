```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

/*
|--------------------------------------------------------------------------
| AMBIL DATA REVIEW
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        r.*,
        u.nama_lengkap,
        u.email,
        g.nama_gunung,
        b.kode_booking
    FROM review r
    JOIN users u ON u.id = r.user_id
    JOIN gunung g ON g.id = r.gunung_id
    JOIN booking b ON b.id = r.booking_id
    ORDER BY r.created_at DESC
");

$reviews = $stmt->fetchAll();


$page_title = 'Kelola Review - Admin Muncak.Kuy';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dash-layout">

    <?php
    $active = 'review';
    require __DIR__ . '/sidebar_admin.php';
    ?>

    <main class="dash-main">

        <div class="dash-topbar">
            <h1>Kelola Review</h1>
        </div>


        <div class="panel reveal">

            <div class="panel-head">

                <div>
                    <h3>Review Pengguna</h3>

                    <p class="text-muted" style="margin-top:5px;">
                        Lihat penilaian dan komentar dari pengguna
                        setelah melakukan pendakian.
                    </p>
                </div>

            </div>


            <?php if (empty($reviews)): ?>

                <div
                    style="
                    text-align:center;
                    padding:40px 20px;
                    "
                >

                    <div
                        style="
                        font-size:42px;
                        margin-bottom:10px;
                        "
                    >
                        ⭐
                    </div>

                    <h3>
                        Belum Ada Review
                    </h3>

                    <p class="text-muted">
                        Belum ada pengguna yang memberikan review.
                    </p>

                </div>


            <?php else: ?>

                <div style="overflow-x:auto;">

                    <table class="data-table">

                        <thead>

                            <tr>
                                <th>Pengguna</th>
                                <th>Gunung</th>
                                <th>Kode Booking</th>
                                <th>Rating</th>
                                <th>Komentar</th>
                                <th>Tanggal</th>
                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($reviews as $r): ?>

                            <tr>

                                <!-- PENGGUNA -->

                                <td>

                                    <strong>
                                        <?= clean($r['nama_lengkap']) ?>
                                    </strong>

                                    <div
                                        class="text-muted"
                                        style="
                                        font-size:12px;
                                        margin-top:3px;
                                        "
                                    >
                                        <?= clean($r['email']) ?>
                                    </div>

                                </td>


                                <!-- GUNUNG -->

                                <td>
                                    <?= clean($r['nama_gunung']) ?>
                                </td>


                                <!-- KODE BOOKING -->

                                <td>

                                    <strong>
                                        <?= clean($r['kode_booking']) ?>
                                    </strong>

                                </td>


                                <!-- RATING -->

                                <td>

                                    <span
                                        style="
                                        font-size:18px;
                                        white-space:nowrap;
                                        "
                                    >

                                        <?php for ($i = 1; $i <= 5; $i++): ?>

                                            <?= $i <= (int)$r['rating'] ? '⭐' : '☆' ?>

                                        <?php endfor; ?>

                                    </span>

                                    <div
                                        class="text-muted"
                                        style="
                                        font-size:12px;
                                        margin-top:3px;
                                        "
                                    >
                                        <?= (int)$r['rating'] ?>/5
                                    </div>

                                </td>


                                <!-- KOMENTAR -->

                                <td style="max-width:350px;">

                                    <?= nl2br(clean($r['komentar'])) ?>

                                </td>


                                <!-- TANGGAL -->

                                <td>

                                    <?= tanggal_indo($r['created_at']) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```
