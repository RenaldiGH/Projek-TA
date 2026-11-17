<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

// Hapus hanya lewat POST (tidak boleh lewat link biasa)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Permintaan hapus tidak valid.');
    header('Location: ' . base_url('pages/timeline/index.php'));
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$event_id = (int) ($_POST['event_id'] ?? 0);

if ($id) {
        // ambil datanya dulu, karena setelah dihapus sudah tidak bisa dibaca
    $stmt = $conn->prepare("SELECT judul, event_id FROM timeline WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM timeline WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        if ($data) {
            catat_riwayat($conn, 'timeline', 'hapus', 'Menghapus timeline "' . $data['judul'] . '"', (int) $data['event_id']);
        }
        set_flash('success', 'Timeline berhasil dihapus.');
    } else {
        set_flash('error', 'Gagal menghapus timeline.');
    }

    $stmt->close();
}

header('Location: ' . base_url('pages/timeline/index.php?event_id=' . $event_id));
exit;