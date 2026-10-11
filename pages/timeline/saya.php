<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$user_id = (int) $_SESSION['user_id'];
$today   = date('Y-m-d');

$my_events = get_user_events($conn, $user_id);

//filter event
$filter = (int) ($_GET['event_id'] ?? 0);
$valid  = array_map('intval', array_column($my_events, 'id'));

if ($filter && !in_array($filter, $valid, true)) {
    $filter = 0;
}

$jadwal = [];

if ($my_events) {

    //jadwal
    $stmt = $conn->prepare(
        "SELECT t.event_id, t.judul, t.tanggal, t.waktu, t.deskripsi, e.nama_event
         FROM timeline t
         JOIN peserta p ON p.event_id = t.event_id
         JOIN events e ON e.id = t.event_id
         WHERE p.user_id = ?"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $jadwal[] = [
            'event_id'  => (int) $row['event_id'],
            'judul'     => $row['judul'],
            'tanggal'   => $row['tanggal'],
            'waktu'     => $row['waktu'],
            'deskripsi' => $row['deskripsi'],
            'event'     => $row['nama_event'],
            'hari_h'    => false,
        ];
    }
    $stmt->close();


    if ($filter) {
        $jadwal = array_values(array_filter($jadwal, fn($j) => $j['event_id'] === $filter));
    }
//urutan jadwal
    usort($jadwal, function ($a, $b) {
        return [$a['tanggal'] ?? '9999-12-31', $a['waktu'] ?? '00:00:00']
           <=> [$b['tanggal'] ?? '9999-12-31', $b['waktu'] ?? '00:00:00'];
    });
}

$index_berikutnya = null;
foreach ($jadwal as $i => $j) {
    if ($j['tanggal'] && $j['tanggal'] >= $today) {
        $index_berikutnya = $i;
        break;
    }
}

$page_title    = 'Timeline Saya';
$page_subtitle = 'Jadwal dari event yang kamu ikuti.';
$active_menu   = 'timeline';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<?php if (!$my_events): ?>

    <div class="panel">
        <div class="empty">
            <h3>Kamu Belum Ikut Event</h3>
            <p>Timeline akan muncul setelah kamu mengikuti event bosq</p>
            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>" class="btn btn-primary">Pilih Event</a>
        </div>
    </div>

<?php else: ?>

    <form method="GET" class="filter-bar">
        <label for="event_id">Tampilkan</label>
        <select name="event_id" id="event_id" onchange="this.form.submit()">
            <option value="0">Semua event yang diikuti</option>
            <?php foreach ($my_events as $e): ?>
                <option value="<?= (int) $e['id'] ?>" <?= (int) $e['id'] === $filter ? 'selected' : '' ?>>
                    <?= h($e['nama_event']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!$jadwal): ?>

        <div class="panel">
            <div class="empty">
                <h3>Belum Ada Jadwal</h3>
                <p>Panitia belum menambahkan jadwal untuk event ini.</p>
            </div>
        </div>

    <?php else: ?>

        <ol class="timeline">
            <?php foreach ($jadwal as $i => $j): ?>
                <?php
                $lewat = $j['tanggal'] && $j['tanggal'] < $today;
                $class = $lewat ? 'past' : ($i === $index_berikutnya ? 'next' : '');
                ?>
                <li class="tl-item <?= $class ?>">
                    <div class="tl-date">
                        <?= h(tanggal_id($j['tanggal'])) ?>
                        <?php if ($j['waktu']): ?>
                            &middot; <?= h(substr($j['waktu'], 0, 5)) ?> WIB
                        <?php endif; ?>
                    </div>

                    <h3><?= h($j['judul']) ?></h3>

                    <?php if (!empty($j['deskripsi'])): ?>
                        <p><?= h($j['deskripsi']) ?></p>
                    <?php endif; ?>

                    <div class="tl-meta">
                        <span class="tag"><?= h($j['event']) ?></span>
                        <?php if ($class === 'next'): ?>
                            <span class="tag ok">Berikutnya</span>
                        <?php elseif ($lewat): ?>
                            <span class="tag off">Sudah lewat</span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>

    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>