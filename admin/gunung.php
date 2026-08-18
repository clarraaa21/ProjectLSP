<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$uploadDir = __DIR__ . '/../uploads/gunung/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/**
 * Upload satu foto
 */
function uploadFotoGunung($file, $uploadDir)
{
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Gagal mengupload foto.');
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        throw new Exception('Format foto harus JPG, JPEG, PNG, atau WEBP.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Ukuran foto maksimal 5 MB.');
    }

    $namaFile = 'gunung_' . uniqid() . '_' . time() . '.' . $ext;
    $target = $uploadDir . $namaFile;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new Exception('Foto gagal disimpan.');
    }

    return $namaFile;
}


/* =========================================================
   TAMBAH / EDIT GUNUNG
========================================================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'simpan'
) {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_toast('error', 'Sesi tidak valid.');
        redirect('/admin/gunung.php');
    }

    $id = (int)($_POST['id'] ?? 0);

    $namaGunung = trim($_POST['nama_gunung'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $provinsi = trim($_POST['provinsi'] ?? '');
    $ketinggian = (int)($_POST['ketinggian'] ?? 0);
    $tingkat = $_POST['tingkat_kesulitan'] ?? 'Menengah';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $estimasi = trim($_POST['estimasi_waktu'] ?? '');
    $harga = (float)($_POST['harga_mulai'] ?? 0);
    $status = $_POST['status'] ?? 'buka';

    try {

        /* =========================
           UPLOAD FOTO UTAMA
        ========================= */
        $fotoUtamaBaru = null;

        if (
            isset($_FILES['foto_utama']) &&
            $_FILES['foto_utama']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $fotoUtamaBaru = uploadFotoGunung(
                $_FILES['foto_utama'],
                $uploadDir
            );
        }


        /* =========================
           EDIT
        ========================= */
        if ($id > 0) {

            // Ambil data lama
            $stmtOld = $pdo->prepare(
                "SELECT foto_utama FROM gunung WHERE id=?"
            );
            $stmtOld->execute([$id]);
            $old = $stmtOld->fetch();

            if ($fotoUtamaBaru) {

                $stmt = $pdo->prepare("
                    UPDATE gunung SET
                        nama_gunung=?,
                        lokasi=?,
                        provinsi=?,
                        ketinggian=?,
                        tingkat_kesulitan=?,
                        deskripsi=?,
                        estimasi_waktu=?,
                        harga_mulai=?,
                        foto_utama=?,
                        status=?
                    WHERE id=?
                ");

                $stmt->execute([
                    $namaGunung,
                    $lokasi,
                    $provinsi,
                    $ketinggian,
                    $tingkat,
                    $deskripsi,
                    $estimasi,
                    $harga,
                    $fotoUtamaBaru,
                    $status,
                    $id
                ]);

                // Hapus foto lama
                if (!empty($old['foto_utama'])) {
                    $oldFile = $uploadDir . basename($old['foto_utama']);

                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }

            } else {

                $stmt = $pdo->prepare("
                    UPDATE gunung SET
                        nama_gunung=?,
                        lokasi=?,
                        provinsi=?,
                        ketinggian=?,
                        tingkat_kesulitan=?,
                        deskripsi=?,
                        estimasi_waktu=?,
                        harga_mulai=?,
                        status=?
                    WHERE id=?
                ");

                $stmt->execute([
                    $namaGunung,
                    $lokasi,
                    $provinsi,
                    $ketinggian,
                    $tingkat,
                    $deskripsi,
                    $estimasi,
                    $harga,
                    $status,
                    $id
                ]);
            }

            /* =========================
               FOTO TAMBAHAN
            ========================= */

            if (
                isset($_FILES['foto_tambahan']) &&
                !empty($_FILES['foto_tambahan']['name'][0])
            ) {

                $jumlahFoto = count($_FILES['foto_tambahan']['name']);

                // Cari urutan terakhir
                $stmtUrutan = $pdo->prepare("
                    SELECT COALESCE(MAX(urutan), 0)
                    FROM gunung_foto
                    WHERE gunung_id=?
                ");

                $stmtUrutan->execute([$id]);
                $urutan = (int)$stmtUrutan->fetchColumn();

                for ($i = 0; $i < $jumlahFoto; $i++) {

                    if ($_FILES['foto_tambahan']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $file = [
                        'name' => $_FILES['foto_tambahan']['name'][$i],
                        'type' => $_FILES['foto_tambahan']['type'][$i],
                        'tmp_name' => $_FILES['foto_tambahan']['tmp_name'][$i],
                        'error' => $_FILES['foto_tambahan']['error'][$i],
                        'size' => $_FILES['foto_tambahan']['size'][$i]
                    ];

                    $foto = uploadFotoGunung($file, $uploadDir);

                    if ($foto) {
                        $urutan++;

                        $stmtFoto = $pdo->prepare("
                            INSERT INTO gunung_foto
                            (gunung_id, foto, urutan)
                            VALUES (?, ?, ?)
                        ");

                        $stmtFoto->execute([
                            $id,
                            $foto,
                            $urutan
                        ]);
                    }
                }
            }

            set_toast(
                'success',
                'Data gunung berhasil diperbarui.'
            );

        } else {

            /* =========================
               TAMBAH GUNUNG
            ========================= */

            $stmt = $pdo->prepare("
                INSERT INTO gunung
                (
                    nama_gunung,
                    lokasi,
                    provinsi,
                    ketinggian,
                    tingkat_kesulitan,
                    deskripsi,
                    estimasi_waktu,
                    harga_mulai,
                    foto_utama,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $namaGunung,
                $lokasi,
                $provinsi,
                $ketinggian,
                $tingkat,
                $deskripsi,
                $estimasi,
                $harga,
                $fotoUtamaBaru,
                $status
            ]);

            $gunungIdBaru = $pdo->lastInsertId();


            /* =========================
               FOTO TAMBAHAN
            ========================= */

            if (
                isset($_FILES['foto_tambahan']) &&
                !empty($_FILES['foto_tambahan']['name'][0])
            ) {

                $jumlahFoto = count($_FILES['foto_tambahan']['name']);

                for ($i = 0; $i < $jumlahFoto; $i++) {

                    if ($_FILES['foto_tambahan']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $file = [
                        'name' => $_FILES['foto_tambahan']['name'][$i],
                        'type' => $_FILES['foto_tambahan']['type'][$i],
                        'tmp_name' => $_FILES['foto_tambahan']['tmp_name'][$i],
                        'error' => $_FILES['foto_tambahan']['error'][$i],
                        'size' => $_FILES['foto_tambahan']['size'][$i]
                    ];

                    $foto = uploadFotoGunung(
                        $file,
                        $uploadDir
                    );

                    if ($foto) {

                        $stmtFoto = $pdo->prepare("
                            INSERT INTO gunung_foto
                            (gunung_id, foto, urutan)
                            VALUES (?, ?, ?)
                        ");

                        $stmtFoto->execute([
                            $gunungIdBaru,
                            $foto,
                            $i + 1
                        ]);
                    }
                }
            }

            set_toast(
                'success',
                'Gunung baru berhasil ditambahkan.'
            );
        }

    } catch (Exception $e) {

        set_toast(
            'error',
            $e->getMessage()
        );
    }

    redirect('/admin/gunung.php');
}


/* =========================================================
   HAPUS FOTO TAMBAHAN
========================================================= */
if (isset($_GET['hapus_foto'])) {

    $fotoId = (int)$_GET['hapus_foto'];

    $stmt = $pdo->prepare("
        SELECT foto, gunung_id
        FROM gunung_foto
        WHERE id=?
    ");

    $stmt->execute([$fotoId]);
    $foto = $stmt->fetch();

    if ($foto) {

        $file = $uploadDir . basename($foto['foto']);

        if (is_file($file)) {
            @unlink($file);
        }

        $pdo->prepare(
            "DELETE FROM gunung_foto WHERE id=?"
        )->execute([$fotoId]);

        set_toast(
            'success',
            'Foto tambahan berhasil dihapus.'
        );
    }

    redirect('/admin/gunung.php?edit=' . ($foto['gunung_id'] ?? ''));
}


/* =========================================================
   HAPUS GUNUNG
========================================================= */
if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    // Ambil foto utama
    $stmt = $pdo->prepare("
        SELECT foto_utama
        FROM gunung
        WHERE id=?
    ");

    $stmt->execute([$id]);
    $gunung = $stmt->fetch();


    // Ambil foto tambahan
    $stmtFoto = $pdo->prepare("
        SELECT foto
        FROM gunung_foto
        WHERE gunung_id=?
    ");

    $stmtFoto->execute([$id]);
    $fotoTambahan = $stmtFoto->fetchAll();


    // Hapus file foto utama
    if (!empty($gunung['foto_utama'])) {

        $file = $uploadDir . basename($gunung['foto_utama']);

        if (is_file($file)) {
            @unlink($file);
        }
    }


    // Hapus file foto tambahan
    foreach ($fotoTambahan as $f) {

        $file = $uploadDir . basename($f['foto']);

        if (is_file($file)) {
            @unlink($file);
        }
    }


    // Hapus database
    $pdo->prepare(
        "DELETE FROM gunung_foto WHERE gunung_id=?"
    )->execute([$id]);

    $pdo->prepare(
        "DELETE FROM gunung WHERE id=?"
    )->execute([$id]);

    set_toast(
        'success',
        'Gunung berhasil dihapus.'
    );

    redirect('/admin/gunung.php');
}


/* =========================================================
   DATA GUNUNG
========================================================= */
$gunungList = $pdo->query("
    SELECT *
    FROM gunung
    ORDER BY id DESC
")->fetchAll();


/* =========================================================
   DATA EDIT
========================================================= */
$editData = null;
$editFoto = [];

if (isset($_GET['edit'])) {

    $stmt = $pdo->prepare(
        "SELECT * FROM gunung WHERE id=?"
    );

    $stmt->execute([
        (int)$_GET['edit']
    ]);

    $editData = $stmt->fetch();


    if ($editData) {

        $stmtFoto = $pdo->prepare("
            SELECT *
            FROM gunung_foto
            WHERE gunung_id=?
            ORDER BY urutan ASC
        ");

        $stmtFoto->execute([
            $editData['id']
        ]);

        $editFoto = $stmtFoto->fetchAll();
    }
}


$page_title = 'Kelola Gunung - Admin Muncak.Kuy';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dash-layout">

    <?php
    $active = 'gunung';
    require __DIR__ . '/sidebar_admin.php';
    ?>

    <main class="dash-main">

        <div class="dash-topbar">

            <h1>Kelola Gunung</h1>

            <button
                class="btn btn-primary btn-sm"
                onclick="document.getElementById('formGunung').scrollIntoView({behavior:'smooth'})"
            >
                + Tambah Gunung
            </button>

        </div>


        <!-- FORM -->

        <div
            class="panel reveal"
            id="formGunung"
            style="margin-bottom:26px;"
        >

            <h3 style="margin-bottom:16px;">
                <?= $editData ? 'Edit Gunung' : 'Tambah Gunung Baru' ?>
            </h3>


            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= csrf_token() ?>"
                >

                <input
                    type="hidden"
                    name="aksi"
                    value="simpan"
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

                    <div class="form-group">

                        <label>Nama Gunung</label>

                        <input
                            type="text"
                            name="nama_gunung"
                            class="form-control"
                            value="<?= clean($editData['nama_gunung'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Lokasi</label>

                        <input
                            type="text"
                            name="lokasi"
                            class="form-control"
                            value="<?= clean($editData['lokasi'] ?? '') ?>"
                            placeholder="Contoh: Malang"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Provinsi</label>

                        <input
                            type="text"
                            name="provinsi"
                            class="form-control"
                            value="<?= clean($editData['provinsi'] ?? '') ?>"
                            placeholder="Contoh: Jawa Timur"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Ketinggian (mdpl)</label>

                        <input
                            type="number"
                            name="ketinggian"
                            class="form-control"
                            value="<?= $editData['ketinggian'] ?? '' ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Tingkat Kesulitan</label>

                        <select
                            name="tingkat_kesulitan"
                            class="form-control"
                        >

                            <?php
                            foreach (
                                ['Pemula','Menengah','Sulit','Ekstrem']
                                as $t
                            ):
                            ?>

                                <option
                                    value="<?= $t ?>"
                                    <?= ($editData['tingkat_kesulitan'] ?? 'Menengah') === $t ? 'selected' : '' ?>
                                >
                                    <?= $t ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>Estimasi Waktu</label>

                        <input
                            type="text"
                            name="estimasi_waktu"
                            class="form-control"
                            value="<?= clean($editData['estimasi_waktu'] ?? '') ?>"
                            placeholder="Contoh: 2 Hari 1 Malam"
                        >

                    </div>


                    <div class="form-group">

                        <label>Harga Mulai (Rp)</label>

                        <input
                            type="number"
                            name="harga_mulai"
                            class="form-control"
                            value="<?= $editData['harga_mulai'] ?? '' ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Status Gunung</label>

                        <select
                            name="status"
                            class="form-control"
                        >

                            <option
                                value="buka"
                                <?= ($editData['status'] ?? 'buka') === 'buka' ? 'selected' : '' ?>
                            >
                                Buka
                            </option>

                            <option
                                value="tutup"
                                <?= ($editData['status'] ?? '') === 'tutup' ? 'selected' : '' ?>
                            >
                                Tutup
                            </option>

                        </select>

                    </div>

                </div>


                <div class="form-group">

                    <label>Deskripsi</label>

                    <textarea
                        name="deskripsi"
                        class="form-control"
                        rows="4"
                    ><?= clean($editData['deskripsi'] ?? '') ?></textarea>

                </div>


                <!-- FOTO UTAMA -->

                <div class="form-group">

                    <label>
                        Foto Utama
                        <small style="color:#777;">
                            (JPG, PNG, WEBP - maksimal 5 MB)
                        </small>
                    </label>

                    <input
                        type="file"
                        name="foto_utama"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <?php if (!empty($editData['foto_utama'])): ?>

                        <div style="margin-top:10px;">

                            <img
                                src="<?= BASE_URL ?>/uploads/gunung/<?= clean($editData['foto_utama']) ?>"
                                style="
                                width:160px;
                                height:100px;
                                object-fit:cover;
                                border-radius:10px;
                                "
                                onerror="this.style.display='none'"
                            >

                        </div>

                    <?php endif; ?>

                </div>


                <!-- FOTO TAMBAHAN -->

                <div class="form-group">

                    <label>
                        Foto Tambahan
                        <small style="color:#777;">
                            (bisa pilih beberapa)
                        </small>
                    </label>

                    <input
                        type="file"
                        name="foto_tambahan[]"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple
                    >

                </div>


                <?php if (!empty($editFoto)): ?>

                    <div class="form-group">

                        <label>Foto Tambahan Saat Ini</label>

                        <div
                            style="
                            display:flex;
                            flex-wrap:wrap;
                            gap:12px;
                            "
                        >

                            <?php foreach ($editFoto as $foto): ?>

                                <div
                                    style="
                                    width:130px;
                                    position:relative;
                                    "
                                >

                                    <img
                                        src="<?= BASE_URL ?>/uploads/gunung/<?= clean($foto['foto']) ?>"
                                        style="
                                        width:130px;
                                        height:90px;
                                        object-fit:cover;
                                        border-radius:10px;
                                        "
                                        onerror="this.parentElement.style.display='none'"
                                    >

                                    <a
                                        href="?hapus_foto=<?= $foto['id'] ?>"
                                        class="btn btn-sm btn-dark"
                                        style="
                                        background:var(--danger);
                                        margin-top:5px;
                                        width:100%;
                                        "
                                        onclick="return confirm('Hapus foto ini?')"
                                    >
                                        Hapus Foto
                                    </a>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <?= $editData ? 'Update Gunung' : 'Simpan Gunung' ?>
                </button>


                <?php if ($editData): ?>

                    <a
                        href="<?= BASE_URL ?>/admin/gunung.php"
                        class="btn btn-outline"
                    >
                        Batal
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- TABEL -->

        <div class="panel reveal">

            <div style="overflow-x:auto;">

                <table class="data-table">

                    <thead>

                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Lokasi</th>
                            <th>Ketinggian</th>
                            <th>Tingkat</th>
                            <th>Harga</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($gunungList as $g): ?>

                            <tr>

                                <td>

                                    <?php if (!empty($g['foto_utama'])): ?>

                                        <img
                                            src="<?= BASE_URL ?>/uploads/gunung/<?= clean($g['foto_utama']) ?>"
                                            style="
                                            width:70px;
                                            height:50px;
                                            object-fit:cover;
                                            border-radius:8px;
                                            "
                                            onerror="this.style.display='none'"
                                        >

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Tidak ada
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <strong>
                                        <?= clean($g['nama_gunung']) ?>
                                    </strong>
                                </td>


                                <td>
                                    <?= clean($g['lokasi']) ?>,
                                    <?= clean($g['provinsi']) ?>
                                </td>


                                <td>
                                    <?= (int)$g['ketinggian'] ?> mdpl
                                </td>


                                <td>
                                    <?= clean($g['tingkat_kesulitan']) ?>
                                </td>


                                <td>
                                    <?= rupiah($g['harga_mulai']) ?>
                                </td>


                                <td>
                                    ⭐ <?= number_format((float)$g['rating'], 1) ?>
                                </td>


                                <td>

                                    <span
                                        class="status-pill <?= $g['status'] === 'buka' ? 'success' : 'danger' ?>"
                                    >
                                        <?= ucfirst($g['status']) ?>
                                    </span>

                                </td>


                                <td>

                                    <div
                                        style="
                                        display:flex;
                                        gap:6px;
                                        "
                                    >

                                        <a
                                            href="?edit=<?= $g['id'] ?>#formGunung"
                                            class="btn btn-sm btn-outline"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="?hapus=<?= $g['id'] ?>"
                                            class="btn btn-sm btn-dark"
                                            style="background:var(--danger);"
                                            onclick="return confirm('Yakin ingin menghapus <?= clean($g['nama_gunung']) ?>?')"
                                        >
                                            Hapus
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                        <?php if (empty($gunungList)): ?>

                            <tr>

                                <td
                                    colspan="9"
                                    style="text-align:center;"
                                >
                                    Belum ada data gunung.

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