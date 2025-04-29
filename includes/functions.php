<?php
require_once __DIR__ . '/../config/whatsapp.php';

/**
 * Generate kode verifikasi
 * @return string
 */
function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Kirim kode verifikasi via WhatsApp
 * @param string $phone_number
 * @param string $nama
 * @param string $code
 * @return array
 */
function sendWhatsAppVerification($phone_number, $nama, $code) {
    $message = getVerificationMessageTemplate($nama, $code);
    return sendWhatsAppMessage($phone_number, $message);
}

/**
 * Verifikasi kode
 * @param string $user_id
 * @param string $code
 * @return bool
 */
function verifyCode($user_id, $code) {
    global $conn;
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $code = mysqli_real_escape_string($conn, $code);
    
    $query = "SELECT * FROM users 
              WHERE id = '$user_id' 
              AND verification_code = '$code' 
              AND verification_expires > NOW()";
    
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Update status verifikasi user
        mysqli_query($conn, "UPDATE users 
                           SET is_verified = 1, 
                               verification_code = NULL, 
                               verification_expires = NULL 
                           WHERE id = '$user_id'");
        return true;
    }
    
    return false;
}

/**
 * Buat pengingat pembayaran
 * @param int $booking_id
 * @return bool
 */
function createPaymentReminder($booking_id) {
    global $conn;
    
    $booking_id = mysqli_real_escape_string($conn, $booking_id);
    
    // Buat pengingat segera
    $query = "INSERT INTO reminders (booking_id, type, scheduled_time) 
              VALUES ('$booking_id', 'payment', NOW())";
    mysqli_query($conn, $query);
    
    // Buat pengingat H-1 batas pembayaran
    $query = "INSERT INTO reminders (booking_id, type, scheduled_time) 
              SELECT '$booking_id', 'payment', 
                     DATE_SUB(DATE_ADD(tanggal_booking, INTERVAL 1 DAY), INTERVAL 1 DAY)
              FROM booking 
              WHERE id = '$booking_id'";
    return mysqli_query($conn, $query);
}

/**
 * Buat pengingat jadwal main
 * @param int $booking_id
 * @return bool
 */
function createScheduleReminder($booking_id) {
    global $conn;
    
    $booking_id = mysqli_real_escape_string($conn, $booking_id);
    
    // Buat pengingat H-1 sebelum main
    $query = "INSERT INTO reminders (booking_id, type, scheduled_time) 
              SELECT '$booking_id', 'schedule', 
                     DATE_SUB(tanggal_main, INTERVAL 1 DAY)
              FROM booking 
              WHERE id = '$booking_id'";
    mysqli_query($conn, $query);
    
    // Buat pengingat 1 jam sebelum main
    $query = "INSERT INTO reminders (booking_id, type, scheduled_time) 
              SELECT '$booking_id', 'schedule', 
                     DATE_SUB(CONCAT(tanggal_main, ' ', jam_mulai), INTERVAL 1 HOUR)
              FROM booking 
              WHERE id = '$booking_id'";
    return mysqli_query($conn, $query);
}

/**
 * Upload bukti pembayaran
 * @param array $file File dari $_FILES
 * @return string|false
 */
function uploadBuktiPembayaran($file) {
    $target_dir = __DIR__ . "/../assets/images/bukti_pembayaran/";
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Cek ekstensi file
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    // Cek ukuran file (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    
    return false;
}
