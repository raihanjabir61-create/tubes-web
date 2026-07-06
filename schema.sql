-- Database creation (optional, done in init_db.php)
CREATE DATABASE IF NOT EXISTS db_donasi_buku;
USE db_donasi_buku;

-- Table for user and admin accounts
CREATE TABLE IF NOT EXISTS tabel_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    no_telp VARCHAR(20) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for book donations
CREATE TABLE IF NOT EXISTS tabel_donasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    judul_buku VARCHAR(255) NOT NULL,
    penulis VARCHAR(150) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    kondisi ENUM('baru', 'bekas_layak') NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    foto LONGTEXT NOT NULL,
    status ENUM('pending', 'diterima', 'ditolak') NOT NULL DEFAULT 'pending',
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES tabel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
