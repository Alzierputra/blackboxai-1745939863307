<?php
require_once __DIR__ . '/../config/email.php';

/**
 * Generate kode verifikasi
 * @return string
 */
function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
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
 * Buat pengingat pembayaran via email
 * @param int $booking_id
 * @return bool
 */
function createPaymentReminder($booking_id) {
    global $conn;
    
    $booking_id = mysqli_real_escape_string($conn, $booking_id);
    
    // Buat pengingat segera
    $query = "INSERT INTO email_notifications (booking_id, type, scheduled_time) 
              VALUES ('$booking_id', 'payment', NOW())";
    mysqli_query($conn, $query);
    
    // Buat pengingat H-1 batas pembayaran
    $query = "INSERT INTO email_notifications (booking_id, type, scheduled_time) 
              SELECT '$booking_id', 'payment', 
                     DATE_SUB(DATE_ADD(tanggal_booking, INTERVAL 1 DAY), INTERVAL 1 DAY)
              FROM booking 
              WHERE id = '$booking_id'";
    return mysqli_query($conn, $query);
}

/**
 * Buat pengingat jadwal main via email
 * @param int $booking_id
 * @return bool
 */
function createScheduleReminder($booking_id) {
    global $conn;
    
    $booking_id = mysqli_real_escape_string($conn, $booking_id);
    
    // Buat pengingat H-1 sebelum main
    $query = "INSERT INTO email_notifications (booking_id, type, scheduled_time) 
              SELECT '$booking_id', 'schedule', 
                     DATE_SUB(tanggal_main, INTERVAL 1 DAY)
              FROM booking 
              WHERE id = '$booking_id'";
    mysqli_query($conn, $query);
    
    // Buat pengingat 1 jam sebelum main
    $query = "INSERT INTO email_notifications (booking_id, type, scheduled_time) 
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

/**
 * Kirim email konfirmasi booking
 * @param int $booking_id
 * @return bool
 */
function sendBookingConfirmationEmail($booking_id) {
    global $conn;
    
    $query = "SELECT b.*, u.nama, u.email, l.nama as nama_lapangan 
              FROM booking b
              JOIN users u ON b.user_id = u.id
              JOIN lapangan l ON b.lapangan_id = l.id
              WHERE b.id = '$booking_id'";
              
    $result = mysqli_query($conn, $query);
    $booking = mysqli_fetch_assoc($result);
    
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi Server
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Pengirim dan Penerima
        $mail->setFrom('your-email@gmail.com', 'Futsal Sayan');
        $mail->addAddress($booking['email'], $booking['nama']);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = 'Konfirmasi Booking Lapangan - Futsal Sayan';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #2C5F2D;">Konfirmasi Booking</h2>
            <p>Halo ' . htmlspecialchars($booking['nama']) . ',</p>
            <p>Terima kasih telah melakukan booking di Futsal Sayan. Berikut detail booking Anda:</p>
            <div style="background-color: #f4f4f4; padding: 15px; margin: 20px 0;">
                <p><strong>Detail Booking:</strong></p>
                <ul>
                    <li>Lapangan: ' . htmlspecialchars($booking['nama_lapangan']) . '</li>
                    <li>Tanggal: ' . date('d/m/Y', strtotime($booking['tanggal_main'])) . '</li>
                    <li>Jam: ' . date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                               date('H:i', strtotime($booking['jam_selesai'])) . '</li>
                    <li>Total: Rp ' . number_format($booking['total_harga'], 0, ',', '.') . '</li>
                    <li>Metode Pembayaran: ' . ucfirst($booking['metode_pembayaran']) . '</li>
                </ul>
            </div>
            <p>Mohon segera lakukan pembayaran untuk mengkonfirmasi booking Anda.</p>
            <hr style="border: 1px solid #eee; margin: 20px 0;">
            <p style="color: #666; font-size: 12px;">Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>';

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
