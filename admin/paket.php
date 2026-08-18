<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

/*
|--------------------------------------------------------------------------
| TAMBAH / EDIT PAKET
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_toast('error', 'Sesi tidak valid.');
        redirect('/admin/paket.php');
    }

    $aksi = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    $gunungId = (int)($_POST['gunung_id'] ?? 0);
    $namaPaket = trim($_POST['nama_paket'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $fasilitas = trim($_POST['fasilitas'] ?? '');
    $kuota = (int)($_POST['kuota_per_hari'] ?? 0);

    // Validasi
    if ($gunungId <= 0 || $namaPaket === '' || $harga <= 0 || $kuota <= 0) {
        set_toast('error', 'Data paket belum lengkap.');
        redirect('/admin/paket.php');
    }

    // Pastikan gunung memang ada
    $stmtGunung = $pdo->prepare("SELECT id FROM gunung WHERE id=?");
    $stmtGunung->execute([$gunungId]);

    if (!$stmtGunung->fetch()) {
        set_toast('error', 'Gunung tidak ditemukan.');
        redirect('/admin/paket.php');
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | TAMBAH
        |--------------------------------------------------------------------------
        */
        if ($aksi === 'tambah') {

            $stmt = $pdo->prepare("
                INSERT INTO paket
                (gunung_id, nama_paket, harga, fasilitas, kuota_per_hari)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $gunungId,
                $namaPaket,
                $harga,
                $fasilitas,
                $kuota
            ]);

            set_toast('success', 'Paket berhasil ditambahkan.');
        }

        /*
        |--------------------------------------------------------------------------
        | EDIT
        |--------------------------------------------------------------------------
        */
        elseif ($aksi === 'edit' && $id > 0) {

            $stmt = $pdo->prepare("
                UPDATE paket SET
                    gunung_id=?,
                    nama_paket=?,
                    harga=?,
                    fasilitas=?,
                    kuota_per_hari=?
                WHERE id=?
            ");

            $stmt->execute([
                $gunungId,
                $namaPaket,
                $harga,
                $fasilitas,
                $kuota,
                $id
            ]);

            set_toast('success', 'Paket berhasil diperbarui.');
        }

    } catch (Exception $e) {

        set_toast(
            'error',
            'Gagal menyimpan paket: ' . $e->getMessage()
        );
    }

    redirect('/admin/paket.php');
}


/*
|--------------------------------------------------------------------------
| HAPUS PAKET
|--------------------------------------------------------------------------
*/
if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        try {

            // Cek apakah paket masih digunakan booking
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM booking
                WHERE paket_id=?
            ");

            $stmt->execute([$id]);
            $jumlahBooking = (int)$stmt->fetchColumn();

            if ($jumlahBooking > 0) {

                set_toast(
                    'error',
                    'Paket tidak bisa dihapus karena sudah digunakan dalam booking.'
                );

            } else {

                $stmt = $pdo->prepare("DELETE FROM paket WHERE id=?");
                $stmt->execute([$id]);

                set_toast(
                    'success',
                    'Paket berhasil dihapus.'
                );
            }

        } catch (Exception $e) {

            set_toast(
                'error',
                'Gagal menghapus paket.'
            );
        }
    }

    redirect('/admin/paket.php');
}


