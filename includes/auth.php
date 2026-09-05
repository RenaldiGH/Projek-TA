<?php
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}
function require_admin() {
    if (($session['role'] ?? '') !== 'admin') {
        header('Location: ' . base_url('pages/dashboard/dashboarduser.php'));
        exit;
    }
}
?>