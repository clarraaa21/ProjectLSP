```php
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title  = 'Muncak.Kuy - Jelajahi Keindahan Alam, Raih Puncak Impianmu';
$active_menu = 'beranda';

/*
|--------------------------------------------------------------------------
| AMBIL GUNUNG POPULER
|--------------------------------------------------------------------------
| Mengambil 4 gunung dengan rating tertinggi.
*/
$gunungPopuler = $pdo->query("
    SELECT *
    FROM gunung
    WHERE status = 'buka'
    ORDER BY rating DESC, jumlah_review DESC
    LIMIT 4
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>


<!-- =====================================================
     HERO
====================================================== -->

<section class="hero">

    <!-- BACKGROUND HERO -->
    <div
        class="hero-bg"
        style="
            background-image:
            url('https://images.unsplash.com/photo-1470770841072-f978cf4d019e?q=80&w=1600&auto=format&fit=crop');
        "
    ></div>

    <div class="hero-overlay"></div>


    <!-- =================================================
         CLOUD + BIRDS
    ================================================== -->

    <div class="cloud-layer">

        <div
            class="cloud cloud-1"
            style="left:5%;"
        ></div>

        <div
            class="cloud cloud-2"
            style="left:45%;"
        ></div>

        <div
            class="cloud cloud-3"
            style="left:70%;"
        ></div>


        <!-- BIRD 1 -->

        <div
            class="bird bird-1"
            style="left:10%;"
        >

            <svg
                viewBox="0 0 24 10"
                fill="none"
                stroke="#fff"
                stroke-width="1.5"
            >

                <path
                    d="M0 5 Q6 -3 12 5 Q18 -3 24 5"
                />

            </svg>

        </div>


        <!-- BIRD 2 -->

        <div
            class="bird bird-2"
            style="left:60%;"
        >

            <svg
                viewBox="0 0 24 10"
                fill="none"
                stroke="#fff"
                stroke-width="1.5"
            >

                <path
                    d="M0 5 Q6 -3 12 5 Q18 -3 24 5"
                />

            </svg>

        </div>


        <!-- BIRD 3 -->

        <div
            class="bird bird-3"
            style="left:35%;"
        >

            <svg
                viewBox="0 0 24 10"
                fill="none"
                stroke="#fff"
                stroke-width="1.5"
            >

                <path
                    d="M0 5 Q6 -3 12 5 Q18 -3 24 5"
                />

            </svg>

        </div>

    </div>


    <!-- =================================================
         HERO CONTENT
    ================================================== -->

    <div class="container hero-content">

        <h1>
            Jelajahi Keindahan Alam,
            <br>
            Raih
            <span class="accent">
                Puncak
            </span>
            Impianmu
        </h1>


        <p>
            Booking pendakian mudah, aman, dan terpercaya
            bersama Muncak.Kuy. Ribuan pendaki telah
            mempercayakan petualangannya bersama kami.
        </p>


        <a
            href="<?= BASE_URL ?>/search.php"
            class="btn btn-primary"
        >
            Mulai Booking
        </a>


        <!-- =================================================
             HERO FEATURE
        ================================================== -->

        <div class="hero-cards">


            <!-- FEATURE 1 -->

            <div class="hero-card reveal">

                <div class="icon-box">

                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="M8 21l4-14 4 14M3 21h18M6 21l4-8M18 21l-4-8"
                        />

                    </svg>

                </div>


                <div>

                    <h4>
                        Banyak Pilihan Gunung
                    </h4>

                    <p>
                        Pilihan gunung populer di seluruh Indonesia.
                    </p>

                </div>

            </div>



            <!-- FEATURE 2 -->

            <div class="hero-card reveal reveal-delay-1">

                <div class="icon-box">

                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="M9 11l3 3L22 4"
                        />

                        <path
                            d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"
                        />

                    </svg>

                </div>


                <div>

                    <h4>
                        Booking Mudah &amp; Cepat
                    </h4>

                    <p>
                        Proses booking praktis dan efisien.
                    </p>

                </div>

            </div>



            <!-- FEATURE 3 -->

            <div class="hero-card reveal reveal-delay-2">

                <div class="icon-box">

                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"
                        />

                    </svg>

                </div>


                <div>

                    <h4>
                        Aman &amp; Terpercaya
                    </h4>

                    <p>
                        Transaksi aman dengan sistem terpercaya.
                    </p>

                </div>

            </div>


        </div>

    </div>


    <!-- =================================================
         MOUNTAIN DECORATION
    ================================================== -->

    <div class="hero-mountains">
    </div>

</section>



<!-- =====================================================
     GUNUNG POPULER
====================================================== -->

<section class="section">

    <div class="container">


        <!-- SECTION HEADER -->

        <div class="section-head reveal">

            <span class="eyebrow">
                Destinasi Favorit
            </span>


            <h2>
                Gunung Terpopuler Pilihan Pendaki
            </h2>


            <p>
                Rekomendasi gunung dengan rating terbaik
                dari ribuan pendaki di seluruh Indonesia.
            </p>

        </div>



        <!-- =================================================
             GUNUNG GRID
        ================================================== -->

        <div class="grid-4">


            <?php foreach ($gunungPopuler as $i => $g): ?>


                <div
                    class="
                        gunung-card
                        reveal-scale
                        reveal-delay-<?= min($i + 1, 4) ?>
                    "
                >


                    <!-- =================================================
                         FOTO UTAMA GUNUNG
                    ================================================== -->

                    <div class="img-wrap">


                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | FOTO UTAMA
                        |--------------------------------------------------------------------------
                        |
                        | Foto gunung yang diupload dari admin disimpan di:
                        |
                        | /uploads/gunung/
                        |
                        | Bukan:
                        |
                        | /assets/img/gunung/
                        |
                        */

                        if (!empty($g['foto_utama'])) {

                            $fotoGunung =
                                BASE_URL .
                                '/uploads/gunung/' .
                                rawurlencode($g['foto_utama']);

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | FOTO CADANGAN
                            |--------------------------------------------------------------------------
                            */

                            $fotoGunung =
                                'https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=600&auto=format&fit=crop';

                        }

                        ?>


                        <img
                            src="<?= $fotoGunung ?>"
                            alt="<?= clean($g['nama_gunung']) ?>"
                            loading="lazy"
                            onerror="
                                this.onerror=null;
                                this.src='https://images.unsplash.com/photo-1519681393784-d120267933ba?q=80&w=600&auto=format&fit=crop';
                            "
                        >



                        <!-- =================================================
                             BADGE KESULITAN
                        ================================================== -->

                        <span class="badge-diff">

                            <?= clean($g['tingkat_kesulitan']) ?>

                        </span>



                        <!-- =================================================
                             WISHLIST
                        ================================================== -->

                        <button
                            class="wishlist-btn"
                            data-gunung-id="<?= (int)$g['id'] ?>"
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



                    <!-- =================================================
                         BODY CARD
                    ================================================== -->

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
                                        M21 10
                                        c0 7-9 13-9 13
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



                        <!-- =================================================
                             RATING
                        ================================================== -->

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
                                        9 9
                                    "
                                />

                            </svg>


                            <?= number_format(
                                (float)$g['rating'],
                                1
                            ) ?>


                            <span class="count">

                                (
                                <?= (int)$g['jumlah_review'] ?>
                                )

                            </span>

                        </div>



                        <!-- =================================================
                             HARGA
                        ================================================== -->

                        <div class="price-row">


                            <div>

                                <div class="price-label">
                                    Mulai dari
                                </div>


                                <div class="price">

                                    <?= rupiah($g['harga_mulai']) ?>

                                </div>

                            </div>



                            <!-- DETAIL -->

                            <a
                                href="<?= BASE_URL ?>/booking.php?gunung_id=<?= (int)$g['id'] ?>"
                                class="btn btn-dark btn-sm"
                            >
                                Lihat Detail
                            </a>


                        </div>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>



        <!-- =================================================
             LIHAT SEMUA
        ================================================== -->

        <div
            style="
                text-align:center;
                margin-top:36px;
            "
        >

            <a
                href="<?= BASE_URL ?>/search.php"
                class="btn btn-outline"
            >
                Lihat Semua Gunung
            </a>

        </div>


    </div>

</section>



<!-- =====================================================
     TRUST BAR
====================================================== -->

<div class="trust-bar">

    <div class="container">


        <!-- TRUST 1 -->

        <div class="trust-item">

            <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <path
                    d="
                        M12 22s8-4 8-10V5
                        l-8-3-8 3v7
                        c0 6 8 10 8 10z
                    "
                />

            </svg>

            Aman &amp; Terpercaya

        </div>



        <!-- TRUST 2 -->

        <div class="trust-item">

            <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <circle
                    cx="12"
                    cy="12"
                    r="10"
                />

                <path
                    d="M12 6v6l4 2"
                />

            </svg>

            Harga Terbaik

        </div>



        <!-- TRUST 3 -->

        <div class="trust-item">

            <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >

                <path
                    d="
                        M22 16.92v3
                        a2 2 0 01-2.18 2
                        19.79 19.79 0 01-8.63-3.07
                        19.5 19.5 0 01-6-6
                        19.79 19.79 0 01-3.07-8.67
                        A2 2 0 014.11 2h3
                        a2 2 0 012 1.72
                        c.13.96.36 1.9.7 2.81
                        a2 2 0 01-.45 2.11
                        L8.09 9.91
                        a16 16 0 006 6
                        l1.27-1.27
                        a2 2 0 012.11-.45
                        c.9.34 1.85.57 2.81.7
                        A2 2 0 0122 16.92z
                    "
                />

            </svg>

            Customer Support 24/7

        </div>


    </div>

</div>



<?php

require_once __DIR__ . '/includes/footer.php';

?>
```
