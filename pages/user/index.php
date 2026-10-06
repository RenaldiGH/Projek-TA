<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$search = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';
$my_id = (int) $_SESSION['user_id'];

$sql = "SELECT u.id, u.nama, u.email, u.role, u.created_at,
               (SELECT COUNT(*) FROM peserta p WHERE p.user_id = u.id) AS jumlah_event
        FROM users u
        WHERE 1 = 1";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (u.nama LIKE ? OR u.email LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if (in_array($role, ['admin', 'peserta'], true)) {
    $sql .= " AND u.role = ?";
    $params[] = $role;
    $types .= 's';
}

$sql .= " ORDER BY u.role ASC, u.nama ASC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$user_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Data User';
$page_subtitle = 'Kelola Semua Akun Pengguna';
$active_menu = 'user';

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
    <a href="<?= base_url('pages/user/tambah.php') ?>" class="btn-add">+ Tambah User</a>
</div>

<div class="table-card">
    <form method="GET" class="filter-bar">
        <div class="filter-group">
            <label for="role">Role</label>
            <select name="role" id="role" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="peserta" <?= $role === 'peserta' ? 'selected' : '' ?>>Peserta</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="q">&nbsp;</label>
            <input type="text" name="q" id="q" placeholder="Cari nama atau email" value="<?= h($search) ?>">
        </div>
    </form>

    <?php if (!$user_list): ?>

        <div class="empty-state">
            <h3>User Tidak Ditemukan</h3>
            <p>Coba ganti kata kunci atau filter role.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">NO</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Event Diikuti</th>
                    <th>Bergabung</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_list as $i => $u): ?>
                    <?php $is_self = (int) $u['id'] === $my_id; ?>
                    <tr>
                        <td><?= $i + 1 ?>.</td>
                        <td>
                            <?= h($u['nama']) ?>
                            <?php if ($is_self): ?>
                                <small style="color:#666;">(kamu)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= h($u['email']) ?></td>
                        <td>
                            <span class="<?= $u['role'] === 'admin' ? 'badge-active' : '' ?>">
                                <?= h(ucfirst($u['role'])) ?>
                            </span>
                        </td>
                        <td><?= (int) $u['jumlah_event'] ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="action-buttons">
                            <a href="<?= base_url('pages/user/edit.php?id=' . (int) $u['id']) ?>" class="btn-edit" title="Edit">✏️</a>

                            <?php if ($u['role'] === 'peserta' && !$is_self): ?>
                                <form method="POST" action="<?= base_url('pages/user/hapus.php') ?>" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="btn-delete" title="Hapus"
                                            style="border:none; background:none; cursor:pointer;"
                                            onclick="return confirm('Yakin hapus user ini? Data keikutsertaan dan wishlist miliknya ikut terhapus.');">🗑️</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>