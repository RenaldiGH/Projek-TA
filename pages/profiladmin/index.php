<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * ASUMSI (sesuaikan dengan project kamu):
 * - Koneksi database mysqli ada di variabel $conn (dari config.php)
 * - Tabel `users` punya kolom: id, nama, email, password, foto, created_at, last_login
 * - ID user yang login disimpan di $_SESSION['user_id']
 *
 * Jika kolom foto belum ada, jalankan:
 *   ALTER TABLE users ADD COLUMN foto VARCHAR(255) NULL;
 */

$userId = (int)($_SESSION['user_id'] ?? 0);

// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---------- Ambil data user ----------
// Cari nama kolom primary key (id / id_user / dll)
function profil_cari_pk(mysqli $conn): string
{
    $res = $conn->query("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
    $row = $res ? $res->fetch_assoc() : null;
    return $row['Column_name'] ?? 'id';
}

// Cek apakah sebuah kolom ada di tabel users
function profil_kolom_ada(mysqli $conn, string $kolom): bool
{
    $kolom = $conn->real_escape_string($kolom);
    $res = $conn->query("SHOW COLUMNS FROM users LIKE '$kolom'");
    return $res && $res->num_rows > 0;
}

$pk = profil_cari_pk($conn);

// Kolom foto dibutuhkan untuk fitur foto profil; tambahkan otomatis jika belum ada
if (!profil_kolom_ada($conn, 'foto')) {
    $conn->query("ALTER TABLE users ADD COLUMN foto VARCHAR(255) NULL");
}

function profil_ambil_user(mysqli $conn, string $pk, int $id): ?array
{
    // SELECT * agar tidak error bila kolom opsional (last_login, created_at) belum ada
    $stmt = $conn->prepare("SELECT * FROM users WHERE `$pk` = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

$user = profil_ambil_user($conn, $pk, $userId);
if (!$user) {
    header('Location: ' . base_url('logout.php'));
    exit;
}

$errors  = [];
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$uploadDir = __DIR__ . '/../../assets/uploads/profile/';
$maxSize   = 2 * 1024 * 1024; // 2 MB

// ---------- Hapus foto profil ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus_foto') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi tidak valid, silakan muat ulang halaman.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET foto = NULL WHERE `$pk` = ?");
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            if (!empty($user['foto'])) {
                $lama = $uploadDir . basename($user['foto']);
                if (is_file($lama)) {
                    @unlink($lama);
                }
            }
            $stmt->close();
            $_SESSION['flash_success'] = 'Foto profil berhasil dihapus.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        $stmt->close();
        $errors[] = 'Gagal menghapus foto profil.';
    }
}

// ---------- Proses form ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') !== 'hapus_foto') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi tidak valid, silakan muat ulang halaman.';
    }

    $nama          = trim($_POST['nama'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $passwordLama  = $_POST['password_lama'] ?? '';
    $passwordBaru  = $_POST['password_baru'] ?? '';
    $konfirmasi    = $_POST['konfirmasi_password'] ?? '';

    // Validasi nama & email
    if ($nama === '' || mb_strlen($nama) > 100) {
        $errors[] = 'Nama wajib diisi (maksimal 100 karakter).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $stmt = $conn->prepare("SELECT `$pk` FROM users WHERE email = ? AND `$pk` <> ? LIMIT 1");
        $stmt->bind_param('si', $email, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email sudah digunakan akun lain.';
        }
        $stmt->close();
    }

    // Validasi password (hanya jika salah satu kolom diisi)
    $ubahPassword = ($passwordLama !== '' || $passwordBaru !== '' || $konfirmasi !== '');
    $hashBaru = null;
    if ($ubahPassword) {
        if ($passwordLama === '') {
            $errors[] = 'Sandi lama wajib diisi untuk mengganti sandi.';
        } elseif (!password_verify($passwordLama, $user['password'])) {
            $errors[] = 'Sandi lama salah.';
        }
        if (strlen($passwordBaru) < 8) {
            $errors[] = 'Sandi baru minimal 8 karakter.';
        }
        if ($passwordBaru !== $konfirmasi) {
            $errors[] = 'Konfirmasi sandi baru tidak cocok.';
        }
        if ($passwordBaru !== '' && $passwordBaru === $passwordLama) {
            $errors[] = 'Sandi baru tidak boleh sama dengan sandi lama.';
        }
        if (!$errors) {
            $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }
    }

    // Validasi & simpan foto profil
    $fotoBaru = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['foto'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload foto gagal, silakan coba lagi.';
        } elseif ($f['size'] > $maxSize) {
            $errors[] = 'Ukuran foto maksimal 2 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($f['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($allowed[$mime]) || @getimagesize($f['tmp_name']) === false) {
                $errors[] = 'Format foto harus JPG, PNG, atau WEBP.';
            } elseif (!$errors) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fotoBaru = 'admin_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($f['tmp_name'], $uploadDir . $fotoBaru)) {
                    $errors[] = 'Gagal menyimpan foto.';
                    $fotoBaru = null;
                }
            }
        }
    }

    // Simpan ke database
    if (!$errors) {
        $set    = ['nama = ?', 'email = ?'];
        $types  = 'ss';
        $params = [$nama, $email];

        if ($hashBaru !== null) {
            $set[] = 'password = ?';
            $types .= 's';
            $params[] = $hashBaru;
        }
        if ($fotoBaru !== null) {
            $set[] = 'foto = ?';
            $types .= 's';
            $params[] = $fotoBaru;
        }

        $types .= 'i';
        $params[] = $userId;

        $sql  = 'UPDATE users SET ' . implode(', ', $set) . " WHERE `$pk` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Hapus foto lama
            if ($fotoBaru !== null && !empty($user['foto'])) {
                $lama = $uploadDir . basename($user['foto']);
                if (is_file($lama)) {
                    @unlink($lama);
                }
            }
            $_SESSION['nama'] = $nama;
            $_SESSION['flash_success'] = 'Profil berhasil diperbarui.';
            $stmt->close();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data.';
            if ($fotoBaru !== null) {
                @unlink($uploadDir . $fotoBaru);
            }
        }
        $stmt->close();
    }

    // Tampilkan kembali input yang sudah diketik
    $user['nama']  = $nama;
    $user['email'] = $email;
}

