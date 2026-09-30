<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_peserta();

$back = base_url('pages/dashboard/dashboarduser.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $back);
    exit;
}

$user_id  = (int) $_SESSION['user_id'];
$event_id = (int) ($_POST['event_id'] ?? 0);
$aksi     = $_POST['aksi'] ?? '';

// Ambil event
$stmt = $conn->prepare("SELECT id, nama_event, status FROM events WHERE id = ?");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event || $event['status'] === 'draft') {
    set_flash('error', 'Event tidak ditemukan.');
    header('Location: ' . $back);
    exit;
}

if ($event['status'] !== 'aktif') {
    set_flash('error', 'Event ini sudah selesai, tidak bisa diubah lagi.');
    header('Location: ' . $back);
    exit;
}

// Cek apakah user sudah terdaftar di event ini
$stmt = $conn->prepare("SELECT id FROM peserta WHERE event_id = ? AND user_id = ?");
$stmt->bind_param('ii', $event_id, $user_id);
$stmt->execute();
$peserta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($aksi === 'ikut') {

    if ($peserta) {
        set_flash('error', 'Kamu sudah terdaftar di event ini.');
        header('Location: ' . $back);
        exit;
    }

    // Cek kuota
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM peserta WHERE event_id = ? AND status = 'aktif'");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $jumlah = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    if ($jumlah >= get_max_peserta($conn)) {
        set_flash('error', 'Kuota event ini sudah penuh.');
        header('Location: ' . $back);
        exit;
    }

    // Data peserta diambil dari akun user
    $stmt = $conn->prepare("SELECT nama, email FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    try {
        $insert = $conn->prepare(
            "INSERT INTO peserta (event_id, user_id, nama, email, status)
             VALUES (?, ?, ?, ?, 'aktif')"
        );
        $insert->bind_param('iiss', $event_id, $user_id, $user['nama'], $user['email']);
        $insert->execute();
        $insert->close();

        set_flash('success', 'Berhasil ikut event "' . $event['nama_event'] . '".');
    } catch (mysqli_sql_exception $e) {
        set_flash('error', 'Gagal mendaftar ke event. Coba lagi.');
    }

} elseif ($aksi === 'batal') {

    if (!$peserta) {
        set_flash('error', 'Kamu belum terdaftar di event ini.');
        header('Location: ' . $back);
        exit;
    }

    // Kalau pengundian sudah dilakukan, tidak boleh keluar
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengundian WHERE event_id = ?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $sudah_diundi = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    $stmt->close();

    if ($sudah_diundi) {
        set_flash('error', 'Pengundian sudah dilakukan, kamu tidak bisa batal ikut.');
        header('Location: ' . $back);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM peserta WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $peserta['id'], $user_id);
    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Kamu batal ikut event "' . $event['nama_event'] . '".');

} else {
    set_flash('error', 'Aksi tidak dikenal.');
}

header('Location: ' . $back);
exit;