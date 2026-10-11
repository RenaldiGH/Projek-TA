<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$back = base_url('pages/user/index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $back);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$my_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, nama, role FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {

    set_flash('error', 'User tidak ditemukan.');

} elseif ($id === $my_id) {

    set_flash('error', 'Kamu tidak bisa menghapus akunmu sendiri.');

} elseif ($user['role'] === 'admin') {

    set_flash('error', 'Akun admin tidak bisa dihapus dari halaman ini.');

} else {

    // kalau event yang diikuti sudah diundi, user tidak boleh dihapus
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM pengundian pg
         JOIN peserta p ON p.event_id = pg.event_id
         WHERE p.user_id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $sudah_diundi = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    $stmt->close();

    if ($sudah_diundi) {

        set_flash('error', 'User ini ikut event yang sudah diundi, tidak bisa dihapus.');

    } else {

        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            set_flash('success', 'User "' . $user['nama'] . '" berhasil dihapus.');
        } catch (mysqli_sql_exception $e) {
            set_flash('error', 'Gagal menghapus user.');
        }
    }
}

header('Location: ' . $back);
exit;