// ---------- Helper tampilan ----------
function profil_tanggal(?string $tgl, bool $denganJam = false): string
{
    if (!$tgl) {
        return '-';
    }
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tgl);
    $out = date('d', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
    return $denganJam ? $out . ' ' . date('H.i', $t) : $out;
}

$fotoUrl = (!empty($user['foto']) && is_file($uploadDir . basename($user['foto'])))
    ? base_url('assets/uploads/profile/' . rawurlencode($user['foto']))
    : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - SKARIGA Secret Santa</title>
    <link rel="stylesheet" href="../../assets/css/style_admin.css">
    <style>
        /* ===== Layout halaman profil admin (mengikuti desain) ===== */
        body { margin: 0; }
        body .dashboard-container { display: flex !important; min-height: 100vh; background: #0a0a3d; }

        /* Sidebar */
        body .dashboard-container aside {
            display: flex !important; flex-direction: column;
            width: 210px !important; min-width: 210px; flex-shrink: 0;
            height: 100vh; position: sticky; top: 0; overflow-y: auto;
            background: #0a0a3d; color: #fff;
        }
        body .dashboard-container aside .brand { padding: 22px 12px; text-align: center; background: none; }
        body .dashboard-container aside .brand h2 { margin: 0; font-size: 13px; line-height: 1.35; color: #fff; letter-spacing: .5px; }
        body .dashboard-container aside nav ul { list-style: none; margin: 0; padding: 0; }
        body .dashboard-container aside nav li a {
            display: block; padding: 13px 10px; text-align: center;
            color: #fff; text-decoration: none; font-size: 13px; background: transparent;
        }
        body .dashboard-container aside nav li a:hover { background: rgba(255,255,255,.1); }
        body .dashboard-container aside nav li.active a,
        body .dashboard-container aside nav li.active a:hover { background: #ff0000; }

        /* Konten utama */
        body .dashboard-container main {
            flex: 1; min-width: 0; box-sizing: border-box;
            background: #fff; border-radius: 24px 0 0 24px; padding: 24px 28px;
        }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .page-header h2 { margin: 0; font-size: 22px; font-weight: 700; color: #111; }
        .page-header p { margin: 4px 0 0; font-size: 13px; color: #444; }
        .btn-back {
            display: inline-block; padding: 8px 16px; border: 1px solid #d5d5d5; border-radius: 8px;
            background: #fff; color: #222; font-size: 13px; text-decoration: none;
        }
        .btn-back:hover { background: #f3f3f3; }

        /* Dua kartu */
        .profile-container { display: grid; grid-template-columns: 300px 1fr; gap: 16px; align-items: start; }
        .profile-card { background: #fff; border: 1px solid #e3e3e3; border-radius: 14px; padding: 24px 28px; box-sizing: border-box; }
        .profile-card:first-child { padding: 0; }

        /* Kartu kiri */
        .avatar-box { text-align: center; padding: 36px 20px 24px; }
        .avatar-circle {
            position: relative; width: 130px; height: 130px; margin: 0 auto 22px;
            border: 4px solid #000; border-radius: 50%; background: #fff; color: #000;
            box-sizing: border-box;
        }
        .avatar-circle svg { width: 100%; height: 100%; border-radius: 50%; display: block; }
        .avatar-circle img.avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block; }
        .avatar-badge {
            position: absolute; right: -10px; bottom: -4px; width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center; box-sizing: border-box;
            background: #fff; border: 2px solid #000; border-radius: 12px; font-size: 17px; cursor: pointer;
        }
        #fotoInput { display: none; }
        .avatar-box h3 { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #111; }
        .avatar-box > span { font-size: 12px; color: #222; }

        .form-hapus-foto { margin: 14px 0 0; }
        .btn-hapus-foto {
            padding: 6px 16px; border: 1px solid #d32f2f; border-radius: 8px; background: #fff;
            color: #d32f2f; font-family: inherit; font-size: 12px; cursor: pointer;
        }
        .btn-hapus-foto:hover { background: #d32f2f; color: #fff; }

        .info-list { border-top: 1px solid #e3e3e3; padding: 22px 24px 8px; }
        .info-item { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; font-size: 13px; line-height: 1.45; color: #111; }
        .info-item > span { font-size: 24px; }
        .info-item div span { font-size: 12px; }

        /* Kartu kanan (form) */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: #111; }
        .form-group label small { font-size: 12px; color: #555; }
        .form-group input.select-input {
            width: 100%; box-sizing: border-box; padding: 14px 16px;
            border: 1px solid #dcdcdc; border-radius: 12px; background: #fff;
            font-family: inherit; font-size: 13px; color: #222;
        }
        .form-group input.select-input[readonly] { background: #fafafa; color: #777; }
        .form-group input.select-input:focus { outline: none; border-color: #1b0a5c; }
        .form-actions { display: flex; justify-content: flex-end; margin-top: 24px; }
        .btn-add {
            background: #1b0a5c; color: #fff; border: none; border-radius: 10px;
            padding: 14px 40px; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-add:hover { background: #2b1480; }

        /* Pesan */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #e6f7ec; color: #1b6e3a; border: 1px solid #b7e4c7; }
        .alert-error { background: #fdecea; color: #a12a22; border: 1px solid #f5c2be; }
        .alert-error ul { margin: 0; padding-left: 18px; }

        @media (max-width: 900px) {
            .profile-container { grid-template-columns: 1fr; }
            body .dashboard-container aside { width: 160px !important; min-width: 160px; }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <aside>
            <div class="brand">
                <h2>SKARIGA SECRET SANTA</h2>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="<?= base_url('pages/dashboard/menuadmin.php') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('pages/event/event.php') ?>">Event</a></li>
                    <li><a href="<?= base_url('pages/peserta/index.php') ?>">Peserta</a></li>
                    <li><a href="<?= base_url('pages/wishlist/index.php') ?>">Wishlist</a></li>
                    <li><a href="<?= base_url('pages/timeline/index.php') ?>">Timeline</a></li>
                    <li><a href="<?= base_url('pages/pengundian/index.php') ?>">Pengundian</a></li>
                    <li><a href="<?= base_url('pages/pemberi/index.php') ?>">Pemberi</a></li>
                    <li><a href="<?= base_url('pages/penerima/index.php') ?>">Penerima</a></li>
                    <li><a href="<?= base_url('pages/laporan/index.php') ?>">Laporan</a></li>
                    <li><a href="<?= base_url('pages/riwayat/index.php') ?>">Riwayat</a></li>
                    <li><a href="<?= base_url('pages/user/index.php') ?>">User</a></li>
                    <li><a href="<?= base_url('pages/adminpengaturan/index.php') ?>">Pengaturan</a></li>
                    <li><a href="<?= base_url('logout.php') ?>">Keluar</a></li>
                </ul>
            </nav>
        </aside>

        <main>
            <header class="page-header">
                <div>
                    <h2>Profil Admin</h2>
                    <p>Kelola informasi akun admin.</p>
                </div>
                <a href="<?= base_url('pages/dashboard/menuadmin.php') ?>" class="btn-back">← Kembali</a>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= h($success) ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= h($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="profile-container">
                    <!-- Kartu kiri: foto & info -->
                    <div class="profile-card">
                        <div class="avatar-box">
                            <div class="avatar-circle">
                                <img id="avatarPreview" class="avatar-img" alt="Foto profil"
                                     src="<?= h($fotoUrl) ?>" style="<?= $fotoUrl ? '' : 'display:none;' ?>">
                                <svg id="avatarDefault" width="45" height="45" viewBox="0 0 24 24" fill="currentColor"
                                     style="<?= $fotoUrl ? 'display:none;' : '' ?>">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <label for="fotoInput" class="avatar-badge" title="Ganti foto profil">📷</label>
                            </div>
                            <input type="file" name="foto" id="fotoInput" form="formProfil" accept="image/jpeg,image/png,image/webp">
                            <h3><?= h($user['nama']) ?></h3>
                            <span>Administrator</span>
                            <?php if ($fotoUrl): ?>
                                <form action="" method="POST" class="form-hapus-foto"
                                      onsubmit="return confirm('Hapus foto profil?');">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="aksi" value="hapus_foto">
                                    <button type="submit" class="btn-hapus-foto">Hapus Foto</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="info-list">
                            <div class="info-item">
                                <span>📅</span>
                                <div>
                                    <strong>Bergabung Sejak</strong><br>
                                    <span><?= h(profil_tanggal($user['created_at'] ?? null)) ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <span>🕒</span>
                                <div>
                                    <strong>Akun Terakhir Aktif</strong><br>
                                    <span><?= h(profil_tanggal($user['last_login'] ?? null, true)) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu kanan: form -->
                    <div class="profile-card">
                        <form id="formProfil" action="" method="POST" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" id="nama" name="nama" value="<?= h($user['nama']) ?>"
                                   class="select-input" required maxlength="100">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= h($user['email']) ?>"
                                   class="select-input" required>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="Administrator" class="select-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="password_lama">Sandi Lama <small>(isi hanya jika ingin mengganti sandi)</small></label>
                            <input type="password" id="password_lama" name="password_lama"
                                       placeholder="Masukkan sandi lama" class="select-input" autocomplete="current-password">
                        </div>

                        <div class="form-group">
                            <label for="password_baru">Sandi Baru <small>(minimal 8 karakter)</small></label>
                            <input type="password" id="password_baru" name="password_baru"
                                       placeholder="Masukkan sandi baru" class="select-input" autocomplete="new-password">
                        </div>

                        <div class="form-group">
                            <label for="konfirmasi_password">Konfirmasi Sandi Baru</label>
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password"
                                       placeholder="Ulangi sandi baru" class="select-input" autocomplete="new-password">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-add">Simpan Perubahan</button>
                        </div>
                        </form>
                    </div>
                </div>
        </main>
    </div>

    <script>
        // Pilih & pratinjau foto profil
        const fotoInput = document.getElementById('fotoInput');
                const preview   = document.getElementById('avatarPreview');
        const defIcon   = document.getElementById('avatarDefault');

        fotoInput.addEventListener('change', () => {
            const file = fotoInput.files[0];
            if (!file) return;

            const okType = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
            if (!okType) {
                alert('Format foto harus JPG, PNG, atau WEBP.');
                fotoInput.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran foto maksimal 2 MB.');
                fotoInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                defIcon.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });

        // Validasi sisi klien: konfirmasi sandi harus cocok
        document.getElementById('formProfil').addEventListener('submit', e => {
            const lama = document.getElementById('password_lama').value;
            const baru = document.getElementById('password_baru').value;
            const konf = document.getElementById('konfirmasi_password').value;
            if (lama || baru || konf) {
                if (baru !== konf) {
                    e.preventDefault();
                    alert('Konfirmasi sandi baru tidak cocok.');
                }
            }
        });
    </script>

</body>
</html>