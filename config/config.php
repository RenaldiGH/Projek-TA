<?php
$koneksi = mysqli_connect("localhost", "root", "", "secret_santa");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}