<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$user_id = (int) $_SESSION['user_id'];
$error   = '';

// Data user dari database
$stmt = $conn->prepare("SELECT nama, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: ' . base_url('logout.php'));
    exit;
}

$nama  = $user['nama'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nama === '' || $email === '') {
        $error = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
       
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        $dipakai = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($dipakai) {
            $error = 'Email sudah dipakai akun lain.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $stmt->bind_param('ssi', $nama, $email, $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE peserta SET nama = ?, email = ? WHERE user_id = ?");
            $stmt->bind_param('ssi', $nama, $email, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['nama']  = $nama;
            $_SESSION['email'] = $email;

            set_flash('success', 'Profil berhasil diperbarui!');
            header('Location: ' . base_url('pages/profil/profiluser.php'));
            exit;
        }
    }
}

$event_diikuti = get_user_events($conn, $user_id);

$page_title    = 'Profil Saya';
$page_subtitle = 'Kelola informasi akun dan lihat event yang kamu ikuti.';
$active_menu   = 'profil';

require_once __DIR__ . '/../../includes/user_layout_top.php';
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="profile-grid">

    <div class="profile-card">
        <div class="profile-avatar"><?= h(strtoupper(mb_substr($user['nama'], 0, 1))) ?></div>
        <h2><?= h($user['nama']) ?></h2>
        <p><?= h($user['email']) ?></p>
        <span class="tag ok">Akun Aktif</span>
    </div>

    <div class="panel">
        <h2>Informasi Profil</h2>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= h($nama) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= h($email) ?>" required>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" id="role" value="<?= h(ucfirst($user['role'])) ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="bergabung">Tanggal Bergabung</label>
                    <input type="text" id="bergabung" value="<?= h(tanggal_id($user['created_at'])) ?>" readonly>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<section class="panel">
    <h2>Event yang Saya Ikuti (<?= count($event_diikuti) ?>)</h2>

    <?php if (!$event_diikuti): ?>

        <div class="empty">
            <h3>Belum Mengikuti Event</h3>
            <p>Pilih event yang ingin kamu ikuti di dashboard.</p>
            <a href="<?= base_url('pages/dashboard/dashboarduser.php') ?>" class="btn btn-primary">Pilih Event</a>
        </div>

    <?php else: ?>

        <ul class="joined-list">
            <?php foreach ($event_diikuti as $e): ?>
                <li class="joined-item">
                    <div>
                        <h3><?= h($e['nama_event']) ?></h3>
                        <small>
                            <?= h(tanggal_id($e['tanggal_event'])) ?>
                            &middot; Budget <?= h(rupiah($e['budget'])) ?>
                            &middot; Bergabung <?= h(tanggal_id($e['tanggal_ikut'], true)) ?>
                        </small>
                    </div>

                    <div class="joined-actions">
                        <span class="tag <?= $e['status'] === 'aktif' ? 'ok' : 'off' ?>">
                            <?= h(ucfirst($e['status'])) ?>
                        </span>
                        <a href="<?= base_url('pages/wishlist/saya.php?event_id=' . (int) $e['id']) ?>" class="btn btn-secondary btn-small">Wishlist</a>
                        <a href="<?= base_url('pages/timeline/saya.php?event_id=' . (int) $e['id']) ?>" class="btn btn-secondary btn-small">Timeline</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../../includes/user_layout_bottom.php'; ?>