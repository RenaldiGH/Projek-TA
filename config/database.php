<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'secret_santa';

$conn = new mysqli($host, $user, $pass, $dbname);

require_once __DIR__ . '/database.php';
?>
