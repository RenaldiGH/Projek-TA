<?php
session_start();

$base_url = 'http://localhost:801/Project-TA-rey-nesya-rendra/';

function base_url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}
?>
