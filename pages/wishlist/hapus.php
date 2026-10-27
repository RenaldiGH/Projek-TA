<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);
$event_id = (int) ($_GET['event_id'] ?? 0);

if ($id) {
    // ambil datanya dulu, karena setelah dihapus sudah tidak bisa dibaca
    $stmt = $conn->prepare(
        "SELECT w.nama_barang, p.event_id
         FROM wishlist w
         JOIN peserta p ON p.id = w.peserta_id
         WHERE w.id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        if ($data) {
            catat_riwayat($conn, 'wishlist', 'hapus', 'Menghapus wishlist "' . $data['nama_barang'] . '"', (int) $data['event_id']);
        }
        set_flash('success', 'Wishlist berhasil dihapus.');
    } else {
        set_flash('error', 'Gagal menghapus wishlist.');
    }

    $stmt->close();
}

header('Location: ' . base_url('pages/wishlist/index.php?event_id=' . $event_id));
exit;