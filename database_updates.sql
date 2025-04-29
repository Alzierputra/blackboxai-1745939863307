-- Menambah kolom untuk verifikasi email di tabel users
ALTER TABLE users 
ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
ADD COLUMN verification_code VARCHAR(6) NULL,
ADD COLUMN verification_expires DATETIME NULL,
ADD COLUMN email VARCHAR(255) NOT NULL AFTER telepon;

-- Menambah kolom untuk bukti pembayaran dan alasan penolakan di tabel booking
ALTER TABLE booking 
ADD COLUMN bukti_pembayaran VARCHAR(255) NULL,
ADD COLUMN alasan_penolakan TEXT NULL;

-- Membuat tabel untuk pengingat email
CREATE TABLE email_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    type ENUM('payment', 'schedule') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_time DATETIME NOT NULL,
    sent_time DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
);
