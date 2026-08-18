```php
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';


/*
|--------------------------------------------------------------------------
| AMBIL PARAMETER PENCARIAN
|--------------------------------------------------------------------------
*/

$q         = trim($_GET['q'] ?? '');
$lokasi    = trim($_GET['lokasi'] ?? '');
$tingkat   = trim($_GET['tingkat'] ?? '');
$sortHarga = trim($_GET['harga'] ?? '');


/*
|--------------------------------------------------------------------------
| QUERY GUNUNG
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM gunung WHERE status='buka'";
$params = [];


/*
|--------------------------------------------------------------------------
| PENCARIAN NAMA / LOKASI / PROVINSI
|--------------------------------------------------------------------------
*/

if ($q !== '') {

    $sql .= " AND (
        nama_gunung LIKE ?
        OR lokasi LIKE ?
        OR provinsi LIKE ?
    )";

    $like = "%$q%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}


/*
|--------------------------------------------------------------------------
| FILTER LOKASI
|--------------------------------------------------------------------------
*/

if ($lokasi !== '') {

    $sql .= " AND provinsi = ?";

    $params[] = $lokasi;
}


/*
|--------------------------------------------------------------------------
| FILTER TINGKAT KESULITAN
|--------------------------------------------------------------------------
*/

if ($tingkat !== '') {

    $sql .= " AND tingkat_kesulitan = ?";

    $params[] = $tingkat;
}


/*
|--------------------------------------------------------------------------
| SORT HARGA
|--------------------------------------------------------------------------
*/

if ($sortHarga === 'termurah') {

    $sql .= " ORDER BY harga_mulai ASC";

} elseif ($sortHarga === 'termahal') {

    $sql .= " ORDER BY harga_mulai DESC";

} else {

    $sql .= " ORDER BY rating DESC, jumlah_review DESC";
}


/*
|--------------------------------------------------------------------------
| EKSEKUSI QUERY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$hasil = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| DAFTAR PROVINSI
|--------------------------------------------------------------------------
*/

$provinsiList = $pdo
    ->query("
        SELECT DISTINCT provinsi
        FROM gunung
        WHERE provinsi IS NOT NULL
        AND provinsi != ''
        ORDER BY provinsi
    ")
    ->fetchAll(PDO::FETCH_COLUMN);


/*
|--------------------------------------------------------------------------
| FUNGSI URL FOTO GUNUNG
|--------------------------------------------------------------------------
|
| Database bisa menyimpan:
|
| 1. semeru.jpg
| 2. assets/img/gunung/semeru.jpg
| 3. /assets/img/gunung/semeru.jpg
| 4. https://...
|
| Fungsi ini menangani semuanya.
|--------------------------------------------------------------------------
*/

function foto_gunung_url($foto)
{
    $foto = trim((string) $foto);

    /*
    |----------------------------------------------------------------------
    | FOTO KOSONG
    |----------------------------------------------------------------------
    */

    if ($foto === '') {

        return 'https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=800&auto=format&fit=crop';
    }


    /*
    |----------------------------------------------------------------------
    | JIKA SUDAH URL
    |----------------------------------------------------------------------
    */

    if (
        strpos($foto, 'http://') === 0 ||
        strpos($foto, 'https://') === 0
    ) {

        return $foto;
    }


    /*
    |----------------------------------------------------------------------
    | BERSIHKAN SLASH DI DEPAN
    |----------------------------------------------------------------------
    */

    $foto = ltrim($foto, '/');


    /*
    |----------------------------------------------------------------------
    | JIKA SUDAH MENYIMPAN PATH assets/
    |----------------------------------------------------------------------
    */

    if (strpos($foto, 'assets/') === 0) {

        return BASE_URL . '/' . $foto;
    }


    /*
    |----------------------------------------------------------------------
    | JIKA MENYIMPAN assets/img/gunung/
    | TANPA "assets/" DI DEPAN
    |----------------------------------------------------------------------
    */

    if (strpos($foto, 'img/gunung/') === 0) {

        return BASE_URL . '/assets/' . $foto;
    }


    /*
    |----------------------------------------------------------------------
    | JIKA HANYA NAMA FILE
    |
    | Contoh:
    | semeru.jpg
    | rinjani.png
    | bromo.webp
    |----------------------------------------------------------------------
    */

    return BASE_URL . '/assets/img/gunung/' . $foto;
}


/*
|--------------------------------------------------------------------------
| FOTO FALLBACK
|--------------------------------------------------------------------------
*/

$fallbackFoto = 'https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=800&auto=format&fit=crop';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

$page_title  = 'Cari Gunung - Muncak.Kuy';
$active_menu = 'gunung';

require_once __DIR__ . '/includes/header.php';
?>


<section
    class="section"
    style="padding-top:130px;"
>

    <div class="container">


        <!-- ==========================================================
             TOMBOL KEMBALI
        =========================================================== -->

        <div style="margin-bottom:20px;">

            <a
                href="<?= BASE_URL ?>/index.php"
                class="btn btn-outline btn-sm"
            >
                ← Kembali
            </a>

        </div>


        <!-- ==========================================================
             JUDUL HALAMAN
        =========================================================== -->

        <div
            class="section-head reveal"
            style="margin-bottom:30px;"
        >

            <span class="eyebrow">
                Pencarian
            </span>

            <h2>
                Temukan Gunung Impianmu
            </h2>

            <p class="text-muted">
                Cari gunung berdasarkan nama, lokasi,
                tingkat kesulitan, atau harga.
            </p>

        </div>


        <!-- ==========================================================
             FORM PENCARIAN
        =========================================================== -->

        <form
            method="GET"
            action="<?= BASE_URL ?>/search.php"
            class="filter-bar reveal"
        >


            <!-- SEARCH BOX -->

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
                    placeholder="Cari gunung, lokasi, atau paket..."
                    value="<?= clean($q) ?>"
                >

            </div>


            <!-- ======================================================
                 FILTER LOKASI
            ======================================================= -->

            <select name="lokasi">

                <option value="">
                    Semua Lokasi
                </option>


                <?php foreach ($provinsiList as $p): ?>

                    <option
                        value="<?= clean($p) ?>"
                        <?= $lokasi === $p ? 'selected' : '' ?>
                    >
                        <?= clean($p) ?>
                    </option>

                <?php endforeach; ?>

            </select>


            <!-- ======================================================
                 FILTER TINGKAT
            ======================================================= -->

            <select name="tingkat">

                <option value="">
                    Semua Tingkat
                </option>


                <?php foreach (
                    ['Pemula', 'Menengah', 'Sulit', 'Ekstrem']
                    as $t
                ): ?>

                    <option
                        value="<?= clean($t) ?>"
                        <?= $tingkat === $t ? 'selected' : '' ?>
                    >
                        <?= clean($t) ?>
                    </option>

                <?php endforeach; ?>

            </select>


            <!-- ======================================================
                 FILTER HARGA
            ======================================================= -->

            <select name="harga">

                <option value="">
                    Harga
                </option>


                <option
                    value="termurah"
                    <?= $sortHarga === 'termurah' ? 'selected' : '' ?>
                >
                    Termurah
                </option>


                <option
                    value="termahal"
                    <?= $sortHarga === 'termahal' ? 'selected' : '' ?>
                >
                    Termahal
                </option>

            </select>


            <!-- ======================================================
                 TOMBOL CARI
            ======================================================= -->

            <button
                type="submit"
                class="btn btn-dark btn-sm"
            >

                <svg
                    width="14"
                    height="14"
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

                Cari

            </button>

        </form>


        <!-- ==========================================================
             JUMLAH HASIL
        =========================================================== -->

        <p
            class="text-muted reveal"
            style="margin-bottom:20px;"
        >
            <?= count($hasil) ?> gunung ditemukan
        </p>


        <!-- ==========================================================
             GRID GUNUNG
        =========================================================== -->

        <div class="grid-4">


            <?php foreach ($hasil as $i => $g): ?>


                <?php
                /*
                |--------------------------------------------------------------------------
                | AMBIL FOTO DARI DATABASE
                |--------------------------------------------------------------------------
                */

                $fotoUrl = foto_gunung_url(
                    $g['foto_utama'] ?? ''
                );
                ?>


                <div
                    class="
                        gunung-card
                        reveal-scale
                        reveal-delay-<?= min(($i % 4) + 1, 4) ?>
                    "
                >


                    <!-- ==================================================
                         FOTO GUNUNG
                    =================================================== -->

                    <div class="img-wrap">


                        <img
                            src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= clean($g['nama_gunung']) ?>"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='<?= $fallbackFoto ?>';"
                        >


                        <!-- TINGKAT KESULITAN -->

                        <span class="badge-diff">

                            <?= clean($g['tingkat_kesulitan']) ?>

                        </span>


                        <!-- WISHLIST -->

                        <button
                            class="wishlist-btn"
                            data-gunung-id="<?= $g['id'] ?>"
                            aria-label="Wishlist"
                            type="button"
                        >

                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >

                                <path
                                    d="
                                        M12 21s-6.7-4.35-9.3-8.1
                                        C.8 9.8 1.8 6 5.2 5
                                        c2-.6 3.7.3 4.8 1.8
                                        C11.1 5.3 12.8 4.4 14.8 5
                                        c3.4 1 4.4 4.8 2.5 7.9
                                        C18.7 16.65 12 21 12 21z
                                    "
                                />

                            </svg>

                        </button>


                    </div>


                    <!-- ==================================================
                         BODY CARD
                    =================================================== -->

                    <div class="body">


                        <!-- NAMA -->

                        <h3>
                            <?= clean($g['nama_gunung']) ?>
                        </h3>


                        <!-- LOKASI -->

                        <div class="location">

                            <svg
                                width="13"
                                height="13"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >

                                <path
                                    d="
                                        M21 10c0 7-9 13-9 13
                                        s-9-6-9-13
                                        a9 9 0 0118 0z
                                    "
                                />

                                <circle
                                    cx="12"
                                    cy="10"
                                    r="3"
                                />

                            </svg>


                            <?= clean($g['lokasi']) ?>,
                            <?= clean($g['provinsi']) ?>

                        </div>


                        <!-- RATING -->

                        <div class="rating">

                            <svg
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >

                                <polygon
                                    points="
                                        12 2
                                        15 9
                                        22 9.5
                                        17 14.5
                                        18.5 22
                                        12 18
                                        5.5 22
                                        7 14.5
                                        2 9.5
                                        9 9.5
                                    "
                                />

                            </svg>


                            <?= number_format(
                                (float) $g['rating'],
                                1
                            ) ?>


                            <span class="count">

                                (<?= (int) $g['jumlah_review'] ?>)

                            </span>

                        </div>


                        <!-- HARGA + DETAIL -->

                        <div class="price-row">

                            <div>

                                <div class="price-label">
                                    Mulai dari
                                </div>

                                <div class="price">
                                    <?= rupiah($g['harga_mulai']) ?>
                                </div>

                            </div>


                            <a
                                href="<?= BASE_URL ?>/booking.php?gunung_id=<?= $g['id'] ?>"
                                class="btn btn-dark btn-sm"
                            >
                                Lihat Detail
                            </a>

                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


        <!-- ==========================================================
             TIDAK ADA HASIL
        =========================================================== -->

        <?php if (empty($hasil)): ?>

            <div
                style="
                    text-align:center;
                    padding:60px 0;
                    color:var(--ink-400);
                "
            >

                <p>

                    Tidak ada gunung yang cocok
                    dengan pencarianmu.
                    Coba ubah filter.

                </p>

            </div>

        <?php endif; ?>


    </div>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
```