/*
|--------------------------------------------------------------------------
| DATA GUNUNG
|--------------------------------------------------------------------------
*/
$gunungList = $pdo->query("
    SELECT id, nama_gunung
    FROM gunung
    ORDER BY nama_gunung ASC
")->fetchAll();


/*
|--------------------------------------------------------------------------
| DATA PAKET
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT
        p.*,
        g.nama_gunung
    FROM paket p
    JOIN gunung g ON g.id = p.gunung_id
    ORDER BY p.id DESC
");

$paketList = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| DATA EDIT
|--------------------------------------------------------------------------
*/
$editData = null;

if (isset($_GET['edit'])) {

    $idEdit = (int)$_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM paket
        WHERE id=?
    ");

    $stmt->execute([$idEdit]);
    $editData = $stmt->fetch();

    if (!$editData) {
        set_toast('error', 'Paket tidak ditemukan.');
        redirect('/admin/paket.php');
    }
}


$page_title = 'Kelola Paket - Admin Muncak.Kuy';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dash-layout">

    <?php
    $active = 'paket';
    require __DIR__ . '/sidebar_admin.php';
    ?>

    <main class="dash-main">

        <div class="dash-topbar">

            <h1>Kelola Paket</h1>

            <button
                class="btn btn-primary btn-sm"
                onclick="document.getElementById('formPaket').scrollIntoView({behavior:'smooth'})"
            >
                + Tambah Paket
            </button>

        </div>


        <!-- =====================================================
             FORM TAMBAH / EDIT
        ====================================================== -->

        <div
            class="panel reveal"
            id="formPaket"
            style="margin-bottom:26px;"
        >

            <h3 style="margin-bottom:18px;">
                <?= $editData ? 'Edit Paket Pendakian' : 'Tambah Paket Pendakian' ?>
            </h3>


            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= csrf_token() ?>"
                >

                <input
                    type="hidden"
                    name="aksi"
                    value="<?= $editData ? 'edit' : 'tambah' ?>"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $editData['id'] ?? '' ?>"
                >


                <div
                    style="
                    display:grid;
                    grid-template-columns:1fr 1fr;
                    gap:16px;
                    "
                >

                    <!-- GUNUNG -->

                    <div class="form-group">

                        <label>Gunung</label>

                        <select
                            name="gunung_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Gunung --
                            </option>

                            <?php foreach ($gunungList as $g): ?>

                                <option
                                    value="<?= $g['id'] ?>"
                                    <?= (int)($editData['gunung_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>
                                >
                                    <?= clean($g['nama_gunung']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- NAMA PAKET -->

                    <div class="form-group">

                        <label>Nama Paket</label>

                        <select
                            name="nama_paket"
                            class="form-control"
                            required
                        >

                            <?php
                            $namaPaketSaatIni = $editData['nama_paket'] ?? '';
                            ?>

                            <option
                                value="Regular"
                                <?= $namaPaketSaatIni === 'Regular' ? 'selected' : '' ?>
                            >
                                Regular
                            </option>

                            <option
                                value="Premium"
                                <?= $namaPaketSaatIni === 'Premium' ? 'selected' : '' ?>
                            >
                                Premium
                            </option>

                            <option
                                value="VIP"
                                <?= $namaPaketSaatIni === 'VIP' ? 'selected' : '' ?>
                            >
                                VIP
                            </option>

                        </select>

                    </div>


                    <!-- HARGA -->

                    <div class="form-group">

                        <label>Harga</label>

                        <input
                            type="number"
                            name="harga"
                            class="form-control"
                            min="1"
                            value="<?= $editData['harga'] ?? '' ?>"
                            placeholder="Contoh: 150000"
                            required
                        >

                    </div>


                    <!-- KUOTA -->

                    <div class="form-group">

                        <label>Kuota Per Hari</label>

                        <input
                            type="number"
                            name="kuota_per_hari"
                            class="form-control"
                            min="1"
                            value="<?= $editData['kuota_per_hari'] ?? '' ?>"
                            placeholder="Contoh: 50"
                            required
                        >

                    </div>

                </div>


                <!-- FASILITAS -->

                <div class="form-group">

                    <label>Fasilitas</label>

                    <textarea
                        name="fasilitas"
                        class="form-control"
                        rows="4"
                        placeholder="Contoh: Tiket masuk, Guide, Asuransi, Makan 2x"
                    ><?= clean($editData['fasilitas'] ?? '') ?></textarea>

                    <small style="color:#777;">
                        Pisahkan fasilitas menggunakan koma.
                    </small>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= $editData ? 'Update Paket' : 'Simpan Paket' ?>
                </button>


                <?php if ($editData): ?>

                    <a
                        href="<?= BASE_URL ?>/admin/paket.php"
                        class="btn btn-outline"
                    >
                        Batal
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- =====================================================
             TABEL PAKET
        ====================================================== -->

        <div class="panel reveal">

            <div class="panel-head">

                <h3>Daftar Paket Pendakian</h3>

            </div>


            <div style="overflow-x:auto;">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>Gunung</th>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Fasilitas</th>
                            <th>Kuota / Hari</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($paketList as $p): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= clean($p['nama_gunung']) ?>
                                    </strong>
                                </td>


                                <td>

                                    <span class="status-pill info">
                                        <?= clean($p['nama_paket']) ?>
                                    </span>

                                </td>


                                <td>
                                    <?= rupiah($p['harga']) ?>
                                </td>


                                <td style="max-width:300px;">

                                    <?= nl2br(
                                        clean(
                                            str_replace(
                                                ',',
                                                ', ',
                                                $p['fasilitas'] ?? '-'
                                            )
                                        )
                                    ) ?>

                                </td>


                                <td>
                                    <?= (int)$p['kuota_per_hari'] ?> orang
                                </td>


                                <td>

                                    <div
                                        style="
                                        display:flex;
                                        gap:6px;
                                        "
                                    >

                                        <a
                                            href="?edit=<?= $p['id'] ?>#formPaket"
                                            class="btn btn-sm btn-outline"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="?hapus=<?= $p['id'] ?>"
                                            class="btn btn-sm btn-dark"
                                            style="background:var(--danger);"
                                            onclick="return confirm('Yakin ingin menghapus paket <?= clean($p['nama_paket']) ?> untuk <?= clean($p['nama_gunung']) ?>?')"
                                        >
                                            Hapus
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                        <?php if (empty($paketList)): ?>

                            <tr>

                                <td
                                    colspan="6"
                                    style="text-align:center;"
                                >

                                    <span class="text-muted">
                                        Belum ada paket pendakian.
                                    </span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>