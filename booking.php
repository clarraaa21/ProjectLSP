<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$gunungId = (int)($_GET['gunung_id'] ?? 0);

/* =========================================================
   AMBIL DATA GUNUNG
   ========================================================= */
$stmt = $pdo->prepare("SELECT * FROM gunung WHERE id=?");
$stmt->execute([$gunungId]);
$gunung = $stmt->fetch();

if (!$gunung) {
    set_toast('error', 'Gunung tidak ditemukan.');
    redirect('/search.php');
}

/* =========================================================
   AMBIL DATA PAKET
   ========================================================= */
$stmtPaket = $pdo->prepare("SELECT * FROM paket WHERE gunung_id=? ORDER BY harga ASC");
$stmtPaket->execute([$gunungId]);
$paketList = $stmtPaket->fetchAll();

/* =========================================================
   AMBIL FOTO GUNUNG
   ========================================================= */
$stmtFoto = $pdo->prepare("
    SELECT foto
    FROM gunung_foto
    WHERE gunung_id=?
    ORDER BY urutan
    LIMIT 4
");
$stmtFoto->execute([$gunungId]);
$fotoList = $stmtFoto->fetchAll(PDO::FETCH_COLUMN);

/* =========================================================
   DATA TAMBAHAN
   ========================================================= */
$deskripsi = trim($gunung['deskripsi'] ?? '');
$estimasiWaktu = trim($gunung['estimasi_waktu'] ?? '');

$page_title = 'Booking ' . $gunung['nama_gunung'] . ' - Muncak.Kuy';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:130px;">
  <div class="container">

    <!-- =====================================================
         TOMBOL KEMBALI
         ===================================================== -->
    <div class="back-button-wrap" style="margin-bottom:24px;">
      <a href="<?= BASE_URL ?>/search.php" class="btn btn-outline btn-sm">
        ← Kembali
      </a>
    </div>


    <!-- =====================================================
         STEPPER BOOKING
         ===================================================== -->
    <div class="stepper reveal">

      <div class="step-item active">
        <span class="circle">1</span>
        Pilih Tanggal
      </div>

      <div class="step-divider"></div>

      <div class="step-item">
        <span class="circle">2</span>
        Paket &amp; Fasilitas
      </div>

      <div class="step-divider"></div>

      <div class="step-item">
        <span class="circle">3</span>
        Data Diri
      </div>

      <div class="step-divider"></div>

      <div class="step-item">
        <span class="circle">4</span>
        Pembayaran
      </div>

    </div>


    <!-- =====================================================
         FORM BOOKING
         ===================================================== -->
    <form
      method="POST"
      action="<?= BASE_URL ?>/process_booking.php"
      id="bookingForm"
      class="booking-grid"
    >

      <input
        type="hidden"
        name="csrf_token"
        value="<?= csrf_token() ?>"
      >

      <input
        type="hidden"
        name="gunung_id"
        value="<?= (int)$gunung['id'] ?>"
      >


      <!-- ===================================================
           KIRI — DETAIL GUNUNG
           =================================================== -->
      <div class="summary-card reveal">

        <!-- FOTO UTAMA -->
        <div
          class="img-wrap"
          style="height:280px;"
        >

          <img
            src="<?= $gunung['foto_utama']
              ? BASE_URL . '/uploads/gunung/' . clean($gunung['foto_utama'])
              : 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=1200&auto=format&fit=crop'
            ?>"
            alt="<?= clean($gunung['nama_gunung']) ?>"
            style="width:100%;height:100%;object-fit:cover;"
            onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=1200&auto=format&fit=crop'"
          >

        </div>


        <!-- KONTEN DETAIL -->
        <div class="content">

          <!-- NAMA GUNUNG -->
          <h2 style="font-size:26px;margin-bottom:6px;">
            <?= clean($gunung['nama_gunung']) ?>
          </h2>


          <!-- LOKASI -->
          <div
            style="
              display:flex;
              align-items:center;
              gap:6px;
              color:var(--ink-600);
              font-size:14px;
              margin-bottom:16px;
            "
          >

            <svg
              width="15"
              height="15"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>

            <?= clean($gunung['lokasi']) ?>,
            <?= clean($gunung['provinsi']) ?>

          </div>


          <!-- =================================================
               INFORMASI SINGKAT
               ================================================= -->
          <div
            style="
              display:grid;
              grid-template-columns:repeat(3,1fr);
              gap:10px;
              margin-bottom:24px;
            "
          >

            <!-- KETINGGIAN -->
            <div
              style="
                background:var(--cream-50);
                border-radius:12px;
                padding:14px;
                text-align:center;
                border:1px solid #eee8d9;
              "
            >

              <div
                style="
                  font-size:11px;
                  color:var(--ink-400);
                  margin-bottom:4px;
                "
              >
                Ketinggian
              </div>

              <div
                style="
                  font-size:15px;
                  font-weight:800;
                  color:var(--forest-800);
                "
              >
                <?= number_format((int)$gunung['ketinggian'], 0, ',', '.') ?>
                mdpl
              </div>

            </div>


            <!-- TINGKAT KESULITAN -->
            <div
              style="
                background:var(--cream-50);
                border-radius:12px;
                padding:14px;
                text-align:center;
                border:1px solid #eee8d9;
              "
            >

              <div
                style="
                  font-size:11px;
                  color:var(--ink-400);
                  margin-bottom:4px;
                "
              >
                Kesulitan
              </div>

              <div
                style="
                  font-size:15px;
                  font-weight:800;
                  color:var(--forest-800);
                "
              >
                <?= clean($gunung['tingkat_kesulitan']) ?>
              </div>

            </div>


            <!-- ESTIMASI -->
            <div
              style="
                background:var(--cream-50);
                border-radius:12px;
                padding:14px;
                text-align:center;
                border:1px solid #eee8d9;
              "
            >

              <div
                style="
                  font-size:11px;
                  color:var(--ink-400);
                  margin-bottom:4px;
                "
              >
                Estimasi
              </div>

              <div
                style="
                  font-size:15px;
                  font-weight:800;
                  color:var(--forest-800);
                "
              >
                <?= $estimasiWaktu !== ''
                    ? clean($estimasiWaktu)
                    : 'Tidak tersedia'
                ?>
              </div>

            </div>

          </div>


          <!-- =================================================
               RATING
               ================================================= -->
          <div
            style="
              display:flex;
              align-items:center;
              gap:8px;
              margin-bottom:22px;
            "
          >

            <div
              style="
                display:flex;
                align-items:center;
                gap:5px;
                color:var(--gold-600);
                font-weight:800;
                font-size:15px;
              "
            >

              <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>
              </svg>

              <?= number_format((float)$gunung['rating'], 1) ?>

            </div>

            <span
              style="
                color:var(--ink-400);
                font-size:13px;
              "
            >
              (<?= (int)$gunung['jumlah_review'] ?> review)
            </span>

          </div>


          <!-- =================================================
               TENTANG GUNUNG
               ================================================= -->
          <div style="margin-bottom:26px;">

            <h3
              style="
                font-size:18px;
                margin-bottom:10px;
              "
            >
              Tentang Gunung
            </h3>

            <?php if ($deskripsi !== ''): ?>

              <p
                style="
                  font-size:14px;
                  line-height:1.8;
                  color:var(--ink-600);
                  white-space:pre-line;
                "
              >
                <?= clean($deskripsi) ?>
              </p>

            <?php else: ?>

              <p
                style="
                  font-size:14px;
                  line-height:1.8;
                  color:var(--ink-400);
                "
              >
                Belum ada deskripsi mengenai gunung ini.
              </p>

            <?php endif; ?>

          </div>


          <!-- =================================================
               INFORMASI PENDAKIAN
               ================================================= -->
          <div
            style="
              background:linear-gradient(
                135deg,
                rgba(34,112,72,.08),
                rgba(242,183,5,.08)
              );
              border-radius:14px;
              padding:18px;
              margin-bottom:26px;
              border:1px solid rgba(34,112,72,.10);
            "
          >

            <h3
              style="
                font-size:17px;
                margin-bottom:12px;
              "
            >
              Informasi Pendakian
            </h3>

            <div
              style="
                display:flex;
                flex-direction:column;
                gap:10px;
                font-size:13.5px;
                color:var(--ink-600);
              "
            >

              <!-- LOKASI -->
              <div
                style="
                  display:flex;
                  justify-content:space-between;
                  gap:15px;
                "
              >
                <span>Lokasi</span>

                <strong style="color:var(--forest-800);text-align:right;">
                  <?= clean($gunung['lokasi']) ?>,
                  <?= clean($gunung['provinsi']) ?>
                </strong>
              </div>


              <!-- KETINGGIAN -->
              <div
                style="
                  display:flex;
                  justify-content:space-between;
                  gap:15px;
                "
              >
                <span>Ketinggian</span>

                <strong style="color:var(--forest-800);">
                  <?= number_format((int)$gunung['ketinggian'], 0, ',', '.') ?>
                  mdpl
                </strong>
              </div>


              <!-- KESULITAN -->
              <div
                style="
                  display:flex;
                  justify-content:space-between;
                  gap:15px;
                "
              >
                <span>Tingkat Kesulitan</span>

                <strong style="color:var(--forest-800);">
                  <?= clean($gunung['tingkat_kesulitan']) ?>
                </strong>
              </div>


              <!-- ESTIMASI -->
              <div
                style="
                  display:flex;
                  justify-content:space-between;
                  gap:15px;
                "
              >
                <span>Estimasi Pendakian</span>

                <strong style="color:var(--forest-800);">
                  <?= $estimasiWaktu !== ''
                      ? clean($estimasiWaktu)
                      : 'Tidak tersedia'
                  ?>
                </strong>
              </div>


              <!-- STATUS -->
              <div
                style="
                  display:flex;
                  justify-content:space-between;
                  gap:15px;
                "
              >
                <span>Status Pendakian</span>

                <?php if ($gunung['status'] === 'buka'): ?>

                  <strong style="color:var(--success);">
                    ● Buka
                  </strong>

                <?php else: ?>

                  <strong style="color:var(--danger);">
                    ● Tutup
                  </strong>

                <?php endif; ?>

              </div>

            </div>

          </div>


          <!-- =================================================
               FOTO TAMBAHAN
               ================================================= -->
          <?php if (!empty($fotoList)): ?>

            <div>

              <h3
                style="
                  font-size:18px;
                  margin-bottom:12px;
                "
              >
                Foto Pendakian
              </h3>

              <div
                style="
                  display:grid;
                  grid-template-columns:repeat(4,1fr);
                  gap:10px;
                "
              >

                <?php foreach ($fotoList as $f): ?>

                  <div
                    style="
                      height:75px;
                      border-radius:10px;
                      overflow:hidden;
                      background:var(--cream-100);
                    "
                  >

                    <img
                      src="<?= BASE_URL ?>/assets/img/gunung/<?= clean($f) ?>"
                      alt="<?= clean($gunung['nama_gunung']) ?>"
                      style="
                        width:100%;
                        height:100%;
                        object-fit:cover;
                      "
                      onerror="this.parentElement.style.display='none'"
                    >

                  </div>

                <?php endforeach; ?>

              </div>

            </div>

          <?php endif; ?>

        </div>

      </div>


      <!-- ===================================================
           KANAN — FORM BOOKING
           =================================================== -->
      <div
        class="summary-card reveal"
        style="position:sticky;top:100px;"
      >

        <div class="content">

          <h3
            style="
              font-size:20px;
              margin-bottom:6px;
            "
          >
            Booking Pendakian
          </h3>

          <p
            style="
              font-size:13px;
              color:var(--ink-600);
              margin-bottom:24px;
            "
          >
            Tentukan tanggal, paket, dan jumlah peserta untuk melanjutkan booking.
          </p>


          <!-- =================================================
               TANGGAL
               ================================================= -->
          <div class="form-group">

            <label>
              Tanggal Pendakian
            </label>

            <input
              type="date"
              name="tanggal_pendakian"
              class="form-control"
              min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
              required
            >

          </div>


          <!-- =================================================
               PAKET
               ================================================= -->
          <div class="form-group">

            <label>
              Paket Pendakian
            </label>

            <?php if (!empty($paketList)): ?>

              <select
                name="paket_id"
                id="paketSelect"
                class="form-control"
                required
              >

                <?php foreach ($paketList as $p): ?>

                  <option
                    value="<?= (int)$p['id'] ?>"
                    data-harga="<?= (float)$p['harga'] ?>"
                  >
                    <?= clean($p['nama_paket']) ?>
                    —
                    <?= rupiah($p['harga']) ?>
                  </option>

                <?php endforeach; ?>

              </select>

            <?php else: ?>

              <div
                style="
                  background:#fbe9ea;
                  color:var(--danger);
                  padding:12px;
                  border-radius:10px;
                  font-size:13px;
                "
              >
                Belum ada paket pendakian yang tersedia untuk gunung ini.
              </div>

            <?php endif; ?>

          </div>


          <!-- =================================================
               JUMLAH PESERTA
               ================================================= -->
          <div class="form-group">

            <label>
              Jumlah Peserta
            </label>

            <div class="qty-control">

              <button
                type="button"
                class="qty-min"
                id="qtyMin"
              >
                −
              </button>

              <input
                type="number"
                name="jumlah_peserta"
                id="jumlahPeserta"
                value="2"
                min="1"
                max="15"
                class="form-control"
                style="
                  text-align:center;
                  width:70px;
                "
                readonly
              >

              <button
                type="button"
                class="qty-plus"
                id="qtyPlus"
              >
                +
              </button>

            </div>

            <small
              style="
                display:block;
                margin-top:8px;
                color:var(--ink-400);
                font-size:11.5px;
              "
            >
              Maksimal 15 peserta per booking.
            </small>

          </div>


          <!-- =================================================
               RINGKASAN
               ================================================= -->
          <div
            style="
              background:var(--cream-50);
              border-radius:12px;
              padding:15px;
              margin-top:20px;
              margin-bottom:16px;
            "
          >

            <div
              style="
                display:flex;
                justify-content:space-between;
                margin-bottom:8px;
                font-size:13px;
                color:var(--ink-600);
              "
            >

              <span>
                Harga per peserta
              </span>

              <strong
                id="hargaPerPeserta"
                style="color:var(--forest-800);"
              >
                <?= rupiah($paketList[0]['harga'] ?? 0) ?>
              </strong>

            </div>


            <div
              style="
                display:flex;
                justify-content:space-between;
                font-size:13px;
                color:var(--ink-600);
              "
            >

              <span>
                Jumlah peserta
              </span>

              <strong
                id="jumlahRingkasan"
                style="color:var(--forest-800);"
              >
                2 orang
              </strong>

            </div>

          </div>


          <!-- =================================================
               TOTAL
               ================================================= -->
          <div class="total-box">

            <span class="label">
              Total Harga
            </span>

            <span
              class="amount"
              id="totalHarga"
            >
              <?= rupiah($paketList[0]['harga'] ?? 0) ?>
            </span>

          </div>


          <!-- =================================================
               BUTTON
               ================================================= -->
          <button
            type="submit"
            class="btn btn-primary btn-block"
            style="margin-top:20px;"
            <?= empty($paketList) ? 'disabled' : '' ?>
          >
            Lanjutkan
          </button>


          <p
            style="
              text-align:center;
              font-size:11.5px;
              color:var(--ink-400);
              margin-top:12px;
            "
          >
            Pastikan data booking sudah benar sebelum melanjutkan.
          </p>

        </div>

      </div>

    </form>

  </div>
</section>


<!-- =========================================================
     JAVASCRIPT BOOKING
     ========================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {

  const paketSelect = document.getElementById('paketSelect');
  const jumlahInput = document.getElementById('jumlahPeserta');
  const totalEl = document.getElementById('totalHarga');
  const hargaPerPesertaEl = document.getElementById('hargaPerPeserta');
  const jumlahRingkasanEl = document.getElementById('jumlahRingkasan');

  const qtyMin = document.getElementById('qtyMin');
  const qtyPlus = document.getElementById('qtyPlus');


  function formatRupiah(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
  }


  function hitungTotal() {

    if (!paketSelect || !jumlahInput || !totalEl) {
      return;
    }

    const selectedOption = paketSelect.selectedOptions[0];

    if (!selectedOption) {
      totalEl.textContent = formatRupiah(0);

      if (hargaPerPesertaEl) {
        hargaPerPesertaEl.textContent = formatRupiah(0);
      }

      return;
    }

    const harga = parseFloat(
      selectedOption.dataset.harga || 0
    );

    const jumlah = parseInt(
      jumlahInput.value || 1
    );

    const total = harga * jumlah;


    /* Harga per peserta */
    if (hargaPerPesertaEl) {
      hargaPerPesertaEl.textContent = formatRupiah(harga);
    }


    /* Jumlah peserta */
    if (jumlahRingkasanEl) {
      jumlahRingkasanEl.textContent =
        jumlah + (jumlah === 1 ? ' orang' : ' orang');
    }


    /* Total */
    totalEl.textContent = formatRupiah(total);
  }


  /* =======================================================
     GANTI PAKET
     ======================================================= */
  if (paketSelect) {

    paketSelect.addEventListener(
      'change',
      hitungTotal
    );

  }


  /* =======================================================
     TOMBOL KURANG
     ======================================================= */
  if (qtyMin) {

    qtyMin.addEventListener('click', function () {

      let jumlah = parseInt(
        jumlahInput.value || 1
      );

      if (jumlah > 1) {
        jumlah--;
        jumlahInput.value = jumlah;
        hitungTotal();
      }

    });

  }


  /* =======================================================
     TOMBOL TAMBAH
     ======================================================= */
  if (qtyPlus) {

    qtyPlus.addEventListener('click', function () {

      let jumlah = parseInt(
        jumlahInput.value || 1
      );

      if (jumlah < 15) {
        jumlah++;
        jumlahInput.value = jumlah;
        hitungTotal();
      }

    });

  }


  /* =======================================================
     HITUNG AWAL
     ======================================================= */
  hitungTotal();

});
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>