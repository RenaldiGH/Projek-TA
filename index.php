
<?php

require_once __DIR__ . '/config/config.php';

// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menyiapkan penyimpanan review
if (!isset($_SESSION['user_reviews'])) {
    $_SESSION['user_reviews'] = [];
}

// Memproses review dari pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_review'])) {

    $nama = trim($_POST['nama'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    $rating = (int) ($_POST['rating'] ?? 0);

    if ($nama === '' || $pesan === '' || $rating < 1 || $rating > 5) {

        $_SESSION['review_message'] = 'Semua kolom wajib diisi!';

    } else {

        $_SESSION['user_reviews'][] = [
            'nama' => $nama,
            'pesan' => $pesan,
            'rating' => $rating
        ];

        $_SESSION['review_message'] = 'Review berhasil dikirim. Terima kasih!';
    }

    header('Location: ' . base_url('index.php') . '#pesan-kesan');
    exit;
}

$review_message = $_SESSION['review_message'] ?? '';
unset($_SESSION['review_message']);

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

<?php include __DIR__ . '/includes/hero_lights.php'; ?>


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

            <div class="about-image">
                <img
                    src="<?= base_url('assets/img/santalanding.png') ?>"
                    alt="Santa membawa kado"
                >
            </div>

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


<div class="about-image">
    <img
        src="<?= base_url('assets/img/Kadolanding.png') ?>"
        alt="Tumpukan kado Secret Santa"
    >
</div>

<div class="about-message">
</div>

</div>
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
                    <img src="<?= base_url('assets/img/emotlove.png') ?>"
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


<!-- SECTION PESAN DAN KESAN -->

<?php
// Mengambil review dari session pengguna
$reviews = $_SESSION['user_reviews'] ?? [];
?>

<section class="review-section" id="pesan-kesan">

    <div class="review-heading">

        <span class="review-label">PESAN DAN KESAN</span>

        <h2>Apa Kata Mereka?</h2>

        <p>
            Terima kasih atas partisipasi dan kesan baik dari seluruh
            peserta Secret Santa SKARIGA. Yuk, simak pesan dan kesan mereka!
        </p>

    </div>


    <!-- FORM TAMBAH REVIEW -->

    <div class="review-form-container">

        <h3>Bagikan Pesan dan Kesanmu!</h3>

        <?php if ($review_message !== ''): ?>

            <div class="review-alert">
                <?= htmlspecialchars($review_message) ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="<?= base_url('index.php#pesan-kesan') ?>">

            <div class="review-name">

                <label>Nama Pengguna</label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Masukkan nama kamu"
                    maxlength="100"
                    required
                >

            </div>


            <div class="review-rating">

                <label>Berikan Rating</label>

                <div class="rating-input">

                    <input type="radio" name="rating" id="star5" value="5" required>
                    <label for="star5">★</label>

                    <input type="radio" name="rating" id="star4" value="4">
                    <label for="star4">★</label>

                    <input type="radio" name="rating" id="star3" value="3">
                    <label for="star3">★</label>

                    <input type="radio" name="rating" id="star2" value="2">
                    <label for="star2">★</label>

                    <input type="radio" name="rating" id="star1" value="1">
                    <label for="star1">★</label>

                </div>

            </div>


            <div class="review-message-input">

                <label>Pesan dan Kesan</label>

                <textarea
                    name="pesan"
                    rows="4"
                    placeholder="Tuliskan pesan dan kesan kamu..."
                    maxlength="1000"
                    required
                ></textarea>

            </div>


            <button type="submit" name="kirim_review">
                Kirim Review
            </button>

        </form>

    </div>

    <!-- AKHIR FORM TAMBAH REVIEW -->


    <div class="review-container">

        <?php foreach ($reviews as $review): ?>

            <div class="review-card">

                <div class="review-top">

                    <div class="review-avatar">
                        <?= strtoupper(substr($review['nama'], 0, 1)) ?>
                    </div>

                    <div class="review-user">

                        <h3>
                            <?= htmlspecialchars($review['nama']) ?>
                        </h3>

                        <span class="review-status">Peserta</span>

                    </div>

                </div>


                <div class="review-stars">

                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                        <span>★</span>
                    <?php endfor; ?>

                </div>


                <p class="review-message">
                    "<?= htmlspecialchars($review['pesan']) ?>"
                </p>

                <div class="review-decoration"></div>

            </div>

        <?php endforeach; ?>

    </div>


    <div class="review-footer">

        <h3>
            Terima Kasih Telah Menjadi Bagian dari
            Secret Santa SKARIGA!
        </h3>

        <p>
            Berbagi kado, berbagi cerita, berbagi kebahagiaan.
        </p>

    </div>

</section>

<!-- AKHIR SECTION PESAN DAN KESAN -->


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

            <span class="footer-follow-title">IKUTI KAMI DI</span>

            <!-- Instagram -->
            <a href="#"
               class="footer-social-icon"
               aria-label="Instagram Secret Santa">

                <img
                    src="<?= base_url('assets/img/instagram.webp') ?>"
                    alt="Instagram"
                >

            </a>

            <!-- Garis pemisah -->
            <div class="footer-divider"></div>

            <!-- Kontak Us -->
            <span class="footer-contact-title">KONTAK US</span>

            <a href="mailto:secret.santa@gmail.com"
               class="footer-contact-link">

                <svg xmlns="http://www.w3.org/2000/svg"
                     width="25"
                     height="25"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="m3 7 9 6 9-6"/>

                </svg>

                <span>secret.santa@gmail.com</span>

            </a>

        </div>

    </div>

</footer>
