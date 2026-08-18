```php
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| AMBIL DATA BOOKING
|--------------------------------------------------------------------------
|
| Sekalian cek apakah booking tersebut sudah memiliki review.
|
*/

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        g.nama_gunung,
        g.lokasi,
        p.nama_paket,
        r.id AS review_id,
        r.rating AS review_rating
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    LEFT JOIN review r 
        ON r.booking_id = b.id
        AND r.user_id = b.user_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");

$stmt->execute([$userId]);

$bookings = $stmt->fetchAll();


$page_title = 'Booking Saya - Muncak.Kuy';

require_once __DIR__ . '/includes/header.php';
?>

<div class="dash-layout" style="padding-top:0;">

  <?php
  $active = 'booking';
  require __DIR__ . '/includes/sidebar_user.php';
  ?>

  <main class="dash-main">

    <div class="dash-topbar">
      <h1>Booking Saya</h1>
    </div>


    <div class="panel reveal">

      <?php if (empty($bookings)): ?>

        <p class="text-muted">
          Belum ada booking.

          <a href="<?= BASE_URL ?>/search.php">
            Cari gunung sekarang &rarr;
          </a>
        </p>


      <?php else: ?>

        <div style="overflow-x:auto;">

          <table class="data-table">

            <thead>

              <tr>
                <th>Kode</th>
                <th>Gunung</th>
                <th>Tanggal</th>
                <th>Paket</th>
                <th>Peserta</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>

            </thead>


            <tbody>

            <?php foreach ($bookings as $b):

              $map = [
                'akan_datang' => ['info', 'Akan Datang'],
                'berlangsung' => ['warning', 'Berlangsung'],
                'selesai' => ['success', 'Selesai'],
                'batal' => ['danger', 'Batal']
              ];

              [$cls, $lbl] = $map[$b['status_pendakian']] ?? ['info', 'Tidak Diketahui'];

            ?>

              <tr>

                <!-- KODE BOOKING -->

                <td>
                  <strong>
                    <?= clean($b['kode_booking']) ?>
                  </strong>
                </td>


                <!-- GUNUNG -->

                <td>
                  <?= clean($b['nama_gunung']) ?>
                </td>


                <!-- TANGGAL -->

                <td>
                  <?= tanggal_indo($b['tanggal_pendakian']) ?>
                </td>


                <!-- PAKET -->

                <td>
                  <?= clean($b['nama_paket']) ?>
                </td>


                <!-- PESERTA -->

                <td>
                  <?= (int)$b['jumlah_peserta'] ?>
                </td>


                <!-- TOTAL -->

                <td>
                  <?= rupiah($b['total_harga']) ?>
                </td>


                <!-- STATUS -->

                <td>

                  <span class="status-pill <?= $cls ?>">
                    <?= $lbl ?>
                  </span>

                </td>


                <!-- AKSI -->

                <td>

                  <div
                    style="
                    display:flex;
                    gap:6px;
                    flex-wrap:wrap;
                    "
                  >

                    <!-- DETAIL -->

                    <a
                      href="<?= BASE_URL ?>/detail_booking.php?kode=<?= urlencode($b['kode_booking']) ?>"
                      class="btn btn-sm btn-dark"
                    >
                      Detail
                    </a>


                    <?php if ($b['status_pendakian'] === 'selesai'): ?>


                      <?php if (!empty($b['review_id'])): ?>

                        <!-- SUDAH REVIEW -->

                        <span
                          class="btn btn-sm btn-outline"
                          style="
                          cursor:default;
                          opacity:.8;
                          "
                        >
                          ✓ Sudah Direview
                        </span>


                      <?php else: ?>

                        <!-- BELUM REVIEW -->

                        <a
                          href="<?= BASE_URL ?>/review.php?kode=<?= urlencode($b['kode_booking']) ?>"
                          class="btn btn-sm btn-primary"
                        >
                          ⭐ Beri Review
                        </a>

                      <?php endif; ?>


                    <?php endif; ?>

                  </div>

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


<?php require_once __DIR__ . '/includes/footer.php'; ?>
```
