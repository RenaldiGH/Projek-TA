<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$events = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? null);
$search = trim($_GET['q'] ?? '');

$peserta_list = [];

if ($event_id) {
    if ($search !== '') {
        $stmt = $conn->prepare(
            "SELECT id, nama, email, no_hp, status
             FROM peserta
             WHERE event_id = ? AND (nama LIKE ? OR email LIKE ?)
             ORDER BY nama ASC"
        );
        $like = '%' . $search . '%';
        $stmt->bind_param('iss', $event_id, $like, $like);
    } else {
        $stmt = $conn->prepare(
            "SELECT id, nama, email, no_hp, status
             FROM peserta
             WHERE event_id = ?
             ORDER BY nama ASC"
        );
        $stmt->bind_param('i', $event_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $peserta_list[] = $row;
    }

    $stmt->close();
}

$page_title = 'Data Peserta';
$page_subtitle = 'Kelola Semua Peserta Secret Santa Skariga';
$active_menu = 'peserta';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if (!$events): ?>

    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Buat event terlebih dahulu di menu Event sebelum menambahkan peserta.</p>
        </div>
    </div>

<?php else: ?>

    <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <a href="<?= base_url('pages/peserta/tambah.php?event_id=' . $event_id) ?>" class="btn-add">+ Tambah Peserta</a>
    </div>

    <div class="table-card">
        <?php
        $search_value = $search;
        $search_label = 'Cari Peserta';
        require __DIR__ . '/../../includes/event_selector.php';
        ?>

        <?php if (!$peserta_list): ?>
            <div class="empty-state">
                <h3>Belum Ada Peserta</h3>
                <p>Silahkan tambahkan peserta terlebih dahulu.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No.Hp</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta_list as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?>.</td>
                            <td><?= h($p['nama']) ?></td>
                            <td><?= h($p['email']) ?></td>
                            <td><?= h($p['no_hp']) ?></td>
                            <td>
                                <span class="<?= $p['status'] === 'aktif' ? 'badge-active' : '' ?>">
                                    <?= h(ucfirst($p['status'])) ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="<?= base_url('pages/peserta/detail.php?id=' . $p['id'] . '&event_id=' . $event_id) ?>" title="Detail">👁️</a>
                                <a href="<?= base_url('pages/peserta/edit.php?id=' . $p['id'] . '&event_id=' . $event_id) ?>" class="btn-edit" title="Edit">✏️</a>
                                <a
                                    href="<?= base_url('pages/peserta/hapus.php?id=' . $p['id'] . '&event_id=' . $event_id) ?>"
                                    class="btn-delete"
                                    title="Hapus"
                                    onclick="return confirm('Yakin ingin menghapus peserta ini?');"
                                >🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>