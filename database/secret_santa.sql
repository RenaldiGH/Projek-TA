
CREATE DATABASE IF NOT EXISTS secret_santa;
USE secret_santa;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'peserta') NOT NULL DEFAULT 'peserta',
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_event VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    budget DECIMAL(12,2) DEFAULT 0,
    tanggal_event DATE,
    status ENUM('draft', 'aktif', 'selesai') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS peserta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peserta_id INT NOT NULL,
    nama_barang VARCHAR(150) NOT NULL,
    kategori VARCHAR(100),
    estimasi_harga DECIMAL(12,2) DEFAULT 0,
    deskripsi TEXT,
    link_referensi VARCHAR(255),
    status ENUM('belum_dipilih', 'dipilih') DEFAULT 'belum_dipilih',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peserta_id) REFERENCES peserta(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS timeline (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    tanggal DATE,
    waktu TIME,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pengundian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    pemberi_id INT NOT NULL,
    penerima_id INT NOT NULL,
    tanggal_undi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('draft', 'terkirim') DEFAULT 'draft',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (pemberi_id) REFERENCES peserta(id) ON DELETE CASCADE,
    FOREIGN KEY (penerima_id) REFERENCES peserta(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT DEFAULT NULL,
    pesan TEXT NOT NULL,
    is_baca TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_aplikasi VARCHAR(150) DEFAULT 'SKARIGA Secret Santa',
    min_peserta INT DEFAULT 3,
    max_peserta INT DEFAULT 10
);

INSERT INTO pengaturan (nama_aplikasi, min_peserta, max_peserta)
VALUES ('SKARIGA Secret Santa', 3, 10);