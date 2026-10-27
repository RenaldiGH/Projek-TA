<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin ();

$events = get_events($conn);
$event_id = (int) ($_GET['event_id'] ?? 0);
$modul = $_GET['modul'] ?? '';
$aksi = $_GET['aksi'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 20;

$from = " FROM riwayat r
          LEFT JOIN users u ON u.id = r.user_id
          LEFT JOIN events e ON e.id = r.event_id";
$where = " WHERE 1 = 1";
$params = [];
$types = '';

if ($event_id) {
    $where .= " AND r.event_id = ?";
    $params[] = $event_id;
    $types .= 'i';
}

if (in_array($modul, ['peserta', 'wishlist', 'timeline'], true)) {
    $where .= " AND r.modul = ?";
    $params[] = $modul;
    $types .= 's';
}

if (in_array($aksi, ['tambah', 'ubah', 'hapus'], true)) {
    $where .= " AND r.aksi = ?";
    $params[] = $aksi;
    $types .= 's';
}

if ($search !== '') {
    $where .= " AND (r.keterangan LIKE ? OR u.nama LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

// Hitung total untuk pagination
$stmt = $conn->prepare("SELECT COUNT(*) AS total" . $from . $where);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = (int) $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_page = max(1, (int) ceil($total / $per_page));
$page = min($page, $total_page);
$offset = ($page - 1) * $per_page;

// Ambil data satu halaman
$stmt = $conn->prepare(
    "SELECT r.modul, r.aksi, r.keterangan, r.created_at,
            u.nama AS pelaku, u.role AS role_pelaku, e.nama_event"
    . $from . $where .
    " ORDER BY r.created_at DESC, r.id DESC LIMIT ? OFFSET ?"
);
$params_page = array_merge($params, [$per_page, $offset]);
$stmt->bind_param($types . 'ii', ...$params_page);
$stmt->execute();
$riwayat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$warna_aksi = ['tambah' => '#146c2e', 'ubah' => '#1d4ed8', 'hapus' => '#b91c1c'];

function link_halaman(int $p): string
{
    return '?' . http_build_query(array_merge($_GET, ['page' => $p]));
}

$page_title = 'Riwayat Aktivitas';
$page_subtitle = 'Catatan perubahan data peserta, wishlist, dan timeline';
$active_menu = 'riwayat';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="table-card">
    <form method="GET" class="filter-bar">
        <div class="filter-group">
            <label for="event_id">Event</label>
            <select name="event_id" id="event_id" onchange="this.form.submit()">
                <option value="0">Semua event</option>
                <?php foreach ($events as $ev): ?>
                    <option value="<?= (int) $ev['id'] ?>" <?= $event_id === (int) $ev['id'] ? 'selected' : '' ?>>
                        <?= h($ev['nama_event']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="modul">Data</label>
            <select name="modul" id="modul" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="peserta" <?= $modul === 'peserta' ? 'selected' : '' ?>>Peserta</option>
                <option value="wishlist" <?= $modul === 'wishlist' ? 'selected' : '' ?>>Wishlist</option>
                <option value="timeline" <?= $modul === 'timeline' ? 'selected' : '' ?>>Timeline</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="aksi">Aksi</label>
            <select name="aksi" id="aksi" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="tambah" <?= $aksi === 'tambah' ? 'selected' : '' ?>>Tambah</option>
                <option value="ubah" <?= $aksi === 'ubah' ? 'selected' : '' ?>>Ubah</option>
                <option value="hapus" <?= $aksi === 'hapus' ? 'selected' : '' ?>>Hapus</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="q">&nbsp;</label>
            <input type="text" name="q" id="q" placeholder="Cari keterangan atau pelaku" value="<?= h($search) ?>">
        </div>
    </form>

    <?php if (!$riwayat): ?>

        <div class="empty-state">
            <h3>Belum Ada Riwayat</h3>
            <p>Riwayat muncul setelah ada data peserta, wishlist, atau timeline yang ditambah, diubah, atau dihapus.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pelaku</th>
                    <th>Event</th>
                    <th>Data</th>
                    <th>Aksi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?= h($r['pelaku'] ?? 'Akun dihapus') ?>
                            <?php if (!empty($r['role_pelaku'])): ?>
                                <br><small style="color:#666;"><?= h(ucfirst($r['role_pelaku'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= h($r['nama_event'] ?? '-') ?></td>
                        <td><?= h(ucfirst($r['modul'])) ?></td>
                        <td>
                            <strong style="color:<?= $warna_aksi[$r['aksi']] ?? '#333' ?>;">
                                <?= h(ucfirst($r['aksi'])) ?>
                            </strong>
                        </td>
                        <td><?= h($r['keterangan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:18px; font-size:14px;">
            <span>Total <?= $total ?> catatan &middot; halaman <?= $page ?> dari <?= $total_page ?></span>

            <span style="display:flex; gap:8px;">
                <?php if ($page > 1): ?>
                    <a href="<?= h(link_halaman($page - 1)) ?>" class="btn-secondary">&laquo; Sebelumnya</a>
                <?php endif; ?>
                <?php if ($page < $total_page): ?>
                    <a href="<?= h(link_halaman($page + 1)) ?>" class="btn-secondary">Berikutnya &raquo;</a>
                <?php endif; ?>
            </span>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>

