<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);

if ($id) {
    $cek = $conn->prepare("SELECT COUNT(*) AS total FROM peserta WHERE event_id = ?");
    $cek->bind_param('i', $id);
    $cek->execute();
    $jumlah_peserta = (int) $cek->get_result()->fetch_assoc()['total'];
    $cek->close();

    if ($jumlah_peserta > 0) {
        set_flash('error', 'Event ini masih punya ' . $jumlah_peserta . ' peserta, tidak bisa dihapus.');
    } else {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            set_flash('success', 'Event berhasil dihapus.');
        } else {
            set_flash('error', 'Gagal menghapus event.');
        }

        $stmt->close();
    }
}

header('Location: ' . base_url('pages/event/event.php'));
exit;