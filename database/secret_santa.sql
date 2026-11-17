-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Nov 2026 pada 23.04
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `secret_santa`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_event` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tema` varchar(150) DEFAULT NULL,
  `aturan_kado` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT 0.00,
  `biaya_pendaftaran` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_event` date DEFAULT NULL,
  `status` enum('draft','aktif','selesai') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id`, `user_id`, `nama_event`, `deskripsi`, `tema`, `aturan_kado`, `gambar`, `budget`, `biaya_pendaftaran`, `tanggal_event`, `status`, `created_at`) VALUES
(15, 9, 'tes', '<ul><li>awad</li></ul><div><ul style=\"list-style-type: square\"><li>aawdaw</li></ul></div><div><ul style=\"list-style-type: circle\"><li>awdaw</li><li>awdawd</li></ul></div>', 'awdad', 'awdawd', 'event_c380a8950462e1d5.jpg', 5000.00, 0.00, '2026-11-17', 'aktif', '2026-11-16 19:47:11'),
(16, 9, '123', 'aaw', 'awd', 'wad', NULL, 6000.00, 0.00, '2026-11-17', 'aktif', '2026-11-16 21:35:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `pesan` text NOT NULL,
  `is_baca` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_aplikasi` varchar(150) DEFAULT 'SKARIGA Secret Santa',
  `min_peserta` int(11) DEFAULT 3,
  `max_peserta` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama_aplikasi`, `min_peserta`, `max_peserta`) VALUES
