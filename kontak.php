<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';


/*
|--------------------------------------------------------------------------
| PROSES KIRIM PESAN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if ($nama === '' || $email === '' || $pesan === '') {

        set_toast(
            'error',
            'Semua kolom wajib diisi.'
        );

        redirect('/kontak.php');
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        set_toast(
            'error',
            'Format email tidak valid.'
        );

        redirect('/kontak.php');
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN KE DATABASE
    |--------------------------------------------------------------------------
    */

    try {

        $stmt = $pdo->prepare("
            INSERT INTO kontak
            (
                nama,
                email,
                pesan,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'baru'
            )
        ");

        $stmt->execute([
            $nama,
            $email,
            $pesan
        ]);


        /*
        |--------------------------------------------------------------------------
        | BERHASIL
        |--------------------------------------------------------------------------
        */

        set_toast(
            'success',
            'Pesan kamu berhasil terkirim! Tim kami akan segera merespons.'
        );

        redirect('/kontak.php');

    } catch (PDOException $e) {

        set_toast(
            'error',
            'Pesan gagal dikirim. Silakan coba lagi.'
        );

        redirect('/kontak.php');
    }
}


$page_title = 'Kontak - Muncak.Kuy';
$active_menu = 'kontak';

require_once __DIR__ . '/includes/header.php';
?>


<section
    class="section"
    style="padding-top:130px;"
>

    <div
        class="container"
        style="max-width:560px;"
    >

        <div class="section-head reveal">

            <span class="eyebrow">
                Kontak Kami
            </span>

            <h2>
                Ada Pertanyaan? Hubungi Kami
            </h2>

        </div>


        <div
            class="card-form reveal-scale in-view"
            style="margin:0 auto;"
        >

            <form
                method="POST"
                autocomplete="off"
            >

                <div class="form-group">

                    <label for="nama">
                        Nama
                    </label>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        class="form-control"
                        placeholder="Masukkan nama kamu"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Masukkan email kamu"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="pesan">
                        Pesan
                    </label>

                    <textarea
                        id="pesan"
                        name="pesan"
                        class="form-control"
                        rows="5"
                        placeholder="Tulis pesan atau pertanyaan kamu..."
                        required
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary btn-block"
                >
                    Kirim Pesan
                </button>

            </form>

        </div>

    </div>

</section>


<?php
require_once __DIR__ . '/includes/footer.php';
?>