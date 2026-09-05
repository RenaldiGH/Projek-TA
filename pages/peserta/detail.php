<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);
$event_id = (int) ($_GET['event_id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT p.*, e.nama_event
     FROM peserta p
     JOIN events e ON e.id = p.event_id
     WHERE p.id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$peserta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$peserta) {
    header('Location: ' . base_url('pages/peserta/index.php?event_id=' . $event_id));
    exit;
}

$wishlist_stmt = $conn->prepare(
    "SELECT nama_barang, kategori, estimasi_harga, deskripsi, status
     FROM wishlist WHERE peserta_id = ? ORDER BY id ASC"
);
$wishlist_stmt->bind_param('i', $id);
$wishlist_stmt->execute();
$wishlist_result = $wishlist_stmt->get_result();

$page_title = 'Detail Peserta';
$page_subtitle = 'Informasi lengkap peserta';
$active_menu = 'peserta';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="table-card" style="min-height:auto;">
    <h3 style="margin-bottom:16px;">Data Diri</h3>
    <table>
        <tr><td style="width:160px; color:#666;">Nama</td><td>: <?= h($peserta['nama']) ?></td></tr>
        <tr><td style="color:#666;">Email</td><td>: <?= h($peserta['email']) ?></td></tr>
        <tr><td style="color:#666;">No. HP</td><td>: <?= h($peserta['no_hp']) ?></td></tr>
        <tr><td style="color:#666;">Status</td><td>: <?= h(ucfirst($peserta['status'])) ?></td></tr>
        <tr><td style="color:#666;">Event</td><td>: <?= h($peserta['nama_event']) ?></td></tr>
        <tr><td style="color:#666;">Terdaftar Sejak</td><td>: <?= h($peserta['created_at']) ?></td></tr>
    </table>

    <h3 style="margin:24px 0 16px;">Wishlist</h3>
    <?php if ($wishlist_result->num_rows === 0): ?>
        <p style="color:#666;">Peserta ini belum mengisi wishlist.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Estimasi Harga</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($w = $wishlist_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= h($w['nama_barang']) ?></td>
                        <td><?= h($w['kategori']) ?></td>
                        <td>Rp<?= number_format((float) $w['estimasi_harga'], 0, ',', '.') ?></td>
                        <td><?= h(str_replace('_', ' ', ucfirst($w['status']))) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="form-actions">
        <a href="<?= base_url('pages/peserta/index.php?event_id=' . $event_id) ?>" class="btn-secondary">Kembali</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>