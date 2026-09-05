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
?>