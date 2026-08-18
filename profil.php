<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| AMBIL DATA USER
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    redirect('/index.php');
}


/*
|--------------------------------------------------------------------------
| PROSES UPDATE PROFIL
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| PROSES UPDATE PROFIL
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CEK CSRF
    |--------------------------------------------------------------------------
    */
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        set_toast('error', 'Permintaan tidak valid. Silakan coba lagi.');
        redirect('/profil.php');
    }

    $nama = trim($_POST['nama_lengkap'] ?? '');
    $telp = trim($_POST['no_telepon'] ?? '');

    if ($nama === '') {
        set_toast('error', 'Nama lengkap tidak boleh kosong.');
        redirect('/profil.php');
    }


    /*
    |--------------------------------------------------------------------------
    | DATA FOTO LAMA
    |--------------------------------------------------------------------------
    */
    $fotoProfilLama = $user['foto_profil'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | DEFAULT FOTO BARU
    |--------------------------------------------------------------------------
    */
    $fotoProfilBaru = $fotoProfilLama;

    /*
    |--------------------------------------------------------------------------
    | FILE FOTO HASIL CROP
    |--------------------------------------------------------------------------
    |
    | Foto hasil crop dikirim melalui input:
    | foto_cropped
    |
    |--------------------------------------------------------------------------
    */
    $croppedImage = trim($_POST['foto_cropped'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VARIABEL UNTUK FOTO BARU
    |--------------------------------------------------------------------------
    */
    $newFileName = null;
    $destination = null;


    /*
    |--------------------------------------------------------------------------
    | PROSES FOTO BARU
    |--------------------------------------------------------------------------
    */
    if ($croppedImage !== '') {

        /*
        |--------------------------------------------------------------------------
        | CEK FORMAT DATA BASE64
        |--------------------------------------------------------------------------
        */
        if (
            !preg_match(
                '/^data:image\/(jpeg|jpg|png|webp);base64,/i',
                $croppedImage
            )
        ) {
            set_toast('error', 'Format foto tidak valid.');
            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | HAPUS HEADER BASE64
        |--------------------------------------------------------------------------
        */
        $croppedImage = preg_replace(
            '/^data:image\/(jpeg|jpg|png|webp);base64,/i',
            '',
            $croppedImage
        );


        /*
        |--------------------------------------------------------------------------
        | DECODE BASE64
        |--------------------------------------------------------------------------
        */
        $croppedImage = str_replace(' ', '+', $croppedImage);

        $imageData = base64_decode($croppedImage, true);


        if ($imageData === false) {
            set_toast('error', 'Foto gagal diproses.');
            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | BATAS UKURAN
        |--------------------------------------------------------------------------
        */
        if (strlen($imageData) > 2 * 1024 * 1024) {
            set_toast(
                'error',
                'Ukuran foto terlalu besar. Maksimal 2 MB.'
            );
            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | FOLDER UPLOAD
        |--------------------------------------------------------------------------
        */
        $uploadDir = __DIR__ . '/uploads/profil/';


        if (!is_dir($uploadDir)) {

            if (!mkdir($uploadDir, 0755, true)) {
                set_toast(
                    'error',
                    'Folder upload foto tidak dapat dibuat.'
                );

                redirect('/profil.php');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CEK FOLDER BISA DITULIS
        |--------------------------------------------------------------------------
        */
        if (!is_writable($uploadDir)) {

            set_toast(
                'error',
                'Folder uploads/profil tidak dapat ditulis oleh server.'
            );

            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | NAMA FILE BARU
        |--------------------------------------------------------------------------
        */
        $newFileName =
            'profil_' .
            $userId .
            '_' .
            time() .
            '_' .
            bin2hex(random_bytes(4)) .
            '.jpg';


        $destination = $uploadDir . $newFileName;


        /*
        |--------------------------------------------------------------------------
        | SIMPAN FOTO BARU
        |--------------------------------------------------------------------------
        */
        $saved = file_put_contents(
            $destination,
            $imageData,
            LOCK_EX
        );


        if ($saved === false) {

            set_toast(
                'error',
                'Foto gagal disimpan ke server.'
            );

            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN FILE BENAR-BENAR ADA
        |--------------------------------------------------------------------------
        */
        if (
            !file_exists($destination) ||
            filesize($destination) <= 0
        ) {

            set_toast(
                'error',
                'Foto berhasil diproses tetapi file tidak ditemukan.'
            );

            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | PATH YANG DISIMPAN KE DATABASE
        |--------------------------------------------------------------------------
        */
        $fotoProfilBaru =
            'uploads/profil/' . $newFileName;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DATABASE
    |--------------------------------------------------------------------------
    */
    try {

        $upd = $pdo->prepare("
            UPDATE users
            SET
                nama_lengkap = ?,
                no_telepon = ?,
                foto_profil = ?
            WHERE id = ?
        ");


        $success = $upd->execute([
            $nama,
            $telp,
            $fotoProfilBaru,
            $userId
        ]);


        /*
        |--------------------------------------------------------------------------
        | CEK QUERY
        |--------------------------------------------------------------------------
        */
        if (!$success) {

            /*
            | Jika database gagal, hapus foto baru
            | supaya tidak meninggalkan file sampah.
            */
            if (
                $destination !== null &&
                file_exists($destination)
            ) {
                @unlink($destination);
            }


            set_toast(
                'error',
                'Data profil gagal disimpan ke database.'
            );

            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFIKASI DATA DARI DATABASE
        |--------------------------------------------------------------------------
        */
        $check = $pdo->prepare("
            SELECT
                id,
                nama_lengkap,
                no_telepon,
                foto_profil
            FROM users
            WHERE id = ?
            LIMIT 1
        ");


        $check->execute([$userId]);

        $updatedUser = $check->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | DATA TIDAK DITEMUKAN
        |--------------------------------------------------------------------------
        */
        if (!$updatedUser) {

            if (
                $destination !== null &&
                file_exists($destination)
            ) {
                @unlink($destination);
            }

            set_toast(
                'error',
                'Data profil tidak ditemukan setelah diperbarui.'
            );

            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFIKASI FOTO DATABASE
        |--------------------------------------------------------------------------
        */
        if (
            $updatedUser['foto_profil'] !== $fotoProfilBaru
        ) {

            if (
                $destination !== null &&
                file_exists($destination)
            ) {
                @unlink($destination);
            }

            set_toast(
                'error',
                'Foto berhasil diunggah tetapi belum tersimpan ke database.'
            );

            redirect('/profil.php');
        }


        /*
        |--------------------------------------------------------------------------
        | HAPUS FOTO LAMA
        |--------------------------------------------------------------------------
        |
        | FOTO LAMA BARU DIHAPUS SETELAH DATABASE BERHASIL UPDATE.
        |
        |--------------------------------------------------------------------------
        */
        if (
            !empty($fotoProfilLama) &&
            $fotoProfilLama !== $fotoProfilBaru
        ) {

            $oldFile =
                __DIR__ .
                '/' .
                ltrim($fotoProfilLama, '/');


            if (
                file_exists($oldFile) &&
                is_file($oldFile)
            ) {
                @unlink($oldFile);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE SESSION
        |--------------------------------------------------------------------------
        */
        $_SESSION['nama'] =
            $updatedUser['nama_lengkap'];


        /*
        |--------------------------------------------------------------------------
        | BERHASIL
        |--------------------------------------------------------------------------
        */
        set_toast(
            'success',
            'Profil berhasil diperbarui.'
        );


        redirect('/profil.php');


    } catch (PDOException $e) {

        /*
        |--------------------------------------------------------------------------
        | HAPUS FOTO BARU JIKA DATABASE ERROR
        |--------------------------------------------------------------------------
        */
        if (
            $destination !== null &&
            file_exists($destination)
        ) {
            @unlink($destination);
        }


        /*
        |--------------------------------------------------------------------------
        | LOG ERROR
        |--------------------------------------------------------------------------
        */
        error_log(
            'Muncak.Kuy profil update error: ' .
            $e->getMessage()
        );


        set_toast(
            'error',
            'Profil gagal disimpan ke database.'
        );


        redirect('/profil.php');
    }
}


/*
|--------------------------------------------------------------------------
| TENTUKAN FOTO PROFIL
|--------------------------------------------------------------------------
*/
$fotoUrl = '';

if (!empty($user['foto_profil'])) {

    $fotoPath = __DIR__ . '/' . ltrim($user['foto_profil'], '/');

    if (
        file_exists($fotoPath) &&
        is_file($fotoPath)
    ) {
        $fotoUrl = '/' . ltrim($user['foto_profil'], '/');
    }
}


/*
|--------------------------------------------------------------------------
| JIKA BELUM ADA FOTO
|--------------------------------------------------------------------------
*/
if ($fotoUrl === '') {

    $fotoUrl =
        'https://ui-avatars.com/api/?name=' .
        urlencode($user['nama_lengkap']) .
        '&background=1b5e3a&color=fff&size=180';
}


$page_title = 'Profil Saya - Muncak.Kuy';

require_once __DIR__ . '/includes/header.php';
?>


<div class="dash-layout" style="padding-top:0;">

    <?php
    $active = 'profil';
    require __DIR__ . '/includes/sidebar_user.php';
    ?>


    <main class="dash-main">

        <!-- TOPBAR -->
        <div class="dash-topbar">

            <div>

                <h1>Profil Saya</h1>

                <p
                    class="text-muted"
                    style="margin-top:5px;"
                >
                    Kelola informasi profil akun Muncak.Kuy kamu.
                </p>

            </div>

        </div>


        <!-- PROFILE PANEL -->
        <div
            class="panel reveal"
            style="
                max-width:760px;
                width:100%;
                margin:0 auto;
                padding:36px;
            "
        >

            <form
                method="POST"
                enctype="multipart/form-data"
                id="profileForm"
            >

                <!-- CSRF -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= csrf_token() ?>"
                >


                <!-- HASIL CROP -->
                <input
                    type="hidden"
                    name="foto_cropped"
                    id="fotoCropped"
                    value=""
                >


                <!-- FOTO PROFIL -->
                <div
                    style="
                        text-align:center;
                        margin-bottom:32px;
                    "
                >

                    <div
                        style="
                            position:relative;
                            width:150px;
                            height:150px;
                            margin:0 auto 16px;
                        "
                    >

                        <img
                            src="<?= clean($fotoUrl) ?>"
                            alt="Foto Profil"
                            id="profilePreview"
                            style="
                                width:150px;
                                height:150px;
                                object-fit:cover;
                                border-radius:50%;
                                border:5px solid var(--white);
                                box-shadow:
                                    0 8px 25px
                                    rgba(13,51,32,0.18);
                            "
                        >


                        <!-- TOMBOL KAMERA -->
                        <label
                            for="foto_profil"
                            title="Ganti Foto Profil"
                            style="
                                position:absolute;
                                right:2px;
                                bottom:2px;
                                width:42px;
                                height:42px;
                                border-radius:50%;
                                background:var(--gold-500);
                                color:var(--forest-900);
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                cursor:pointer;
                                box-shadow:
                                    0 5px 15px
                                    rgba(13,51,32,0.2);
                                font-size:18px;
                                font-weight:800;
                                transition:
                                    transform .2s ease;
                            "
                            onmouseover="this.style.transform='scale(1.08)'"
                            onmouseout="this.style.transform='scale(1)'"
                        >
                            📷
                        </label>

                    </div>


                    <!-- INPUT FOTO -->
                    <input
                        type="file"
                        name="foto_profil"
                        id="foto_profil"
                        accept="image/jpeg,image/png,image/webp"
                        style="display:none;"
                    >


                    <h3
                        style="
                            font-size:18px;
                            margin-bottom:4px;
                        "
                    >
                        <?= clean($user['nama_lengkap']) ?>
                    </h3>


                    <p
                        class="text-muted"
                        style="
                            font-size:13px;
                            margin:0;
                        "
                    >
                        Klik ikon kamera untuk mengganti foto profil
                    </p>


                    <p
                        class="text-muted"
                        style="
                            font-size:11.5px;
                            margin-top:5px;
                        "
                    >
                        JPG, PNG, atau WEBP • Maksimal 2 MB
                    </p>

                </div>


                <!-- NAMA -->
                <div class="form-group">

                    <label for="nama_lengkap">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama_lengkap"
                        id="nama_lengkap"
                        class="form-control"
                        value="<?= clean($user['nama_lengkap']) ?>"
                        placeholder="Masukkan nama lengkap"
                        required
                    >

                </div>


                <!-- EMAIL -->
                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        class="form-control"
                        value="<?= clean($user['email']) ?>"
                        disabled
                        style="
                            background:var(--cream-100);
                            cursor:not-allowed;
                        "
                    >

                    <small
                        class="text-muted"
                        style="
                            display:block;
                            margin-top:6px;
                            font-size:11.5px;
                        "
                    >
                        Email tidak dapat diubah dari halaman profil.
                    </small>

                </div>


                <!-- NOMOR TELEPON -->
                <div class="form-group">

                    <label for="no_telepon">
                        No. Telepon
                    </label>

                    <input
                        type="text"
                        name="no_telepon"
                        id="no_telepon"
                        class="form-control"
                        value="<?= clean($user['no_telepon'] ?? '') ?>"
                        placeholder="08xxxxxxxxxx"
                    >

                </div>


                <!-- BUTTON -->
                <div
                    style="
                        display:flex;
                        justify-content:flex-end;
                        gap:12px;
                        margin-top:26px;
                        flex-wrap:wrap;
                    "
                >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>



<!-- =========================================================
     MODAL CROP FOTO
========================================================= -->

<div
    id="cropModal"
    style="
        display:none;
        position:fixed;
        inset:0;
        z-index:99999;
        background:rgba(0,0,0,.72);
        align-items:center;
        justify-content:center;
        padding:20px;
    "
>

    <div
        style="
            width:100%;
            max-width:520px;
            background:var(--white);
            border-radius:20px;
            padding:24px;
            box-shadow:0 20px 60px rgba(0,0,0,.3);
        "
    >

        <!-- JUDUL -->
        <div
            style="
                display:flex;
                align-items:center;
                justify-content:space-between;
                margin-bottom:18px;
            "
        >

            <div>

                <h3
                    style="
                        margin:0;
                        font-size:19px;
                    "
                >
                    Sesuaikan Foto
                </h3>

                <p
                    class="text-muted"
                    style="
                        font-size:12px;
                        margin-top:4px;
                    "
                >
                    Geser dan zoom agar wajah berada di tengah.
                </p>

            </div>


            <button
                type="button"
                id="closeCrop"
                style="
                    width:34px;
                    height:34px;
                    border-radius:50%;
                    background:var(--cream-100);
                    color:var(--ink-900);
                    font-size:18px;
                    cursor:pointer;
                "
            >
                ×
            </button>

        </div>


        <!-- AREA CROP -->
        <div
            id="cropArea"
            style="
                position:relative;
                width:100%;
                max-width:400px;
                height:400px;
                margin:0 auto;
                background:#111;
                overflow:hidden;
                border-radius:18px;
                user-select:none;
                touch-action:none;
            "
        >

            <img
                id="cropImage"
                src=""
                alt="Crop Foto"
                draggable="false"
                style="
                    position:absolute;
                    left:50%;
                    top:50%;
                    transform:
                        translate(-50%,-50%)
                        scale(1);
                    max-width:none;
                    width:auto;
                    height:auto;
                    user-select:none;
                    pointer-events:none;
                "
            >


            <!-- LINGKARAN PANDUAN -->
            <div
                style="
                    position:absolute;
                    inset:0;
                    pointer-events:none;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                "
            >

                <div
                    style="
                        width:250px;
                        height:250px;
                        border-radius:50%;
                        border:3px solid rgba(255,255,255,.95);
                        box-shadow:
                            0 0 0 9999px
                            rgba(0,0,0,.45);
                    "
                ></div>

            </div>

        </div>


        <!-- ZOOM -->
        <div
            style="
                margin-top:22px;
            "
        >

            <label
                for="zoomRange"
                style="
                    display:block;
                    font-size:13px;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                Zoom Foto
            </label>

            <input
                type="range"
                id="zoomRange"
                min="0.5"
                max="3"
                step="0.01"
                value="1"
                style="
                    width:100%;
                    accent-color:var(--forest-700);
                "
            >

        </div>


        <!-- BUTTON CROP -->
        <div
            style="
                display:flex;
                gap:10px;
                margin-top:20px;
            "
        >

            <button
                type="button"
                id="cancelCrop"
                class="btn btn-outline"
                style="flex:1;"
            >
                Batal
            </button>


            <button
                type="button"
                id="applyCrop"
                class="btn btn-primary"
                style="flex:1;"
            >
                Gunakan Foto
            </button>

        </div>

    </div>

</div>



<!-- =========================================================
     JAVASCRIPT CROP FOTO
========================================================= -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('foto_profil');
    const preview = document.getElementById('profilePreview');
    const modal = document.getElementById('cropModal');
    const cropArea = document.getElementById('cropArea');
    const cropImage = document.getElementById('cropImage');
    const zoomRange = document.getElementById('zoomRange');
    const fotoCropped = document.getElementById('fotoCropped');

    const closeCrop = document.getElementById('closeCrop');
    const cancelCrop = document.getElementById('cancelCrop');
    const applyCrop = document.getElementById('applyCrop');


    let imageObjectURL = null;

    let scale = 1;

    let posX = 0;
    let posY = 0;

    let startX = 0;
    let startY = 0;

    let dragging = false;


    /*
    |--------------------------------------------------------------------------
    | BUKA FILE
    |--------------------------------------------------------------------------
    */

    input.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CEK UKURAN
        |--------------------------------------------------------------------------
        */

        if (file.size > 2 * 1024 * 1024) {

            alert('Ukuran foto maksimal 2 MB.');

            this.value = '';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CEK FORMAT
        |--------------------------------------------------------------------------
        */

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {

            alert('Format foto harus JPG, PNG, atau WEBP.');

            this.value = '';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | BUAT URL SEMENTARA
        |--------------------------------------------------------------------------
        */

        if (imageObjectURL) {
            URL.revokeObjectURL(imageObjectURL);
        }

        imageObjectURL = URL.createObjectURL(file);

        cropImage.src = imageObjectURL;


        cropImage.onload = function () {

            scale = 1;

            posX = 0;
            posY = 0;

            zoomRange.value = 1;

            updateCropImage();

            modal.style.display = 'flex';

        };

    });


    /*
    |--------------------------------------------------------------------------
    | UPDATE POSISI FOTO
    |--------------------------------------------------------------------------
    */

    function updateCropImage() {

        cropImage.style.transform =
            'translate(calc(-50% + ' +
            posX +
            'px), calc(-50% + ' +
            posY +
            'px)) scale(' +
            scale +
            ')';

    }


    /*
    |--------------------------------------------------------------------------
    | ZOOM
    |--------------------------------------------------------------------------
    */

    zoomRange.addEventListener('input', function () {

        scale = parseFloat(this.value);

        updateCropImage();

    });


    /*
    |--------------------------------------------------------------------------
    | DRAG MOUSE
    |--------------------------------------------------------------------------
    */

    cropArea.addEventListener('mousedown', function (e) {

        dragging = true;

        startX = e.clientX - posX;
        startY = e.clientY - posY;

        cropArea.style.cursor = 'grabbing';

    });


    document.addEventListener('mousemove', function (e) {

        if (!dragging) {
            return;
        }

        posX = e.clientX - startX;
        posY = e.clientY - startY;

        updateCropImage();

    });


    document.addEventListener('mouseup', function () {

        dragging = false;

        cropArea.style.cursor = 'default';

    });


    /*
    |--------------------------------------------------------------------------
    | DRAG TOUCH / HP
    |--------------------------------------------------------------------------
    */

    cropArea.addEventListener(
        'touchstart',
        function (e) {

            const touch = e.touches[0];

            dragging = true;

            startX = touch.clientX - posX;
            startY = touch.clientY - posY;

        },
        {
            passive: true
        }
    );


    cropArea.addEventListener(
        'touchmove',
        function (e) {

            if (!dragging) {
                return;
            }

            const touch = e.touches[0];

            posX = touch.clientX - startX;
            posY = touch.clientY - startY;

            updateCropImage();

        },
        {
            passive: true
        }
    );


    cropArea.addEventListener(
        'touchend',
        function () {

            dragging = false;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | HASIL CROP
    |--------------------------------------------------------------------------
    */

    applyCrop.addEventListener('click', function () {

        if (!cropImage.complete) {
            return;
        }


        const outputSize = 500;

        const canvas = document.createElement('canvas');

        canvas.width = outputSize;
        canvas.height = outputSize;

        const ctx = canvas.getContext('2d');


        /*
        |--------------------------------------------------------------------------
        | BACKGROUND
        |--------------------------------------------------------------------------
        */

        ctx.fillStyle = '#ffffff';

        ctx.fillRect(
            0,
            0,
            outputSize,
            outputSize
        );


        /*
        |--------------------------------------------------------------------------
        | HITUNG UKURAN AREA CROP
        |--------------------------------------------------------------------------
        */

        const areaSize = cropArea.clientWidth;

        const circleSize = 250;

        const cropLeft =
            (areaSize - circleSize) / 2;

        const cropTop =
            (areaSize - circleSize) / 2;


        /*
        |--------------------------------------------------------------------------
        | UKURAN FOTO ASLI DI BROWSER
        |--------------------------------------------------------------------------
        */

        const naturalWidth = cropImage.naturalWidth;
        const naturalHeight = cropImage.naturalHeight;


        /*
        |--------------------------------------------------------------------------
        | UKURAN FOTO DI LAYAR
        |--------------------------------------------------------------------------
        */

        const displayedWidth =
            cropImage.offsetWidth * scale;

        const displayedHeight =
            cropImage.offsetHeight * scale;


        /*
        |--------------------------------------------------------------------------
        | POSISI FOTO DI AREA
        |--------------------------------------------------------------------------
        */

        const imageLeft =
            (areaSize - displayedWidth) / 2 + posX;

        const imageTop =
            (areaSize - displayedHeight) / 2 + posY;


        /*
        |--------------------------------------------------------------------------
        | BAGIAN FOTO YANG DIPILIH
        |--------------------------------------------------------------------------
        */

        const sourceX =
            (cropLeft - imageLeft)
            *
            naturalWidth
            /
            displayedWidth;

        const sourceY =
            (cropTop - imageTop)
            *
            naturalHeight
            /
            displayedHeight;


        const sourceWidth =
            circleSize
            *
            naturalWidth
            /
            displayedWidth;

        const sourceHeight =
            circleSize
            *
            naturalHeight
            /
            displayedHeight;


        /*
        |--------------------------------------------------------------------------
        | CLIP LINGKARAN
        |--------------------------------------------------------------------------
        */

        ctx.save();

        ctx.beginPath();

        ctx.arc(
            outputSize / 2,
            outputSize / 2,
            outputSize / 2,
            0,
            Math.PI * 2
        );

        ctx.closePath();

        ctx.clip();


        /*
        |--------------------------------------------------------------------------
        | DRAW FOTO
        |--------------------------------------------------------------------------
        */

        ctx.drawImage(
            cropImage,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            0,
            0,
            outputSize,
            outputSize
        );

        ctx.restore();


        /*
        |--------------------------------------------------------------------------
        | HASIL KE BASE64
        |--------------------------------------------------------------------------
        */

        const result = canvas.toDataURL(
            'image/jpeg',
            0.88
        );


        /*
        |--------------------------------------------------------------------------
        | SIMPAN KE INPUT HIDDEN
        |--------------------------------------------------------------------------
        */

        fotoCropped.value = result;


        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN PREVIEW
        |--------------------------------------------------------------------------
        */

        preview.src = result;


        /*
        |--------------------------------------------------------------------------
        | TUTUP MODAL
        |--------------------------------------------------------------------------
        */

        modal.style.display = 'none';

    });


    /*
    |--------------------------------------------------------------------------
    | BATAL
    |--------------------------------------------------------------------------
    */

    function closeModal() {

        modal.style.display = 'none';

        input.value = '';

        fotoCropped.value = '';

    }


    closeCrop.addEventListener(
        'click',
        closeModal
    );


    cancelCrop.addEventListener(
        'click',
        closeModal
    );


    /*
    |--------------------------------------------------------------------------
    | KLIK AREA LUAR MODAL
    |--------------------------------------------------------------------------
    */

    modal.addEventListener('click', function (e) {

        if (e.target === modal) {
            closeModal();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (e) {

        if (
            e.key === 'Escape' &&
            modal.style.display === 'flex'
        ) {
            closeModal();
        }

    });

});

</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>