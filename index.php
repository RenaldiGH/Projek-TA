<?php

require_once __DIR__ . '/config/config.php';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

?>


<section class="hero" id="beranda">

    <div class="hero-container">

        <div class="hero-content">

            <h1>SECRET SANTA</h1>

            <h2>SKARIGA</h2>

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


        <div class="hero-image">

            <img
                src="<?= base_url('assets/img/halaman utama.png') ?>"
                alt="Santa Secret Santa"
            >

        </div>

    </div>

</section>


<section class="about" id="tentang">

    <div class="about-container">

        <div class="about-text">

            <h2>TENTANG SECRET SANTA</h2>

            <p>
                Secret santa adalah tradisi tukar kado secara acak.
                <br>
                Setiap peserta akan mendapatkan satu orang, penerima
                <br>
                kado tanpa mengetahui siapa pengirimnya!
                <br>
                Yuk, ikut berpartisipasi dan sebarkan kebahagiaan!
            </p>

        </div>

        <div class="about-image">
            <img
                src="<?= base_url('assets/img/santalanding.png') ?>"
                alt="Santa membawa kado"
            >
        </div>

        <div class="about-message">

            <h3>
                berbagi kado,<br>
                berbagi cerita<br>
                berbagi kebahagiaan
            </h3>

        </div>

    </div>

</section>

<section class="about-gifts" aria-label="Tumpukan kado Secret Santa">
    <img
        src="<?= base_url('assets/img/Kadolanding.png') ?>"
        alt="Tumpukan kado Secret Santa"
    >
</section>
<section class="work" id="cara-kerja">
    <div class="work-box">

        <h2>Cara Kerja</h2>

        <div class="work-items">

            <div class="work-item">
                <div class="work-image">
                    <img src="<?= base_url('assets/img/fotoprofile.png') ?>"
                         alt="Daftar akun">
                </div>

                <div class="work-content">
                    <h3>1. Daftar</h3>
                    <p>Buat akun <br>daftarkan dirimu</p>
                </div>
            </div>

            <div class="work-item">
                <div class="work-image">
                    <img src="<?= base_url('assets/img/emotlovemerah.jpg') ?>"
                         alt="Mengisi wishlist">
                </div>

                <div class="work-content">
                    <h3>2. Isi Wishlist</h3>
                    <p>Daftar kado yang<br>kamu inginkan</p>
                </div>
            </div>

            <div class="work-item">
                <div class="work-image">
                    <img src="<?= base_url('assets/img/pengundian.png') ?>"
                         alt="Pengundian otomatis">
                </div>

                <div class="work-content">
                    <h3>3. Pengundian</h3>
                    <p>Admin melakukan<br>pengundian otomatis</p>
                </div>
            </div>

            <div class="work-item">
                <div class="work-image">
                    <img src="<?= base_url('assets/img/surprise.png') ?>"
                         alt="Tukar kado">
                </div>

                <div class="work-content">
                    <h3>4. Tukar Kado</h3>
                    <p>Berikan kado dengan<br>pasanganmu!</p>
                </div>
            </div>

        </div>
    </div>
</section>


<section class="timeline" id="timeline">

    <div class="timeline-container">

        <div class="timeline-item">
            <div class="timeline-date">
                <img src="<?= base_url('assets/img/kalender.png') ?>"
                     alt="Kalender pendaftaran">
            </div>

            <div class="timeline-content">
                <h3>1–10 Des<br>Pendaftaran</h3>
                <p>Waktu pendaftaran<br>Secret Santa</p>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-date">
                <img src="<?= base_url('assets/img/pengundian.png') ?>"
                     alt="Kado wishlist">
            </div>

            <div class="timeline-content">
                <h3>11–15 Des<br>Pengisian Wishlist</h3>
                <p>Isi daftar kado yang<br>kamu inginkan</p>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-date">
                <img src="<?= base_url('assets/img/dadu1.png') ?>"
                     alt="Dadu pengundian">
            </div>

            <div class="timeline-content">
                <h3>16 Desember<br>Pengundian</h3>
                <p>Pengundian<br>pasangan otomatis</p>
            </div>
        </div>

        <div class="timeline-item">
            <div class="timeline-date">
                <img src="<?= base_url('assets/img/pohonnatal.png') ?>"
                     alt="Pohon Natal tukar kado">
            </div>

            <div class="timeline-content">
                <h3>20 Desember<br>Tukar Kado</h3>
                <p>Saatnya bertukar kado<br>dengan pasanganmu!</p>
            </div>
        </div>

    </div>
</section>



    <footer class="footer">
        <div class="footer-container">

            <div class="footer-left">
                SKARIGA SECRET SANTA 2026
            </div>

            <div class="footer-center">
                MARI RAYAKAN KEBERSAMAAN DENGAN<br>
                TUKAR KADO YANG BERKESAN
            </div>

            <div class="footer-right">
                <span>IKUTI KAMI DI</span>

                <a href="#" aria-label="Instagram Secret Santa">
                    <img src="<?= base_url('assets/img/instagram.png') ?>"
                        alt="Instagram">
                </a>
            </div>

        </div>
    </footer>
