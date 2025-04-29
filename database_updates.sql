-- Menambah kolom untuk verifikasi user
ALTER TABLE users 
ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
ADD COLUMN verification_code VARCHAR(6) NULL,
ADD COLUMN verification_expires DATETIME NULL;

-- Membuat tabel untuk pengingat
CREATE TABLE reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    type ENUM('payment', 'schedule') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_time DATETIME NOT NULL,
    sent_time DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
);

-- Menambah kolom untuk bukti pembayaran dan alasan penolakan di tabel booking
ALTER TABLE booking 
ADD COLUMN bukti_pembayaran VARCHAR(255) NULL,
ADD COLUMN alasan_penolakan TEXT NULL;
