```php
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

/*
|--------------------------------------------------------------------------
| PROSES AKSI
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        set_toast('error', 'Pesan tidak valid.');
        redirect('/admin/kontak.php');
    }

    try {

        /*
        |----------------------------------------------------------------------
        | TANDAI SUDAH DIBACA
        |----------------------------------------------------------------------
        */

        if ($aksi === 'dibaca') {

            $stmt = $pdo->prepare("
                UPDATE kontak
                SET status = 'dibaca'
                WHERE id = ?
            ");

            $stmt->execute([$id]);

            set_toast(
                'success',
                'Pesan berhasil ditandai sudah dibaca.'
            );

            redirect('/admin/kontak.php');
        }


        /*
        |----------------------------------------------------------------------
        | HAPUS PESAN
        |----------------------------------------------------------------------
        */

        if ($aksi === 'hapus') {

            $stmt = $pdo->prepare("
                DELETE FROM kontak
                WHERE id = ?
            ");

            $stmt->execute([$id]);

            set_toast(
                'success',
                'Pesan berhasil dihapus.'
            );

            redirect('/admin/kontak.php');
        }


    } catch (PDOException $e) {

        error_log(
            'Gagal memproses pesan kontak: ' .
            $e->getMessage()
        );

        set_toast(
            'error',
            'Terjadi kesalahan saat memproses pesan.'
        );

        redirect('/admin/kontak.php');
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PESAN
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT *
    FROM kontak
    ORDER BY
        CASE
            WHEN status = 'baru' THEN 0
            ELSE 1
        END,
        created_at DESC
");

$pesanKontak = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| JUMLAH PESAN BARU
|--------------------------------------------------------------------------
*/

$stmtBaru = $pdo->query("
    SELECT COUNT(*)
    FROM kontak
    WHERE status = 'baru'
");

$jumlahBaru = (int)$stmtBaru->fetchColumn();


$page_title = 'Pesan Kontak - Admin Muncak.Kuy';

$active = 'kontak';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dash-layout" style="padding-top:0;">

    <?php
    $active = 'kontak';
    require __DIR__ . '/sidebar_admin.php';
    ?>

    <main class="dash-main">

        <div class="dash-topbar">

            <div>

                <h1>Pesan Kontak</h1>

                <p class="text-muted" style="margin-top:5px;">
                    Kelola pesan dan pertanyaan dari pengguna Muncak.Kuy.
                </p>

            </div>

        </div>


        <!-- INFO -->

        <div
            class="panel reveal"
            style="
            margin-bottom:20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:20px;
            "
        >

            <div>

                <strong>
                    Pesan Baru
                </strong>

                <div
                    class="text-muted"
                    style="font-size:13px;margin-top:4px;"
                >
                    Pesan yang belum dibaca oleh admin.
                </div>

            </div>

            <div
                style="
                font-size:24px;
                font-weight:700;
                color:var(--primary);
                "
            >
                <?= $jumlahBaru ?>
            </div>

        </div>


        <!-- DAFTAR PESAN -->

        <div class="panel reveal">

            <div class="panel-head">

                <h3>
                    Daftar Pesan
                </h3>

            </div>


            <?php if (empty($pesanKontak)): ?>

                <div
                    style="
                    text-align:center;
                    padding:50px 20px;
                    "
                >

                    <div
                        style="
                        font-size:40px;
                        margin-bottom:10px;
                        "
                    >
                        ✉️
                    </div>

                    <strong>
                        Belum ada pesan
                    </strong>

                    <p
                        class="text-muted"
                        style="margin-top:6px;"
                    >
                        Pesan dari pengguna akan muncul di sini.
                    </p>

                </div>

            <?php else: ?>

                <div style="overflow-x:auto;">

                    <table class="data-table">

                        <thead>

                            <tr>

                                <th>Nama</th>
                                <th>Email</th>
                                <th>Pesan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($pesanKontak as $pesan): ?>

                            <tr>

                                <!-- NAMA -->

                                <td>

                                    <strong>
                                        <?= clean($pesan['nama']) ?>
                                    </strong>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <a
                                        href="mailto:<?= clean($pesan['email']) ?>"
                                        style="color:var(--primary);"
                                    >
                                        <?= clean($pesan['email']) ?>
                                    </a>

                                </td>


                                <!-- PESAN -->

                                <td style="min-width:280px;">

                                    <div
                                        style="
                                        max-width:400px;
                                        line-height:1.6;
                                        white-space:normal;
                                        "
                                    >
                                        <?= nl2br(clean($pesan['pesan'])) ?>
                                    </div>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if ($pesan['status'] === 'baru'): ?>

                                        <span class="status-pill info">
                                            Baru
                                        </span>

                                    <?php else: ?>

                                        <span class="status-pill success">
                                            Sudah Dibaca
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- TANGGAL -->

                                <td>

                                    <span
                                        class="text-muted"
                                        style="white-space:nowrap;"
                                    >
                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime($pesan['created_at'])
                                        ) ?>
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

                                        <?php if ($pesan['status'] === 'baru'): ?>

                                            <form
                                                method="POST"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="aksi"
                                                    value="dibaca"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$pesan['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-primary"
                                                >
                                                    ✓ Dibaca
                                                </button>

                                            </form>

                                        <?php endif; ?>


                                        <form
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Yakin ingin menghapus pesan ini?')"
                                        >

                                            <input
                                                type="hidden"
                                                name="aksi"
                                                value="hapus"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$pesan['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline"
                                            >
                                                🗑 Hapus
                                            </button>

                                        </form>

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


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```
