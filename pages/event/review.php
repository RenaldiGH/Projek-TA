<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$user_id  = (int) $_SESSION['user_id'];
$event_id = (int) ($_GET['id'] ?? 0);
$today    = date('Y-m-d');
$back     = base_url('pages/dashboard/dashboarduser.php');

/* ---------- Data event (draft tidak boleh dilihat peserta) ---------- */
$stmt = $conn->prepare(
    "SELECT e.id, e.nama_event, e.deskripsi, e.budget, e.biaya_pendaftaran, e.tanggal_event, e.status, u.nama AS penyelenggara
     FROM events e
     LEFT JOIN users u ON u.id = e.user_id
     WHERE e.id = ? AND e.status <> 'draft'"
);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    set_flash('error', 'Event tidak ditemukan.');
    header('Location: ' . $back);
    exit;
}

/* ---------- Daftar peserta (hanya nama dan tanggal bergabung) ---------- */
$stmt = $conn->prepare(
    "SELECT id, user_id, nama, created_at
     FROM peserta
     WHERE event_id = ? AND status = 'aktif'
     ORDER BY created_at ASC, id ASC"
);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$daftar_peserta = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$jumlah      = count($daftar_peserta);
$max_peserta = get_max_peserta($conn);
$persen      = $max_peserta > 0 ? min(100, (int) round($jumlah / $max_peserta * 100)) : 0;

$peserta_saya = null;
foreach ($daftar_peserta as $p) {
    if ((int) $p['user_id'] === $user_id) {
        $peserta_saya = $p;
        break;
    }
}
$sudah_ikut = $peserta_saya !== null;

/* ---------- Jadwal event ---------- */
$stmt = $conn->prepare(
    "SELECT judul, tanggal, waktu, deskripsi
     FROM timeline
     WHERE event_id = ?
     ORDER BY (tanggal IS NULL) ASC, tanggal ASC, waktu ASC, id ASC"
);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$index_berikutnya = null;
foreach ($jadwal as $i => $j) {
    if ($j['tanggal'] && $j['tanggal'] >= $today) {
        $index_berikutnya = $i;
        break;
    }
}

/* ---------- Status pengundian (hasilnya tetap rahasia) ---------- */
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengundian WHERE event_id = ?");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$sudah_diundi = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
$stmt->close();

/* ---------- Wishlist milik user di event ini ---------- */
$total_wishlist = 0;
if ($sudah_ikut) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM wishlist WHERE peserta_id = ?");
    $stmt->bind_param('i', $peserta_saya['id']);
    $stmt->execute();
    $total_wishlist = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

$aktif = $event['status'] === 'aktif';
$penuh = $jumlah >= $max_peserta;

$label_status = $aktif ? ['Event Aktif', ''] : ['Event Selesai', 'done'];
if ($sudah_ikut) {
    $label_status = ['Sudah Ikut', 'joined'];
}

$page_title    = $event['nama_event'];
$page_subtitle = 'Review event: info, peserta, dan jadwal.';
$active_menu   = 'dashboard';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<p class="rv-back"><a href="<?= $back ?>">&larr; Kembali ke daftar event</a></p>

<section class="rv-hero">
    <div class="rv-cover <?= event_palette((int) $event['id']) ?>">
        <span class="event-badge <?= $label_status[1] ?>"><?= h($label_status[0]) ?></span>
        <svg viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
            <path d="M50 30 C40 10, 15 10, 22 25 C27 34, 42 32, 50 30 C58 32, 73 34, 78 25 C85 10, 60 10, 50 30 Z"/>
            <rect x="15" y="32" width="70" height="16" rx="2"/>
            <rect x="20" y="50" width="60" height="42" rx="2"/>
            <rect x="45" y="32" width="10" height="60" fill="rgba(0,0,0,0.25)"/>
        </svg>
    </div>

    <div class="rv-info">
        <dl class="rv-facts">
            <div>
                <dt>Tanggal Event</dt>
                <dd><?= h(tanggal_id($event['tanggal_event'])) ?></dd>
            </div>
            <div>
                <dt>Budget Kado</dt>
                <dd class="rv-price"><?= h(rupiah($event['budget'])) ?></dd>
            </div>

            <div>
    <dt>Biaya Pendaftaran</dt>
    <dd><?= (float) $event['biaya_pendaftaran'] == 0 ? 'Gratis' : h(rupiah($event['biaya_pendaftaran'])) ?></dd>
