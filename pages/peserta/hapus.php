<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);
$event_id = (int) ($_GET['event_id'] ?? 0);

if ($id) {
    // ambil datanya dulu, karena setelah dihapus sudah tidak bisa dibaca
    $stmt = $conn->prepare("SELECT nama, event_id FROM peserta WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // hapus peserta wishlist nya jug ikut hilang
    $stmt = $conn->prepare("DELETE FROM peserta WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        if ($data) {
            catat_riwayat($conn, 'peserta', 'hapus', 'Menghapus peserta "' . $data['nama'] . '"', (int) $data['event_id']);
        }
        set_flash('success', 'Peserta berhasil dihapus.');
    } else {
        set_flash('error', 'Gagal menghapus peserta.');
    }

    $stmt->close();
}

header('Location: ' . base_url('pages/peserta/index.php?event_id=' . $event_id));
exit;