(1, 'secret santa', 10, 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengundian`
--

CREATE TABLE `pengundian` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `pemberi_id` int(11) NOT NULL,
  `penerima_id` int(11) NOT NULL,
  `tanggal_undi` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','terkirim') DEFAULT 'draft',
  `status_kado` enum('belum_dibeli','sudah_dibeli','sudah_dibungkus','diserahkan') NOT NULL DEFAULT 'belum_dibeli',
  `cek_tema` tinyint(1) NOT NULL DEFAULT 0,
  `cek_budget` tinyint(1) NOT NULL DEFAULT 0,
  `cek_bungkus` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengundian`
--

INSERT INTO `pengundian` (`id`, `event_id`, `pemberi_id`, `penerima_id`, `tanggal_undi`, `status`, `status_kado`, `cek_tema`, `cek_budget`, `cek_bungkus`) VALUES
(29, 15, 28, 27, '2026-11-16 20:26:54', 'draft', 'belum_dibeli', 0, 0, 0),
(30, 15, 27, 28, '2026-11-16 20:26:54', 'draft', 'belum_dibeli', 0, 0, 0),
(31, 16, 30, 29, '2026-11-16 21:36:39', 'draft', 'belum_dibeli', 0, 0, 0),
(32, 16, 29, 30, '2026-11-16 21:36:39', 'draft', 'sudah_dibeli', 1, 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `peserta`
--

CREATE TABLE `peserta` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peserta`
--

INSERT INTO `peserta` (`id`, `event_id`, `user_id`, `nama`, `email`, `no_hp`, `status`, `created_at`) VALUES
(27, 15, 14, 'budi', '123@gmail.com', NULL, 'aktif', '2026-11-16 19:56:10'),
(28, 15, 15, 'rehan', '111@gmail.com', NULL, 'aktif', '2026-11-16 20:11:47'),
(29, 16, 14, 'budi', '123@gmail.com', NULL, 'aktif', '2026-11-16 21:36:03'),
(30, 16, 15, 'rehan', '111@gmail.com', NULL, 'aktif', '2026-11-16 21:36:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `pesan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `nama`, `rating`, `pesan`, `created_at`) VALUES
(1, 'rey', 4, 'ok', '2026-11-15 11:37:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat`
--

CREATE TABLE `riwayat` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `modul` enum('peserta','wishlist','timeline') NOT NULL,
  `aksi` enum('tambah','ubah','hapus') NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat`
--

INSERT INTO `riwayat` (`id`, `user_id`, `event_id`, `modul`, `aksi`, `keterangan`, `created_at`) VALUES
(1, 13, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 14:42:52'),
(2, 9, NULL, 'timeline', 'tambah', 'Menambah jadwal \"part 1\"', '2026-10-30 14:43:59'),
(3, 13, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"tukar barang\"', '2026-10-30 15:54:52'),
(4, 13, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 15:56:48'),
(5, 13, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"roti\"', '2026-10-30 15:57:19'),
(6, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 15:59:47'),
(7, 15, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 16:00:52'),
(8, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"budi\"', '2026-11-03 07:26:44'),
(9, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"rehan\"', '2026-11-03 07:26:46'),
(10, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"rey\"', '2026-11-03 07:26:47'),
(11, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-11-03 07:27:12'),
(12, 14, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"tukar barang\"', '2026-11-03 07:27:14'),
(13, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-11-03 07:27:16'),
(14, 14, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"tukar barang\"', '2026-11-03 07:27:18'),
(15, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-11-03 07:27:20'),
(16, 14, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"tukar barang\"', '2026-11-03 07:27:24'),
(17, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-11-03 07:37:37'),
(18, 14, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"roti\"', '2026-11-03 07:38:00'),
(19, 13, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-11-06 00:32:50'),
(20, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"123\"', '2026-11-06 01:21:04'),
(21, 14, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"babi gurih\"', '2026-11-06 01:21:30'),
(22, 13, NULL, 'peserta', 'tambah', 'Mengikuti event \"123\"', '2026-11-06 01:22:21'),
(23, 13, NULL, 'peserta', 'tambah', 'Mengikuti event \"testing\"', '2026-11-06 01:23:46'),
(24, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"testing\"', '2026-11-06 01:23:52'),
(25, 14, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"babi gurih 123\"', '2026-11-06 01:24:29'),
(26, 14, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"testing\"', '2026-11-06 01:25:41'),
(27, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"budi\"', '2026-11-06 01:26:19'),
(28, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"rey\"', '2026-11-06 01:26:21'),
(29, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"budi\"', '2026-11-06 01:26:41'),
(30, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"rey\"', '2026-11-06 01:26:43'),
(31, 9, NULL, 'peserta', 'hapus', 'Menghapus peserta \"rey\"', '2026-11-06 01:26:48'),
(32, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"p\"', '2026-11-06 03:09:25'),
(33, 14, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"roti\"', '2026-11-06 03:09:50'),
(34, 14, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"p\"', '2026-11-11 07:38:27'),
(35, 14, NULL, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-11-11 22:44:33'),
(36, 14, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"roti\"', '2026-11-11 22:45:03'),
(37, 9, NULL, 'timeline', 'tambah', 'Menambah jadwal \"tes\"', '2026-11-11 22:45:50'),
(38, 9, NULL, 'wishlist', 'tambah', 'Menambah wishlist \"wad\"', '2026-11-16 19:28:57'),
(39, 14, NULL, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"tukar barang\"', '2026-11-16 19:43:35'),
(40, 14, 15, 'peserta', 'tambah', 'Mengikuti event \"tes\"', '2026-11-16 19:56:10'),
(41, 15, 15, 'peserta', 'tambah', 'Mengikuti event \"tes\"', '2026-11-16 20:11:47'),
(42, 15, 15, 'wishlist', 'tambah', 'Menambah wishlist \"abc\"', '2026-11-16 20:12:20'),
(43, 9, 15, 'timeline', 'tambah', 'Menambah jadwal \"awdawd\"', '2026-11-16 20:26:38'),
(44, 14, 16, 'peserta', 'tambah', 'Mengikuti event \"123\"', '2026-11-16 21:36:03'),
(45, 15, 16, 'peserta', 'tambah', 'Mengikuti event \"123\"', '2026-11-16 21:36:10'),
(46, 15, 15, '', 'ubah', 'Status kado: Belum dibeli', '2026-11-16 21:36:16'),
(47, 14, 16, '', 'ubah', 'Status kado: Sudah dibeli', '2026-11-16 21:47:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `timeline`
--

CREATE TABLE `timeline` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `timeline`
--

INSERT INTO `timeline` (`id`, `event_id`, `judul`, `tanggal`, `waktu`, `deskripsi`, `created_at`) VALUES
(6, 15, 'awdawd', '2026-11-17', '03:26:00', 'awda<b>sefsef</b><div><ul><li><b>wad</b></li></ul></div>', '2026-11-16 20:26:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','peserta') NOT NULL DEFAULT 'peserta',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `foto`, `created_at`) VALUES
(9, 'admin', '1234@gmail.com', '$2y$10$EAZZ/0Ps7ZRivz.A/3fMgeNCpPJ98wRV01s3sOq.Kx3yucc/LMC8i', 'admin', NULL, '2026-09-05 02:19:31'),
(13, 'rey', 'alditik08@gmail.com', '$2y$10$yDnG626HkDnHZKaWxpaV5OUZcXnW5IzrEo4g2kCt.THVjvBqU81ZW', 'peserta', NULL, '2026-10-30 14:39:45'),
(14, 'budi', '123@gmail.com', '$2y$10$1Fcj69MXbFhOBkB/PrONhu6AkaUCK4LFEWCZq.qV8C2xHZ9voov9e', 'peserta', NULL, '2026-10-30 15:59:35'),
(15, 'rehan', '111@gmail.com', '$2y$10$DjOx7lkc2u5LCxq9WLN9hu5xPDwJCQDYwXZswiAaK7m7ka4fa3HSG', 'peserta', NULL, '2026-10-30 16:00:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `estimasi_harga` decimal(12,2) DEFAULT 0.00,
  `deskripsi` text DEFAULT NULL,
  `link_referensi` varchar(255) DEFAULT NULL,
  `status` enum('belum_dipilih','dipilih') DEFAULT 'belum_dipilih',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `wishlist`
--

INSERT INTO `wishlist` (`id`, `peserta_id`, `nama_barang`, `kategori`, `estimasi_harga`, `deskripsi`, `link_referensi`, `status`, `created_at`) VALUES
(13, 28, 'abc', 'awdaw', 50000.00, 'awdaw<div><ul style=\"list-style-type: disc\"><li> efs</li></ul></div><div>awda</div>', '', 'belum_dipilih', '2026-11-16 20:12:20');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_hash` (`token_hash`),
  ADD KEY `fk_reset_user` (`user_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengundian`
--
ALTER TABLE `pengundian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `pemberi_id` (`pemberi_id`),
  ADD KEY `penerima_id` (`penerima_id`);

--
-- Indeks untuk tabel `peserta`
--
ALTER TABLE `peserta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_event_user` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `timeline`
--
ALTER TABLE `timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_id` (`peserta_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengundian`
--
ALTER TABLE `pengundian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT untuk tabel `timeline`
--
ALTER TABLE `timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengundian`
--
ALTER TABLE `pengundian`
  ADD CONSTRAINT `pengundian_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengundian_ibfk_2` FOREIGN KEY (`pemberi_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengundian_ibfk_3` FOREIGN KEY (`penerima_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peserta`
--
ALTER TABLE `peserta`
  ADD CONSTRAINT `peserta_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peserta_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  ADD CONSTRAINT `riwayat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `timeline`
--
ALTER TABLE `timeline`
  ADD CONSTRAINT `timeline_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`peserta_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
