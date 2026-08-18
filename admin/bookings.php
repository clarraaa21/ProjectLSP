<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();


/*
|--------------------------------------------------------------------------
| UPDATE STATUS BOOKING
|--------------------------------------------------------------------------
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['booking_id'])
) {

    // Validasi CSRF
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {

        set_toast(
            'error',
            'Sesi tidak valid. Silakan coba lagi.'
        );

        redirect('/admin/bookings.php');
    }


    $id = (int)($_POST['booking_id'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS PEMBAYARAN
    |--------------------------------------------------------------------------
    */
    if (isset($_POST['status_pembayaran'])) {

        $statusPembayaran =
            $_POST['status_pembayaran'] ?? '';

        $allowedPembayaran = [
            'menunggu',
            'lunas',
            'gagal',
            'refund'
        ];


        if (
            $id > 0 &&
            in_array(
                $statusPembayaran,
                $allowedPembayaran,
                true
            )
        ) {

            $stmt = $pdo->prepare("
                UPDATE booking
                SET status_pembayaran = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $statusPembayaran,
                $id
            ]);


            set_toast(
                'success',
                'Status pembayaran berhasil diperbarui.'
            );
        }


        redirect('/admin/bookings.php');
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS PENDAKIAN
    |--------------------------------------------------------------------------
    */
    if (isset($_POST['status_pendakian'])) {

        $statusPendakian =
            $_POST['status_pendakian'] ?? '';

        $allowedPendakian = [
            'akan_datang',
            'berlangsung',
            'selesai',
            'batal'
        ];


        if (
            $id > 0 &&
            in_array(
                $statusPendakian,
                $allowedPendakian,
                true
            )
        ) {

            $stmt = $pdo->prepare("
                UPDATE booking
                SET status_pendakian = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $statusPendakian,
                $id
            ]);


            set_toast(
                'success',
                'Status pendakian berhasil diperbarui.'
            );
        }


        redirect('/admin/bookings.php');
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH BOOKING
|--------------------------------------------------------------------------
*/
$search = trim($_GET['q'] ?? '');


$sql = "
    SELECT
        b.*,
        g.nama_gunung,
        u.nama_lengkap
    FROM booking b

    JOIN gunung g
        ON g.id = b.gunung_id

    JOIN users u
        ON u.id = b.user_id

    WHERE 1=1
";


$params = [];


