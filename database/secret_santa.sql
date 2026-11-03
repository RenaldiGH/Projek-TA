-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Nov 2026 pada 06.23
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
  `budget` decimal(12,2) DEFAULT 0.00,
  `tanggal_event` date DEFAULT NULL,
  `status` enum('draft','aktif','selesai') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id`, `user_id`, `nama_event`, `deskripsi`, `budget`, `tanggal_event`, `status`, `created_at`) VALUES
(6, 9, 'tukar barang', 'abc', 10000.00, '2026-10-30', 'aktif', '2026-10-30 14:42:43');

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
(1, 'judi bola yogas', 3, 10);

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
  `status` enum('draft','terkirim') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengundian`
--

INSERT INTO `pengundian` (`id`, `event_id`, `pemberi_id`, `penerima_id`, `tanggal_undi`, `status`) VALUES
(14, 6, 15, 14, '2026-10-30 16:04:10', 'draft'),
(15, 6, 14, 13, '2026-10-30 16:04:10', 'draft'),
(16, 6, 13, 15, '2026-10-30 16:04:10', 'draft');

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
(13, 6, 13, 'rey', 'alditik08@gmail.com', NULL, 'aktif', '2026-10-30 15:56:48'),
(14, 6, 14, 'budi', '123@gmail.com', NULL, 'aktif', '2026-10-30 15:59:47'),
(15, 6, 15, 'rehan', '111@gmail.com', NULL, 'aktif', '2026-10-30 16:00:52');

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
(1, 13, 6, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 14:42:52'),
(2, 9, 6, 'timeline', 'tambah', 'Menambah jadwal \"part 1\"', '2026-10-30 14:43:59'),
(3, 13, 6, 'peserta', 'hapus', 'Menghapus keikutsertaan di event \"tukar barang\"', '2026-10-30 15:54:52'),
(4, 13, 6, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 15:56:48'),
(5, 13, 6, 'wishlist', 'tambah', 'Menambah wishlist \"roti\"', '2026-10-30 15:57:19'),
(6, 14, 6, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 15:59:47'),
(7, 15, 6, 'peserta', 'tambah', 'Mengikuti event \"tukar barang\"', '2026-10-30 16:00:52');

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
(4, 6, 'part 1', '2026-10-30', '21:43:00', 'abc', '2026-10-30 14:43:59');

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
(13, 'rey', 'alditik08@gmail.com', '$2y$10$oRKO/curxsMJpULjmvcNu.rAnWK/M9pe2M9h/.NrTL2am66lAAQG.', 'peserta', NULL, '2026-10-30 14:39:45'),
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
(6, 13, 'roti', 'food', 3001.00, '', '', 'belum_dipilih', '2026-10-30 15:57:19');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengundian`
--
ALTER TABLE `pengundian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `riwayat`
--
ALTER TABLE `riwayat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `timeline`
--
ALTER TABLE `timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
