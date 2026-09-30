<?php
function h($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

function get_events(mysqli $conn): array
{
    $events = [];
    $result = $conn->query("SELECT id, nama_event, tanggal_event FROM events ORDER BY tanggal_event DESC, id DESC");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

    return $events;
}


function resolve_event_id(mysqli $conn, $requested): ?int
{
    if ($requested !== null && ctype_digit((string) $requested)) {
        return (int) $requested;
    }

    $events = get_events($conn);

    return $events ? (int) $events[0]['id'] : null;
}


function set_flash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

// Tampilkan & langsung hapus flash message yang ada.
function render_flash(): void
{
    if (!empty($_SESSION['flash_success'])) {
        echo '<div class="alert alert-success">' . h($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }

    if (!empty($_SESSION['flash_error'])) {
        echo '<div class="alert alert-error">' . h($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
}

function tanggal_id($date, bool $short = false): string
{
    if (!$date) {
        return '-';
    }

    $bulan = $short
        ? ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
        : ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $ts = strtotime($date);

    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function get_max_peserta(mysqli $conn): int
{
    $row = $conn->query("SELECT max_peserta FROM pengaturan LIMIT 1")->fetch_assoc();

    return $row ? (int) $row['max_peserta'] : 10;
}

function get_events_for_user(mysqli $conn, int $user_id): array
{
    $stmt = $conn->prepare(
        "SELECT e.id, e.nama_event, e.deskripsi, e.budget, e.tanggal_event, e.status,
                (SELECT COUNT(*) FROM peserta px WHERE px.event_id = e.id AND px.status = 'aktif') AS jumlah,
                (SELECT px.id FROM peserta px WHERE px.event_id = e.id AND px.user_id = ? LIMIT 1) AS peserta_id
         FROM events e
         WHERE e.status <> 'draft'
         ORDER BY (e.status = 'selesai') ASC, e.tanggal_event ASC, e.id DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

function get_user_events(mysqli $conn, int $user_id): array
{
    $stmt = $conn->prepare(
        "SELECT e.id, e.nama_event, e.deskripsi, e.budget, e.tanggal_event, e.status,
                p.id AS peserta_id, p.created_at AS tanggal_ikut
         FROM peserta p
         JOIN events e ON e.id = p.event_id
         WHERE p.user_id = ?
         ORDER BY (e.status = 'selesai') ASC, e.tanggal_event ASC, e.id DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

function event_palette(int $id): string
{
    $palettes = ['pal-merah', 'pal-biru', 'pal-hijau', 'pal-ungu', 'pal-emas'];

    return $palettes[$id % count($palettes)];
}
?>