if ($search !== '') {

    $sql .= "
        AND (
            b.kode_booking LIKE ?
            OR u.nama_lengkap LIKE ?
            OR g.nama_gunung LIKE ?
        )
    ";

    $like = "%{$search}%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}


$sql .= "
    ORDER BY b.created_at DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$bookings = $stmt->fetchAll();


$page_title = 'Kelola Booking - Admin Muncak.Kuy';

require_once __DIR__ . '/../includes/header.php';
?>


<div class="dash-layout">

    <?php

    $active = 'booking';

    require __DIR__ . '/sidebar_admin.php';

    ?>


    <main class="dash-main">


        <!-- TOPBAR -->

        <div class="dash-topbar">

            <h1>Kelola Booking</h1>

        </div>


        <!-- SEARCH -->

        <form
            method="GET"
            class="filter-bar reveal"
        >

            <div class="search-box">

                <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >

                    <circle
                        cx="11"
                        cy="11"
                        r="8"
                    />

                    <path
                        d="M21 21l-4.35-4.35"
                    />

                </svg>


                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Cari kode booking, nama, atau gunung..."
                    value="<?= clean($search) ?>"
                >

            </div>


            <button
                class="btn btn-sm btn-outline"
                type="submit"
            >
                Cari
            </button>


            <?php if ($search !== ''): ?>

                <a
                    href="<?= BASE_URL ?>/admin/bookings.php"
                    class="btn btn-sm btn-dark"
                >
                    Reset
                </a>

            <?php endif; ?>


        </form>



        <!-- TABLE -->

        <div class="panel reveal">

            <div style="overflow-x:auto;">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>Kode</th>

                            <th>Pengguna</th>

                            <th>Gunung</th>

                            <th>Tanggal</th>

                            <th>Peserta</th>

                            <th>Total</th>

                            <th>Pembayaran</th>

                            <th>Pendakian</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($bookings as $b): ?>


                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | STATUS PEMBAYARAN
                        |--------------------------------------------------------------------------
                        */

                        $bayarMap = [

                            'menunggu' => [
                                'warning',
                                'Menunggu'
                            ],

                            'lunas' => [
                                'success',
                                'Lunas'
                            ],

                            'gagal' => [
                                'danger',
                                'Gagal'
                            ],

                            'refund' => [
                                'info',
                                'Refund'
                            ]

                        ];


                        $bayarStatus =
                            $bayarMap[
                                $b['status_pembayaran']
                            ]
                            ?? [
                                'warning',
                                ucfirst(
                                    $b['status_pembayaran']
                                )
                            ];


                        /*
                        |--------------------------------------------------------------------------
                        | STATUS PENDAKIAN
                        |--------------------------------------------------------------------------
                        */

                        $pendakianMap = [

                            'akan_datang' => [
                                'info',
                                'Akan Datang'
                            ],

                            'berlangsung' => [
                                'warning',
                                'Berlangsung'
                            ],

                            'selesai' => [
                                'success',
                                'Selesai'
                            ],

                            'batal' => [
                                'danger',
                                'Batal'
                            ]

                        ];


                        $pendakianStatus =
                            $pendakianMap[
                                $b['status_pendakian']
                            ]
                            ?? [
                                'info',
                                ucfirst(
                                    $b['status_pendakian']
                                )
                            ];

                        ?>


                        <tr>


                            <!-- KODE -->

                            <td>

                                <strong>
                                    <?= clean(
                                        $b['kode_booking']
                                    ) ?>
                                </strong>

                            </td>


                            <!-- PENGGUNA -->

                            <td>

                                <?= clean(
                                    $b['nama_lengkap']
                                ) ?>

                            </td>


                            <!-- GUNUNG -->

                            <td>

                                <?= clean(
                                    $b['nama_gunung']
                                ) ?>

                            </td>


                            <!-- TANGGAL -->

                            <td>

                                <?= tanggal_indo(
                                    $b['tanggal_pendakian']
                                ) ?>

                            </td>


                            <!-- PESERTA -->

                            <td>

                                <?= (int)$b[
                                    'jumlah_peserta'
                                ] ?>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <?= rupiah(
                                    $b['total_harga']
                                ) ?>

                            </td>


                            <!-- STATUS PEMBAYARAN -->

                            <td>


                                <div
                                    style="
                                    display:flex;
                                    flex-direction:column;
                                    gap:7px;
                                    "
                                >


                                    <span
                                        class="status-pill <?= $bayarStatus[0] ?>"
                                    >

                                        <?= $bayarStatus[1] ?>

                                    </span>


                                    <form
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= csrf_token() ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="booking_id"
                                            value="<?= (int)$b['id'] ?>"
                                        >


                                        <select
                                            name="status_pembayaran"
                                            onchange="this.form.submit()"
                                            style="
                                            padding:6px 8px;
                                            border-radius:8px;
                                            border:1.5px solid #e3ddcc;
                                            font-size:12px;
                                            width:100%;
                                            "
                                        >

                                            <option
                                                value="menunggu"
                                                <?= $b['status_pembayaran'] === 'menunggu'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Menunggu
                                            </option>


                                            <option
                                                value="lunas"
                                                <?= $b['status_pembayaran'] === 'lunas'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Lunas
                                            </option>


                                            <option
                                                value="gagal"
                                                <?= $b['status_pembayaran'] === 'gagal'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Gagal
                                            </option>


                                            <option
                                                value="refund"
                                                <?= $b['status_pembayaran'] === 'refund'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Refund
                                            </option>

                                        </select>

                                    </form>


                                </div>

                            </td>


                            <!-- STATUS PENDAKIAN -->

                            <td>


                                <div
                                    style="
                                    display:flex;
                                    flex-direction:column;
                                    gap:7px;
                                    "
                                >


                                    <span
                                        class="status-pill <?= $pendakianStatus[0] ?>"
                                    >

                                        <?= $pendakianStatus[1] ?>

                                    </span>


                                    <form
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= csrf_token() ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="booking_id"
                                            value="<?= (int)$b['id'] ?>"
                                        >


                                        <select
                                            name="status_pendakian"
                                            onchange="this.form.submit()"
                                            style="
                                            padding:6px 8px;
                                            border-radius:8px;
                                            border:1.5px solid #e3ddcc;
                                            font-size:12px;
                                            width:100%;
                                            "
                                        >


                                            <option
                                                value="akan_datang"
                                                <?= $b['status_pendakian'] === 'akan_datang'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Akan Datang
                                            </option>


                                            <option
                                                value="berlangsung"
                                                <?= $b['status_pendakian'] === 'berlangsung'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Berlangsung
                                            </option>


                                            <option
                                                value="selesai"
                                                <?= $b['status_pendakian'] === 'selesai'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Selesai
                                            </option>


                                            <option
                                                value="batal"
                                                <?= $b['status_pendakian'] === 'batal'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Batal
                                            </option>


                                        </select>

                                    </form>


                                </div>


                            </td>


                            <!-- AKSI -->

                            <td>


                                <a
                                    href="<?= BASE_URL ?>/eticket.php?kode=<?= urlencode($b['kode_booking']) ?>"
                                    class="btn btn-sm btn-dark"
                                    target="_blank"
                                >
                                    Tiket
                                </a>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                    <?php if (empty($bookings)): ?>


                        <tr>

                            <td
                                colspan="9"
                                class="text-muted"
                                style="
                                text-align:center;
                                padding:30px;
                                "
                            >

                                Tidak ada data booking.

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