<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$events = get_events($conn);
$event_id = resolve_event_id($conn, $_GET['event_id'] ?? null);
$search = trim($_GET['q'] ?? '');

$wishlist_list = [];

if ($event_id) {
    $sql = "SELECT w.id, w.nama_barang, w.kategori, w.estimasi_harga, w.status, p.nama AS peserta_nama
            FROM wishlist w
            JOIN peserta p ON p.id = w.peserta_id
            WHERE p.event_id = ?";
    $params = [$event_id];
    $types = 'i';

    if ($search !== '') {
        $sql .= " AND (p.nama LIKE ? OR w.nama_barang LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }

    $sql .= " ORDER BY p.nama ASC, w.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $wishlist_list[] = $row;
    }

    $stmt->close();
}

$page_title = 'Data Wishlist';
$page_subtitle = 'Kelola Semua Daftar Wishlist';
$active_menu = 'wishlist';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<?php if (!$events): ?>

    <div class="table-card">
        <div class="empty-state">
            <h3>Belum Ada Event</h3>
            <p>Buat event terlebih dahulu sebelum mengelola wishlist</p>
        </div>
    </div>

<?php else: ?>

    <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
        <a href="<?= base_url('pages/wishlist/tambah.php?event_id=' . $event_id) ?>" class="btn-add">+ Tambah Wishlist</a>
    </div>

    <div class="table-card">
        <?php
        $search_value = $search;
        $search_label = 'Cari Wishlist';
        require __DIR__ . '/../../includes/event_selector.php';
        ?>

        <?php if (!$wishlist_list): ?>
            <div class="empty-state">
                <h3>Belum Ada Wishlist</h3>
                <p>Wishlist akan muncul setelah peserta mengisi, atau tambahkan manual di sini</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>Nama Peserta</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Estimasi</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wishlist_list as $i => $w): ?>
                        <tr>
                            <td><?= $i + 1 ?>.</td>
                            <td><?= h($w['peserta_nama']) ?></td>
                            <td><?= h($w['nama_barang']) ?></td>
                            <td><?= h($w['kategori']) ?></td>
                            <td>Rp<?= number_format((float) $w['estimasi_harga'], 0, ',', '.') ?></td>
                            <td><?= h(str_replace('_', ' ', ucfirst($w['status']))) ?></td>
                            <td class="action-buttons">
                                <a href="<?= base_url('pages/wishlist/edit.php?id=' . $w['id'] . '&event_id=' . $event_id) ?>" class="btn-edit" title="Edit">✏️</a>
                                <a
                                    href="<?= base_url('pages/wishlist/hapus.php?id=' . $w['id'] . '&event_id=' . $event_id) ?>"
                                    class="btn-delete"
                                    title="Hapus"
                                    onclick="return confirm('Yakin ingin menghapus wishlist ini?');"
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