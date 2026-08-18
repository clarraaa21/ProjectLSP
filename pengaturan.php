<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| PROSES GANTI PASSWORD
|--------------------------------------------------------------------------
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    csrf_verify($_POST['csrf_token'] ?? '')
) {

    $lama  = $_POST['password_lama'] ?? '';
    $baru  = $_POST['password_baru'] ?? '';
    $ulang = $_POST['password_ulang'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | AMBIL PASSWORD USER
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare(
        "SELECT password FROM users WHERE id=?"
    );

    $stmt->execute([$userId]);

    $u = $stmt->fetch();


    /*
    |--------------------------------------------------------------------------
    | VALIDASI PASSWORD LAMA
    |--------------------------------------------------------------------------
    */
    if (!$u || !password_verify($lama, $u['password'])) {

        set_toast(
            'error',
            'Password lama salah.'
        );

    /*
    |--------------------------------------------------------------------------
    | VALIDASI PASSWORD BARU
    |--------------------------------------------------------------------------
    */
    } elseif (strlen($baru) < 6) {

        set_toast(
            'error',
            'Password baru minimal 6 karakter.'
        );

    /*
    |--------------------------------------------------------------------------
    | VALIDASI KONFIRMASI
    |--------------------------------------------------------------------------
    */
    } elseif ($baru !== $ulang) {

        set_toast(
            'error',
            'Konfirmasi password baru tidak cocok.'
        );

    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */
    } else {

        $hash = password_hash(
            $baru,
            PASSWORD_BCRYPT
        );

        $pdo
            ->prepare(
                "UPDATE users SET password=? WHERE id=?"
            )
            ->execute([
                $hash,
                $userId
            ]);

        set_toast(
            'success',
            'Password berhasil diperbarui.'
        );
    }


    redirect('/pengaturan.php');
}


$page_title = 'Pengaturan - Muncak.Kuy';

require_once __DIR__ . '/includes/header.php';
?>


<div
    class="dash-layout"
    style="padding-top:0;"
>

    <?php
    $active = 'pengaturan';
    require __DIR__ . '/includes/sidebar_user.php';
    ?>


    <main class="dash-main">


        <!-- TOPBAR -->
        <div class="dash-topbar">

            <div>

                <h1>
                    Pengaturan Akun
                </h1>

                <p
                    class="text-muted"
                    style="
                        margin-top:6px;
                        margin-bottom:0;
                    "
                >
                    Kelola keamanan akun Muncak.Kuy kamu.
                </p>

            </div>

        </div>


        <!-- PASSWORD PANEL -->
        <div
            class="panel reveal"
            style="
                width:100%;
                max-width:760px;
                margin:25px auto 0;
                padding:38px;
            "
        >


            <!-- HEADER PANEL -->
            <div
                style="
                    margin-bottom:30px;
                    padding-bottom:20px;
                    border-bottom:1px solid
                        rgba(13,51,32,.10);
                "
            >

                <h3
                    style="
                        margin:0 0 7px;
                        font-size:21px;
                    "
                >
                    Ganti Password
                </h3>

                <p
                    class="text-muted"
                    style="
                        margin:0;
                        font-size:13px;
                        line-height:1.6;
                    "
                >
                    Gunakan password yang mudah kamu ingat
                    tetapi sulit ditebak oleh orang lain.
                </p>

            </div>


            <!-- FORM -->
            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= csrf_token() ?>"
                >


                <!-- PASSWORD LAMA -->
                <div
                    class="form-group"
                    style="margin-bottom:22px;"
                >

                    <label
                        for="password_lama"
                    >
                        Password Lama
                    </label>

                    <input
                        type="password"
                        name="password_lama"
                        id="password_lama"
                        class="form-control"
                        placeholder="Masukkan password lama"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <!-- PASSWORD BARU -->
                <div
                    class="form-group"
                    style="margin-bottom:22px;"
                >

                    <label
                        for="password_baru"
                    >
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password_baru"
                        id="password_baru"
                        class="form-control"
                        placeholder="Masukkan password baru"
                        autocomplete="new-password"
                        minlength="6"
                        required
                    >

                    <small
                        class="text-muted"
                        style="
                            display:block;
                            margin-top:7px;
                            font-size:11.5px;
                        "
                    >
                        Password baru minimal 6 karakter.
                    </small>

                </div>


                <!-- KONFIRMASI PASSWORD -->
                <div
                    class="form-group"
                    style="margin-bottom:28px;"
                >

                    <label
                        for="password_ulang"
                    >
                        Ulangi Password Baru
                    </label>

                    <input
                        type="password"
                        name="password_ulang"
                        id="password_ulang"
                        class="form-control"
                        placeholder="Masukkan kembali password baru"
                        autocomplete="new-password"
                        minlength="6"
                        required
                    >

                </div>


                <!-- BUTTON -->
                <div
                    style="
                        display:flex;
                        justify-content:flex-end;
                        gap:12px;
                        flex-wrap:wrap;
                        padding-top:5px;
                    "
                >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Perbarui Password
                    </button>

                </div>

            </form>

        </div>


    </main>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>