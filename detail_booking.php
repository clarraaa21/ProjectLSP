<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];
$kode   = trim($_GET['kode'] ?? '');

if ($kode === '') {
    set_toast('error', 'Kode booking tidak ditemukan.');
    redirect('/booking_saya.php');
}

/*
|--------------------------------------------------------------------------
| Ambil Detail Booking
|--------------------------------------------------------------------------
| User hanya boleh melihat booking miliknya sendiri.
| Admin tidak menggunakan halaman ini karena dashboard admin memiliki
| halaman pengelolaan booking sendiri.
*/
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        g.nama_gunung,
        g.lokasi,
        g.provinsi,
        g.ketinggian,
        g.tingkat_kesulitan,
        g.deskripsi,
        g.foto_utama,
        p.nama_paket,
        p.harga AS harga_paket,
        u.nama_lengkap
    FROM booking b
    JOIN gunung g ON g.id = b.gunung_id
    JOIN paket p ON p.id = b.paket_id
    JOIN users u ON u.id = b.user_id
    WHERE b.kode_booking = ?
      AND b.user_id = ?
    LIMIT 1
");

$stmt->execute([$kode, $userId]);
$booking = $stmt->fetch();

if (!$booking) {
    set_toast('error', 'Booking tidak ditemukan atau bukan milik kamu.');
    redirect('/booking_saya.php');
}

/*
|--------------------------------------------------------------------------
| Status Pendakian
|--------------------------------------------------------------------------
*/
$statusPendakian = [
    'akan_datang' => [
        'class' => 'info',
        'label' => 'Akan Datang'
    ],
    'berlangsung' => [
        'class' => 'warning',
        'label' => 'Berlangsung'
    ],
    'selesai' => [
        'class' => 'success',
        'label' => 'Selesai'
    ],
    'batal' => [
        'class' => 'danger',
        'label' => 'Batal'
    ]
];

$statusKey = $booking['status_pendakian'];

if (isset($statusPendakian[$statusKey])) {
    $statusPendakianClass = $statusPendakian[$statusKey]['class'];
    $statusPendakianLabel = $statusPendakian[$statusKey]['label'];
} else {
    $statusPendakianClass = 'info';
    $statusPendakianLabel = ucfirst(str_replace('_', ' ', $statusKey));
}

/*
|--------------------------------------------------------------------------
| Status Pembayaran
|--------------------------------------------------------------------------
*/
$statusPembayaran = [
    'menunggu' => [
        'class' => 'warning',
        'label' => 'Menunggu Pembayaran'
    ],
    'lunas' => [
        'class' => 'success',
        'label' => 'Lunas'
    ],
    'gagal' => [
        'class' => 'danger',
        'label' => 'Gagal'
    ],
    'refund' => [
        'class' => 'danger',
        'label' => 'Refund'
    ]
];

$statusBayarKey = $booking['status_pembayaran'];

if (isset($statusPembayaran[$statusBayarKey])) {
    $statusBayarClass = $statusPembayaran[$statusBayarKey]['class'];
    $statusBayarLabel = $statusPembayaran[$statusBayarKey]['label'];
} else {
    $statusBayarClass = 'warning';
    $statusBayarLabel = ucfirst(str_replace('_', ' ', $statusBayarKey));
}

/*
|--------------------------------------------------------------------------
| Foto Gunung
|--------------------------------------------------------------------------
*/
$fotoGunung = '';

if (!empty($booking['foto_utama'])) {
    $fotoGunung = BASE_URL . '/uploads/gunung/' . clean($booking['foto_utama']);
} else {
    $fotoGunung = 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=1200&auto=format&fit=crop';
}

$page_title = 'Detail Booking ' . $booking['kode_booking'] . ' - Muncak.Kuy';

