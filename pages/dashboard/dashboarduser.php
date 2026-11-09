```php
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$user_id = (int) $_SESSION['user_id'];
$today   = date('Y-m-d');

$events      = get_events_for_user($conn, $user_id);
$max_peserta = get_max_peserta($conn);

$total_ikut = 0;
foreach ($events as $e) {
    if (!empty($e['peserta_id'])) {
        $total_ikut++;
    }
}

// Menghitung wishlist pengguna
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM wishlist w
     JOIN peserta p ON p.id = w.peserta_id
     WHERE p.user_id = ?"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$total_wishlist = (int) $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Mengambil jadwal terdekat
$stmt = $conn->prepare(
    "SELECT t.judul, t.tanggal, e.nama_event
     FROM timeline t
     JOIN peserta p ON p.event_id = t.event_id
     JOIN events e ON e.id = t.event_id
     WHERE p.user_id = ? AND t.tanggal >= ?
     ORDER BY t.tanggal ASC, t.waktu ASC
     LIMIT 1"
);
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$timeline_dekat = $stmt->get_result()->fetch_assoc();
$stmt->close();

$page_title    = 'Halo, ' . ($_SESSION['nama'] ?? 'Peserta') . '!';
$page_subtitle = 'Selamat datang di Secret Santa SKARIGA';
$active_menu   = 'dashboard';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<section class="stats">

    <div class="stat">
        <h3>Event Diikuti</h3>
        <strong><?= $total_ikut ?> Event</strong>
        <p>
            <a href="<?= base_url('pages/profil/profiluser.php') ?>">
                Lihat daftar event
            </a>
        </p>
    </div>

    <div class="stat">
        <h3>Wishlist Saya</h3>
        <strong><?= $total_wishlist ?> Barang</strong>
        <p>
            <a href="<?= base_url('pages/wishlist/saya.php') ?>">
                Kelola wishlist
            </a>
        </p>
    </div>

    <div class="stat">
        <h3>Jadwal Terdekat</h3>

        <?php if ($timeline_dekat): ?>

            <strong style="font-size:18px;">
                <?= h($timeline_dekat['judul']) ?>
            </strong>

            <p>
                <?= h(tanggal_id($timeline_dekat['tanggal'])) ?>
                &middot;
                <?= h($timeline_dekat['nama_event']) ?>
            </p>

        <?php else: ?>

            <strong style="font-size:18px;">Belum ada</strong>

            <p>
                <a href="<?= base_url('pages/timeline/saya.php') ?>">
                    Lihat timeline
                </a>
            </p>

        <?php endif; ?>
    </div>

</section>


<section class="section">

    <div class="section-head">
        <h2>Pilih Event</h2>
        <span>Kamu boleh ikut lebih dari satu event</span>
    </div>

    <?php if (!$events): ?>

        <div class="panel">
            <div class="empty">
                <h3>Belum Ada Event</h3>
                <p>Belum ada event yang dibuka. Coba cek lagi nanti ya.</p>
            </div>
        </div>

    <?php else: ?>

        <div class="event-grid">

            <?php foreach ($events as $e): ?>

                <?php
                $sudah_ikut = !empty($e['peserta_id']);
                $aktif      = $e['status'] === 'aktif';
                $penuh      = (int) $e['jumlah'] >= $max_peserta;

                if ($sudah_ikut) {
                    $badge = ['joined', 'Sudah Ikut'];
                } elseif (!$aktif) {
                    $badge = ['done', 'Selesai'];
                } else {
                    $badge = ['', 'Event Aktif'];
                }

                // Mengambil daftar peserta pada setiap event
                $stmt_peserta = $conn->prepare(
                    "SELECT u.nama
                     FROM peserta p
                     JOIN users u ON u.id = p.user_id
                     WHERE p.event_id = ?
                     ORDER BY u.nama ASC"
                );

                $event_id = (int) $e['id'];

                $stmt_peserta->bind_param('i', $event_id);
                $stmt_peserta->execute();

                $daftar_peserta = $stmt_peserta->get_result()->fetch_all(MYSQLI_ASSOC);

                $stmt_peserta->close();
                ?>

                <article class="event-card">

                    <div class="event-cover <?= event_palette((int) $e['id']) ?>">

                        <span class="event-badge <?= $badge[0] ?>">
                            <?= h($badge[1]) ?>
                        </span>

                        <svg viewBox="0 0 100 100"
                             fill="currentColor"
                             aria-hidden="true">

                            <path d="M50 30 C40 10, 15 10, 22 25 C27 34, 42 32, 50 30 C58 32, 73 34, 78 25 C85 10, 60 10, 50 30 Z"/>

                            <rect x="15" y="32" width="70" height="16" rx="2"/>

                            <rect x="20" y="50" width="60" height="42" rx="2"/>

                            <rect x="45" y="32" width="10" height="60"
                                  fill="rgba(0,0,0,0.25)"/>

                        </svg>

                    </div>


                    <div class="event-body">

                        <h3>
                            <a class="event-link" href="<?= base_url('pages/event/review.php?id=' . (int) $e['id']) ?>">
                                <?= h($e['nama_event']) ?>
                            </a>
                        </h3>

                        <div class="event-date">
                            <?= h(tanggal_id($e['tanggal_event'])) ?>
                        </div>

                        <?php if (!empty($e['deskripsi'])): ?>

                            <p class="event-desc">
                                <?= h($e['deskripsi']) ?>
                            </p>

                        <?php endif; ?>


                        <div class="event-price">
                            <?= h(rupiah($e['budget'])) ?>
                            <small>budget kado</small>
                        </div>


                        <div class="event-quota">
                            <?= (int) $e['jumlah'] ?> /
                            <?= $max_peserta ?> peserta
                            &middot;
                            <span class="event-more">Lihat detail &rarr;</span>
                        </div>


                        <!-- INFORMASI BIAYA PENDAFTARAN -->
                        <?php if (isset($e['biaya']) && is_numeric($e['biaya'])): ?>

                            <div class="event-quota">
                                Biaya pendaftaran:

                                <?php if ((float) $e['biaya'] == 0): ?>

                                    <strong>Gratis</strong>

                                <?php else: ?>

                                    <strong>
                                        <?= h(rupiah($e['biaya'])) ?>
                                    </strong>

                                <?php endif; ?>
                            </div>

                        <?php endif; ?>


                        <!-- TOMBOL LIHAT DETAIL EVENT -->
                        <button type="button"
                                class="btn-detail-event"
                                data-nama="<?= h($e['nama_event']) ?>"
                                data-deskripsi="<?= h($e['deskripsi'] ?? 'Belum ada deskripsi event.') ?>"
                                data-tanggal="<?= h(tanggal_id($e['tanggal_event'])) ?>"
                                data-budget="<?= h(rupiah($e['budget'])) ?>"
                                onclick="bukaDetailEvent(this)">

                            Lihat Detail Event

                        </button>


                        <!-- DAFTAR PESERTA EVENT -->
                        <div class="event-quota">

                            <strong>Peserta yang Bergabung:</strong>

                            <?php if (!empty($daftar_peserta)): ?>

                                <ul>

                                    <?php foreach ($daftar_peserta as $peserta): ?>

                                        <li>
                                            <?= h($peserta['nama']) ?>
                                        </li>

                                    <?php endforeach; ?>

                                </ul>

                            <?php else: ?>

                                <p>Belum ada peserta yang bergabung.</p>

                            <?php endif; ?>

                        </div>


                        <!-- TOMBOL IKUT EVENT -->
                        <form method="POST"
                              action="<?= base_url('pages/event/ikut.php') ?>"
                              class="event-form">

                            <input type="hidden"
                                   name="event_id"
                                   value="<?= (int) $e['id'] ?>">

                            <?php if ($sudah_ikut && $aktif): ?>

                                <input type="hidden"
                                       name="aksi"
                                       value="batal">

                                <button type="submit"
                                        class="btn btn-danger btn-block"
                                        onclick="return confirm('Batal ikut event ini? Wishlist kamu di event ini ikut terhapus.');">

                                    Batal Ikut

                                </button>

                            <?php elseif ($sudah_ikut): ?>

                                <button type="button"
                                        class="btn btn-secondary btn-block"
                                        disabled>

                                    Terdaftar

                                </button>

                            <?php elseif (!$aktif): ?>

                                <button type="button"
                                        class="btn btn-secondary btn-block"
                                        disabled>

                                    Event Selesai

                                </button>

                            <?php elseif ($penuh): ?>

                                <button type="button"
                                        class="btn btn-secondary btn-block"
                                        disabled>

                                    Kuota Penuh

                                </button>

                            <?php else: ?>

                                <input type="hidden"
                                       name="aksi"
                                       value="ikut">

                                <button type="submit"
                                        class="btn btn-primary btn-block">

                                    Ikut Event

                                </button>

                            <?php endif; ?>

                        </form>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>




<div id="modalDetailEvent" class="modal-detail-event">

    <div class="modal-detail-content">

        <button type="button"
                class="modal-detail-close"
                onclick="tutupDetailEvent()">

            &times;

        </button>

        <div class="modal-detail-header">

            <span>INFORMASI EVENT</span>

            <div class="modal-gift"></div>

        </div>


        <div class="modal-detail-body">

            <h2 id="detailNamaEvent"></h2>

            <p class="detail-tanggal">
                <span id="detailTanggalEvent"></span>
            </p>


            <div class="detail-info-box">

                <h3>Deskripsi Event</h3>

                <p id="detailDeskripsiEvent"></p>

            </div>


            <div class="detail-budget-box">

                <span>Budget Kado</span>

                <strong id="detailBudgetEvent"></strong>

            </div>

        </div>


        <button type="button"
                class="btn-tutup-detail"
                onclick="tutupDetailEvent()">

            Tutup

        </button>

    </div>

</div>


<script>

function bukaDetailEvent(button) {

    document.getElementById('detailNamaEvent').textContent =
        button.dataset.nama;

    document.getElementById('detailTanggalEvent').textContent =
        button.dataset.tanggal;

    document.getElementById('detailDeskripsiEvent').textContent =
        button.dataset.deskripsi;

    document.getElementById('detailBudgetEvent').textContent =
        button.dataset.budget;

    document.getElementById('modalDetailEvent').style.display = 'flex';

}


function tutupDetailEvent() {

    document.getElementById('modalDetailEvent').style.display = 'none';

}


document.getElementById('modalDetailEvent').addEventListener('click', function(e) {

    if (e.target === this) {
        tutupDetailEvent();
    }

});


document.addEventListener('keydown', function(e) {

    if (e.key === 'Escape') {
        tutupDetailEvent();
    }

});

</script>


<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>
```