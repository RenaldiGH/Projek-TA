<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

//agar event_id ikut
$event_id = (int) ($_GET['event_id'] ?? 0);

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT id, nama_event, deskripsi, budget, tanggal_event, status
         FROM events
         WHERE nama_event LIKE ?
         ORDER BY tanggal_event DESC, id DESC"
    );
    $like = '%' . $search . '%';
    $stmt->bind_param('s', $like);
} else {
    $stmt = $conn->prepare(
        "SELECT id, nama_event, deskripsi, budget, tanggal_event, status
         FROM events
         ORDER BY tanggal_event DESC, id DESC"
    );
}
$stmt->execute();
$result = $stmt->get_result();
$event_list = [];
while ($row = $result->fetch_assoc()) {
    $event_list[] = $row;
}
$stmt->close();

$page_title = 'Data Event';
$page_subtitle = 'Kelola Semua Event Secreet Santa Skariga';
$active_menu = 'event';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
    <a href="<?= base_url('pages/event/tambah.php') ?>" class="btn-add">+ Tambah Event</a>
</div>

<div class="table-card">
    <form method="GET" class="filter-bar">
        <div class="filter-group">
            <label for="q">&nbsp;</label>
            <input type="text" name="q" id="q" placeholder="Cari Event" value="<?= h($search) ?>">
        </div>
    </form>

    <?php if (!$event_list): ?>
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Silahkan tambahkan event terlebih dahulu.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">NO</th>
                    <th>Nama Event</th>
                    <th>Tanggal</th>
                    <th>Budget</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($event_list as $i => $e): ?>
                    <tr>
                        <td><?= $i + 1 ?>.</td>
                        <td><?= h($e['nama_event']) ?></td>
                        <td><?= $e['tanggal_event'] ? date('d M Y', strtotime($e['tanggal_event'])) : '-' ?></td>
                        <td>Rp<?= number_format((float) $e['budget'], 0, ',', '.') ?></td>
                        <td><?= h($e['deskripsi']) ?></td>
                        <td>
                            <span class="<?= $e['status'] === 'aktif' ? 'badge-active' : ($e['status'] === 'selesai' ? 'badge-done' : '') ?>">
                                <?= h(ucfirst($e['status'])) ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <a href="<?= base_url('pages/peserta/index.php?event_id=' . $e['id']) ?>" title="Lihat Peserta">👁️</a>
                            <a href="<?= base_url('pages/event/edit.php?id=' . $e['id']) ?>" class="btn-edit" title="Edit">✏️</a>
                            <a href="<?= base_url('pages/event/hapus.php?id=' . $e['id']) ?>"
                                class="btn-delete"
                                title="Hapus"
                                onclick="return confirm('Yakin ingin menghapus event ini?');"
                            >🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>