require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:130px;">
  <div class="container">

    <!-- Tombol Kembali -->
    <div class="back-button-wrap" style="margin-bottom:25px;">
      <a href="<?= BASE_URL ?>/booking_saya.php" class="btn btn-outline btn-sm">
        ← Kembali ke Booking Saya
      </a>
    </div>

    <!-- Header -->
    <div class="section-head reveal" style="margin-bottom:30px;">
      <span class="eyebrow">Detail Booking</span>
      <h2><?= clean($booking['nama_gunung']) ?></h2>
      <p>
        Berikut adalah informasi lengkap booking pendakian kamu.
      </p>
    </div>

    <div class="booking-detail-grid">

      <!-- =========================================================
           KIRI: INFORMASI GUNUNG
      ========================================================== -->
      <div class="summary-card reveal">

        <div class="img-wrap" style="height:280px;">
          <img
            src="<?= $fotoGunung ?>"
            alt="<?= clean($booking['nama_gunung']) ?>"
            style="width:100%;height:100%;object-fit:cover;"
            onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=1200&auto=format&fit=crop'"
          >
        </div>

        <div class="content">

          <h3 style="margin-bottom:8px;">
            <?= clean($booking['nama_gunung']) ?>
          </h3>

          <p class="text-muted" style="margin-bottom:18px;">
            <?= clean($booking['lokasi']) ?>,
            <?= clean($booking['provinsi']) ?>
            &middot;
            <?= (int)$booking['ketinggian'] ?> mdpl
          </p>

          <!-- Informasi Gunung -->
          <div style="
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:12px;
            margin-bottom:20px;
          ">

            <div style="
              padding:14px;
              background:var(--surface-2,#f7f7f7);
              border-radius:12px;
            ">
              <div style="
                font-size:12px;
                color:var(--ink-400);
                margin-bottom:5px;
              ">
                Tingkat Kesulitan
              </div>

              <strong>
                <?= clean($booking['tingkat_kesulitan']) ?>
              </strong>
            </div>

            <div style="
              padding:14px;
              background:var(--surface-2,#f7f7f7);
              border-radius:12px;
            ">
              <div style="
                font-size:12px;
                color:var(--ink-400);
                margin-bottom:5px;
              ">
                Ketinggian
              </div>

              <strong>
                <?= (int)$booking['ketinggian'] ?> mdpl
              </strong>
            </div>

          </div>

          <?php if (!empty($booking['deskripsi'])): ?>

            <div style="margin-top:10px;">

              <h4 style="margin-bottom:8px;">
                Tentang Gunung
              </h4>

              <p style="
                font-size:14px;
                line-height:1.8;
                color:var(--ink-600);
                margin:0;
              ">
                <?= nl2br(clean($booking['deskripsi'])) ?>
              </p>

            </div>

          <?php endif; ?>

        </div>

      </div>


      <!-- =========================================================
           KANAN: DETAIL BOOKING
      ========================================================== -->
      <div class="summary-card reveal">

        <div class="content">

          <!-- Kode Booking -->
          <div style="
            background:var(--ink-900,#0d3320);
            color:white;
            border-radius:14px;
            padding:20px;
            margin-bottom:24px;
          ">

            <div style="
              font-size:12px;
              opacity:.75;
              margin-bottom:6px;
            ">
              Kode Booking
            </div>

            <div style="
              font-size:22px;
              font-weight:700;
              letter-spacing:1px;
            ">
              <?= clean($booking['kode_booking']) ?>
            </div>

          </div>


          <!-- Status -->
          <div style="
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:14px;
            margin-bottom:24px;
          ">

            <div style="
              padding:16px;
              border:1px solid var(--border,#e5e5e5);
              border-radius:12px;
            ">

              <div style="
                font-size:12px;
                color:var(--ink-400);
                margin-bottom:8px;
              ">
                Status Pendakian
              </div>

              <span class="status-pill <?= $statusPendakianClass ?>">
                <?= $statusPendakianLabel ?>
              </span>

            </div>

            <div style="
              padding:16px;
              border:1px solid var(--border,#e5e5e5);
              border-radius:12px;
            ">

              <div style="
                font-size:12px;
                color:var(--ink-400);
                margin-bottom:8px;
              ">
                Status Pembayaran
              </div>

              <span class="status-pill <?= $statusBayarClass ?>">
                <?= $statusBayarLabel ?>
              </span>

            </div>

          </div>


          <!-- Detail Booking -->
          <h3 style="
            font-size:18px;
            margin-bottom:18px;
          ">
            Informasi Booking
          </h3>

          <div class="detail-list">

            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Nama Pemesan
              </span>

              <strong style="text-align:right;">
                <?= clean($booking['nama_lengkap']) ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Gunung
              </span>

              <strong style="text-align:right;">
                <?= clean($booking['nama_gunung']) ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Lokasi
              </span>

              <strong style="text-align:right;">
                <?= clean($booking['lokasi']) ?>,
                <?= clean($booking['provinsi']) ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Tanggal Pendakian
              </span>

              <strong style="text-align:right;">
                <?= tanggal_indo($booking['tanggal_pendakian']) ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Paket
              </span>

              <strong style="text-align:right;">
                <?= clean($booking['nama_paket']) ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Jumlah Peserta
              </span>

              <strong style="text-align:right;">
                <?= (int)$booking['jumlah_peserta'] ?> Orang
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Harga Paket
              </span>

              <strong style="text-align:right;">
                <?= rupiah($booking['harga_paket']) ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Metode Pembayaran
              </span>

              <strong style="text-align:right;">
                <?= !empty($booking['metode_pembayaran'])
                    ? clean($booking['metode_pembayaran'])
                    : 'Belum ditentukan' ?>
              </strong>
            </div>


            <div class="row" style="
              display:flex;
              justify-content:space-between;
              gap:20px;
              padding:12px 0;
              border-bottom:1px solid var(--border,#eee);
            ">
              <span class="text-muted">
                Booking Dibuat
              </span>

              <strong style="text-align:right;">
                <?= !empty($booking['created_at'])
                    ? date('d/m/Y H:i', strtotime($booking['created_at']))
                    : '-' ?>
              </strong>
            </div>


            <?php if (!empty($booking['catatan'])): ?>

              <div class="row" style="
                display:flex;
                justify-content:space-between;
                gap:20px;
                padding:12px 0;
                border-bottom:1px solid var(--border,#eee);
              ">

                <span class="text-muted">
                  Catatan
                </span>

                <strong style="
                  text-align:right;
                  max-width:60%;
                  line-height:1.6;
                ">
                  <?= nl2br(clean($booking['catatan'])) ?>
                </strong>

              </div>

            <?php endif; ?>

          </div>


          <!-- Total -->
          <div class="total-box" style="
            margin-top:22px;
            display:flex;
            justify-content:space-between;
            align-items:center;
          ">

            <span class="label">
              Total Pembayaran
            </span>

            <span class="amount">
              <?= rupiah($booking['total_harga']) ?>
            </span>

          </div>


          <!-- Tombol -->
          <div style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-top:22px;
          ">

            <a
              href="<?= BASE_URL ?>/eticket.php?kode=<?= urlencode($booking['kode_booking']) ?>"
              class="btn btn-dark"
            >
              🎫 Lihat E-Tiket
            </a>

            <a
              href="<?= BASE_URL ?>/booking_saya.php"
              class="btn btn-outline"
            >
              ← Booking Saya
            </a>

          </div>

        </div>

      </div>

    </div>

  </div>
</section>


<style>
/* =========================================================
   DETAIL BOOKING
========================================================= */

.booking-detail-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 24px;
  align-items: start;
}

.booking-detail-grid .summary-card {
  height: 100%;
}

.booking-detail-grid .summary-card .img-wrap {
  overflow: hidden;
  border-radius: 14px 14px 0 0;
}

@media (max-width: 900px) {
  .booking-detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {

  .booking-detail-grid {
    gap: 16px;
  }

  .booking-detail-grid [style*="grid-template-columns:repeat(2"] {
    grid-template-columns: 1fr !important;
  }

  .booking-detail-grid .row {
    flex-direction: column;
    gap: 5px !important;
  }

  .booking-detail-grid .row strong {
    text-align: left !important;
    max-width: 100% !important;
  }

}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>