<?php

require_once 'config/config.php';

include 'includes/header.php';

include 'includes/navbar.php';

?>


<!-- =====================================
     HERO
===================================== -->

<section class="hero" id="beranda">

    <div class="hero-container">


        <!-- BAGIAN KIRI -->
        <div class="hero-content">

            <h1>
                SECRET SANTA
            </h1>

            <h2>
                SKARIGA
            </h2>

            <p>
                Tukar kado, berbagi kebahagiaan,<br>
                jalin keakraban bersama teman
            </p>


            <div class="hero-buttons">

                <a
                    href="<?= base_url('login.php') ?>"
                    class="hero-login"
                >
                    LOGIN
                </a>

                <a
                    href="<?= base_url('register.php') ?>"
                    class="hero-register"
                >
                    REGISTER
                </a>

            </div>

        </div>


        <!-- BAGIAN KANAN -->
        <div class="hero-image">

            <img
                src="<?= base_url('assets/img/santa1.png') ?>"
                alt="Santa Secret Santa"
            >

        </div>

    </div>

</section>



<!-- =====================================
     TENTANG SECRET SANTA
===================================== -->

<section class="about" id="tentang">

    <div class="about-container">


        <!-- TEXT -->
        <div class="about-content">

            <h2>
                TENTANG SECRET SANTA
            </h2>

            <p>
                Secret Santa adalah tradisi tukar kado secara acak.
                Setiap peserta akan mendapatkan satu orang,
                penerima kado tanpa mengetahui siapa pengirimnya!
            </p>

            <p>
                Yuk, ikut berpartisipasi dan sebarkan kebahagiaan!
            </p>

        </div>


        <!-- GAMBAR -->
        <div class="about-image">

            <img
                src="<?= base_url('assets/img/santa1.png') ?>"
                alt="Gift"
            >

        </div>


        <!-- TEXT KANAN -->
        <div class="about-slogan">

            <strong>
                berbagi kado,<br>
                berbagi cerita<br>
                berbagi kebahagiaan
            </strong>

        </div>

    </div>

</section>



<!-- =====================================
     CARA KERJA
===================================== -->

<section class="how-section" id="cara-kerja">

    <h2>
        CARA KERJA
    </h2>


    <div class="steps">


        <!-- STEP 1 -->
        <div class="step">

            <div class="step-icon">
                👤
            </div>

            <div class="step-text">

                <h3>
                    1. Daftar
                </h3>

                <p>
                    Buat akun &<br>
                    daftarkan dirimu
                </p>

            </div>

        </div>



        <!-- STEP 2 -->
        <div class="step">

            <div class="step-icon">
                ❤️
            </div>

            <div class="step-text">

                <h3>
                    2. Isi Wishlist
                </h3>

                <p>
                    daftar kado yang<br>
                    km inginkan
                </p>

            </div>

        </div>



        <!-- STEP 3 -->
        <div class="step">

            <div class="step-icon">
                🎁
            </div>

            <div class="step-text">

                <h3>
                    3. Pengundian
                </h3>

                <p>
                    Admin melakukan<br>
                    pengundian otomatis
                </p>

            </div>

        </div>



        <!-- STEP 4 -->
        <div class="step">

            <div class="step-icon">
                🎉
            </div>

            <div class="step-text">

                <h3>
                    4. Tukar Kado
                </h3>

                <p>
                    berikan kado dengan<br>
                    pasanganmu!
                </p>

            </div>

        </div>

    </div>

</section>



<!-- =====================================
     TIMELINE
===================================== -->

<section class="timeline" id="timeline">

    <div class="timeline-container">


        <!-- 1 -->
        <div class="timeline-item">

            <div class="timeline-icon">
                📅
            </div>

            <div>

                <span>
                    1–10 des
                </span>

                <h3>
                    Pendaftaran
                </h3>

                <p>
                    Waktu pendaftaran<br>
                    secret santa
                </p>

            </div>

        </div>



        <!-- 2 -->
        <div class="timeline-item">

            <div class="timeline-icon">
                🎁
            </div>

            <div>

                <span>
                    11–15 des
                </span>

                <h3>
                    Pengisian Wishlist
                </h3>

                <p>
                    isi daftar kado yang<br>
                    km inginkan
                </p>

            </div>

        </div>



        <!-- 3 -->
        <div class="timeline-item">

            <div class="timeline-icon">
                🎲
            </div>

            <div>

                <span>
                    16 desember
                </span>

                <h3>
                    Pengundian
                </h3>

                <p>
                    pengundian<br>
                    pasangan otomatis
                </p>

            </div>

        </div>



        <!-- 4 -->
        <div class="timeline-item">

            <div class="timeline-icon">
                🎄
            </div>

            <div>

                <span>
                    20 des
                </span>

                <h3>
                    Tukar Kado
                </h3>

                <p>
                    saatnya bertukar kado<br>
                    dengan pasanganmu!
                </p>

            </div>

        </div>

    </div>

</section>


<?php

include 'includes/footer.php';

?>