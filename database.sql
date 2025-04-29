-- Membuat database
CREATE DATABASE IF NOT EXISTS db_futsal_sayan1;
USE db_futsal_sayan1;

-- Membuat tabel users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(15) NOT NULL,
    alamat TEXT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user'
);

-- Membuat tabel lapangan
CREATE TABLE lapangan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga_per_jam DECIMAL(10,2) NOT NULL,
    gambar VARCHAR(255),
    status ENUM('tersedia', 'maintenance') DEFAULT 'tersedia'
);

-- Membuat tabel booking
CREATE TABLE booking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    lapangan_id INT,
    tanggal_main DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    metode_pembayaran ENUM('cod', 'transfer', 'qris') NOT NULL,
    status_pembayaran ENUM('pending', 'dikonfirmasi', 'dibatalkan') DEFAULT 'pending',
    tanggal_booking TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (lapangan_id) REFERENCES lapangan(id)
);

-- Insert data admin default
INSERT INTO users (nama, telepon, alamat, username, password, role) 
VALUES ('Admin Sayan', '081234567890', 'Bekasi', 'admin', 'admin123', 'admin');

-- Insert data lapangan
INSERT INTO lapangan (nama, deskripsi, harga_per_jam, gambar) VALUES
('Lapangan 1', 'Lapangan rumput sintetis ukuran standar dengan penerangan yang baik', 100000, 'lapangan1.jpg'),
('Lapangan 2', 'Lapangan vinyl berkualitas tinggi dengan atap tertutup', 120000, 'lapangan2.jpg'),
('Lapangan 3', 'Lapangan premium dengan rumput sintetis grade A dan tribun penonton', 150000, 'lapangan3.jpg');
