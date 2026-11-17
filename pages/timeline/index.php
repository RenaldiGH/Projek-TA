<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$events = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? null);

$search = trim($_GET['q'] ?? '');
$timeline_list = [];

if ($event_id) {
    if ($search !== '') {
        $stmt = $conn->prepare(
            "SELECT id, judul, tanggal, waktu, deskripsi
             FROM timeline
             WHERE event_id = ? AND (judul LIKE ? OR deskripsi LIKE ?)
             ORDER BY tanggal ASC, waktu ASC"
        );
        $like = '%' . $search . '%';
        $stmt->bind_param('iss', $event_id, $like, $like);
    } else {
        $stmt = $conn->prepare(
            "SELECT id, judul, tanggal, waktu, deskripsi
             FROM timeline
             WHERE event_id = ?
             ORDER BY tanggal ASC, waktu ASC"
        );
        $stmt->bind_param('i', $event_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $timeline_list[] = $row;
    }

    $stmt->close();
}

$page_title = 'Data Timeline';
$page_subtitle = 'Kelola Semua Timeline';
$active_menu = 'timeline';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if (!$events): ?>
    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Buat event terlebih dahulu sebelum mengatur timeline.</p>
        </div>
    </div>

<?php else: ?>

    <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
      <a href="<?= base_url('pages/timeline/tambah.php?event_id=' . $event_id) ?>" class="btn-add" data-modal-event>+ Tambah Timeline</a>
    </div>

    <div class="table-card">
        <form method="GET" class="filter-bar">
            <div class="filter-group">
                <label for="event_id">Pilih Event</label>
                <select name="event_id" id="event_id" onchange="this.form.submit()">
                    <?php foreach ($events as $event): ?>
                        <option value="<?= (int) $event['id'] ?>" <?= (int) $event_id === (int) $event['id'] ? 'selected' : '' ?>>
                            <?= h($event['nama_event']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="q">&nbsp;</label>
                <input type="text" name="q" id="q" placeholder="Cari judul timeline" value="<?= h($search) ?>">
            </div>
        </form>

        <?php if (!$timeline_list): ?>
            <div class="empty-state">
                <?php if ($search !== ''): ?>
                    <h3>Tidak Ditemukan</h3>
                    <p>Tidak ada timeline yang cocok dengan "<?= h($search) ?>".</p>
                <?php else: ?>
                <h3>Belum Ada Timeline</h3>
                <p>Silahkan tambahkan timeline untuk event ini.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="data-table table-grid">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Deskripsi</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timeline_list as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1 ?>.</td>
                            <td><?= h($t['judul']) ?></td>
                            <td><?= $t['tanggal'] ? date('d M Y', strtotime($t['tanggal'])) : '-' ?></td>
                            <td><?= $t['waktu'] ? substr($t['waktu'], 0, 5) : '-' ?></td>
                            <td class="col-deskripsi">
                                <div class="rich-content"><?= rich_ringkas_html($t['deskripsi']) ?></div>
                            </td>
                            <td class="action-buttons">
                                <a href="<?= base_url('pages/timeline/edit.php?id=' . $t['id'] . '&event_id=' . $event_id) ?>" class="btn-edit" title="Edit" data-modal-event data-judul="Edit Timeline">✏️</a>
                                <a href="#" class="btn-delete" title="Hapus" data-hapus="<?= base_url('pages/timeline/hapus.php') ?>" data-id="<?= (int) $t['id'] ?>" data-event="<?= (int) $event_id ?>" data-pesan="Yakin ingin menghapus timeline ini?">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php endif; ?>
<link rel="stylesheet" href="<?= base_url('assets/css/rich-editor.css') ?>">
<script src="<?= base_url('assets/js/rich-editor.js') ?>"></script>
<script src="<?= base_url('assets/js/modal-event.js') ?>"></script>
<?= script_hapus_post() ?>
<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>