</div>
            <div>
                <dt>Penyelenggara</dt>
                <dd><?= h($event['penyelenggara'] ?? '-') ?></dd>
            </div>
            <div>
                <dt>Pengundian</dt>
                <dd>
                    <?php if ($sudah_diundi): ?>
                        <span class="tag ok">Sudah dilakukan</span>
                    <?php else: ?>
                        <span class="tag">Belum dilakukan</span>
                    <?php endif; ?>
                </dd>
            </div>
        </dl>

        <?php if (!empty($event['deskripsi'])): ?>
            <p class="rv-desc"><?= nl2br(h($event['deskripsi'])) ?></p>
        <?php endif; ?>

        <div class="rv-quota">
            <div class="rv-quota-text">
                <strong><?= $jumlah ?> / <?= $max_peserta ?></strong> peserta terdaftar
            </div>
            <div class="rv-bar"><span style="width: <?= $persen ?>%"></span></div>
        </div>

        <form method="POST" action="<?= base_url('pages/event/ikut.php') ?>" class="rv-action">
            <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
            <input type="hidden" name="kembali" value="review">

            <?php if ($sudah_ikut && $aktif && !$sudah_diundi): ?>
                <input type="hidden" name="aksi" value="batal">
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Batal ikut event ini? Wishlist kamu di event ini ikut terhapus.');">
                    Batal Ikut
                </button>
            <?php elseif ($sudah_ikut): ?>
                <button type="button" class="btn btn-secondary" disabled>
                    <?= $sudah_diundi ? 'Terdaftar (sudah diundi)' : 'Terdaftar' ?>
                </button>
            <?php elseif (!$aktif): ?>
                <button type="button" class="btn btn-secondary" disabled>Event Selesai</button>
            <?php elseif ($penuh): ?>
                <button type="button" class="btn btn-secondary" disabled>Kuota Penuh</button>
            <?php else: ?>
                <input type="hidden" name="aksi" value="ikut">
                <button type="submit" class="btn btn-primary">Ikut Event</button>
            <?php endif; ?>

            <?php if ($sudah_ikut): ?>
                <a href="<?= base_url('pages/wishlist/saya.php?event_id=' . (int) $event['id']) ?>" class="btn btn-secondary">
                    Wishlist Saya (<?= $total_wishlist ?>)
                </a>
            <?php endif; ?>
        </form>
    </div>
</section>

<div class="rv-columns">

    <section class="panel">
        <h2>Peserta yang Ikut <span class="rv-count"><?= $jumlah ?></span></h2>

        <?php if (!$daftar_peserta): ?>
            <div class="empty">
                <h3>Belum Ada Peserta</h3>
                <p>Jadilah yang pertama ikut event ini.</p>
            </div>
        <?php else: ?>
            <ul class="rv-people">
                <?php foreach ($daftar_peserta as $n => $p): ?>
                    <li>
                        <span class="rv-avatar"><?= h(strtoupper(mb_substr($p['nama'], 0, 1))) ?></span>
                        <div class="rv-person">
                            <strong>
                                <?= h($p['nama']) ?>
                                <?php if ((int) $p['user_id'] === $user_id): ?>
                                    <span class="tag ok">Kamu</span>
                                <?php endif; ?>
                            </strong>
                            <small>Bergabung <?= h(tanggal_id(substr($p['created_at'], 0, 10), true)) ?></small>
                        </div>
                        <span class="rv-no">#<?= $n + 1 ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Jadwal Event <span class="rv-count"><?= count($jadwal) ?></span></h2>

        <?php if (!$jadwal): ?>
            <div class="empty">
                <h3>Belum Ada Jadwal</h3>
                <p>Panitia belum menambahkan jadwal untuk event ini.</p>
            </div>
        <?php else: ?>
            <ol class="timeline rv-timeline">
                <?php foreach ($jadwal as $i => $j): ?>
                    <?php
                    $lewat = $j['tanggal'] && $j['tanggal'] < $today;
                    $class = $lewat ? 'past' : ($i === $index_berikutnya ? 'next' : '');
                    ?>
                    <li class="tl-item <?= $class ?>">
                        <?php if ($j['tanggal']): ?>
                            <div class="tl-date">
                                <?= h(tanggal_id($j['tanggal'])) ?>
                                <?php if ($j['waktu']): ?>
                                    &middot; <?= h(substr($j['waktu'], 0, 5)) ?> WIB
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h3><?= h($j['judul']) ?></h3>
                        <?php if (!empty($j['deskripsi'])): ?>
                            <p><?= h($j['deskripsi']) ?></p>
                        <?php endif; ?>
                        <?php if ($class === 'next'): ?>
                            <div class="tl-meta"><span class="tag ok">Berikutnya</span></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>

</div>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>