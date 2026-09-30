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
    if ($e['peserta_id']) {
        $total_ikut++;
    }
}

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
        <p><a href="<?= base_url('pages/profil/profiluser.php') ?>">Lihat daftar event</a></p>
    </div>

    <div class="stat">
        <h3>Wishlist Saya</h3>
        <strong><?= $total_wishlist ?> Barang</strong>
        <p><a href="<?= base_url('pages/wishlist/saya.php') ?>">Kelola wishlist</a></p>
    </div>

    <div class="stat">
        <h3>Jadwal Terdekat</h3>
        <?php if ($timeline_dekat): ?>
            <strong style="font-size:18px;"><?= h($timeline_dekat['judul']) ?></strong>
            <p><?= h(tanggal_id($timeline_dekat['tanggal'])) ?> &middot; <?= h($timeline_dekat['nama_event']) ?></p>
        <?php else: ?>
            <strong style="font-size:18px;">Belum ada</strong>
            <p><a href="<?= base_url('pages/timeline/saya.php') ?>">Lihat timeline</a></p>
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
                ?>
                <article class="event-card">
                    <div class="event-cover <?= event_palette((int) $e['id']) ?>">
                        <span class="event-badge <?= $badge[0] ?>"><?= h($badge[1]) ?></span>
                        <svg viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                            <path d="M50 30 C40 10, 15 10, 22 25 C27 34, 42 32, 50 30 C58 32, 73 34, 78 25 C85 10, 60 10, 50 30 Z"/>
                            <rect x="15" y="32" width="70" height="16" rx="2"/>
                            <rect x="20" y="50" width="60" height="42" rx="2"/>
                            <rect x="45" y="32" width="10" height="60" fill="rgba(0,0,0,0.25)"/>
                        </svg>
                    </div>

                    <div class="event-body">
                        <h3><?= h($e['nama_event']) ?></h3>
                        <div class="event-date"><?= h(tanggal_id($e['tanggal_event'])) ?></div>

                        <?php if (!empty($e['deskripsi'])): ?>
                            <p class="event-desc"><?= h($e['deskripsi']) ?></p>
                        <?php endif; ?>

                        <div class="event-price">
                            <?= h(rupiah($e['budget'])) ?><small>budget kado</small>
                        </div>
                        <div class="event-quota">
                            <?= (int) $e['jumlah'] ?> / <?= $max_peserta ?> peserta
                        </div>

                        <form method="POST" action="<?= base_url('pages/event/ikut.php') ?>" class="event-form">
                            <input type="hidden" name="event_id" value="<?= (int) $e['id'] ?>">

                            <?php if ($sudah_ikut && $aktif): ?>
                                <input type="hidden" name="aksi" value="batal">
                                <button type="submit" class="btn btn-danger btn-block"
                                        onclick="return confirm('Batal ikut event ini? Wishlist kamu di event ini ikut terhapus.');">
                                    Batal Ikut
                                </button>
                            <?php elseif ($sudah_ikut): ?>
                                <button type="button" class="btn btn-secondary btn-block" disabled>Terdaftar</button>
                            <?php elseif (!$aktif): ?>
                                <button type="button" class="btn btn-secondary btn-block" disabled>Event Selesai</button>
                            <?php elseif ($penuh): ?>
                                <button type="button" class="btn btn-secondary btn-block" disabled>Kuota Penuh</button>
                            <?php else: ?>
                                <input type="hidden" name="aksi" value="ikut">
                                <button type="submit" class="btn btn-primary btn-block">Ikut Event</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>