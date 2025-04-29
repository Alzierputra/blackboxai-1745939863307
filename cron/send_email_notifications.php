<?php
require_once '../config/database.php';
require_once '../config/email.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Kirim email pengingat pembayaran
 */
function sendPaymentReminders() {
    global $conn;
    
    // Ambil semua booking yang pending dan belum lewat batas waktu
    $query = "SELECT b.*, u.nama, u.email, l.nama as nama_lapangan 
             FROM booking b
             JOIN users u ON b.user_id = u.id
             JOIN lapangan l ON b.lapangan_id = l.id
             JOIN email_notifications e ON b.id = e.booking_id
             WHERE b.status_pembayaran = 'pending'
             AND e.type = 'payment'
             AND e.status = 'pending'
             AND e.scheduled_time <= NOW()";
             
    $result = mysqli_query($conn, $query);
    
    while ($booking = mysqli_fetch_assoc($result)) {
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
            $mail->Subject = 'Pengingat Pembayaran Booking Lapangan';
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #2C5F2D;">Pengingat Pembayaran</h2>
                <p>Halo ' . htmlspecialchars($booking['nama']) . ',</p>
                <p>Ini adalah pengingat untuk pembayaran booking lapangan Anda:</p>
                <div style="background-color: #f4f4f4; padding: 15px; margin: 20px 0;">
                    <p><strong>Detail Booking:</strong></p>
                    <ul>
                        <li>Lapangan: ' . htmlspecialchars($booking['nama_lapangan']) . '</li>
                        <li>Tanggal: ' . date('d/m/Y', strtotime($booking['tanggal_main'])) . '</li>
                        <li>Jam: ' . date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                                   date('H:i', strtotime($booking['jam_selesai'])) . '</li>
                        <li>Total: Rp ' . number_format($booking['total_harga'], 0, ',', '.') . '</li>
                    </ul>
                </div>
                <p>Mohon segera lakukan pembayaran untuk mengkonfirmasi booking Anda.</p>
                <p>Jika Anda sudah melakukan pembayaran, mohon abaikan email ini.</p>
                <hr style="border: 1px solid #eee; margin: 20px 0;">
                <p style="color: #666; font-size: 12px;">Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            </div>';

            $mail->send();
            
            // Update status pengingat
            mysqli_query($conn, "UPDATE email_notifications 
                               SET status = 'sent', 
                                   sent_time = NOW() 
                               WHERE booking_id = '{$booking['id']}'
                               AND type = 'payment'");
                               
            // Log aktivitas
            error_log("Berhasil mengirim pengingat pembayaran ke {$booking['email']}");
        } catch (Exception $e) {
            error_log("Gagal mengirim pengingat pembayaran ke {$booking['email']}: {$mail->ErrorInfo}");
            
            mysqli_query($conn, "UPDATE email_notifications 
                               SET status = 'failed', 
                                   sent_time = NOW() 
                               WHERE booking_id = '{$booking['id']}'
                               AND type = 'payment'");
        }
    }
}

/**
 * Kirim email pengingat jadwal main
 */
function sendScheduleReminders() {
    global $conn;
    
    // Ambil semua booking yang sudah dikonfirmasi dan mendekati jadwal main
    $query = "SELECT b.*, u.nama, u.email, l.nama as nama_lapangan 
             FROM booking b
             JOIN users u ON b.user_id = u.id
             JOIN lapangan l ON b.lapangan_id = l.id
             JOIN email_notifications e ON b.id = e.booking_id
             WHERE b.status_pembayaran = 'dikonfirmasi'
             AND e.type = 'schedule'
             AND e.status = 'pending'
             AND e.scheduled_time <= NOW()";
             
    $result = mysqli_query($conn, $query);
    
    while ($booking = mysqli_fetch_assoc($result)) {
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
            $mail->Subject = 'Pengingat Jadwal Main - Futsal Sayan';
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #2C5F2D;">Pengingat Jadwal Main</h2>
                <p>Halo ' . htmlspecialchars($booking['nama']) . ',</p>
                <p>Ini adalah pengingat untuk jadwal main Anda:</p>
                <div style="background-color: #f4f4f4; padding: 15px; margin: 20px 0;">
                    <p><strong>Detail Jadwal:</strong></p>
                    <ul>
                        <li>Lapangan: ' . htmlspecialchars($booking['nama_lapangan']) . '</li>
                        <li>Tanggal: ' . date('d/m/Y', strtotime($booking['tanggal_main'])) . '</li>
                        <li>Jam: ' . date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                                   date('H:i', strtotime($booking['jam_selesai'])) . '</li>
                    </ul>
                </div>
                <p>Selamat bermain!</p>
                <hr style="border: 1px solid #eee; margin: 20px 0;">
                <p style="color: #666; font-size: 12px;">Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
            </div>';

            $mail->send();
            
            // Update status pengingat
            mysqli_query($conn, "UPDATE email_notifications 
                               SET status = 'sent', 
                                   sent_time = NOW() 
                               WHERE booking_id = '{$booking['id']}'
                               AND type = 'schedule'");
                               
            // Log aktivitas
            error_log("Berhasil mengirim pengingat jadwal ke {$booking['email']}");
        } catch (Exception $e) {
            error_log("Gagal mengirim pengingat jadwal ke {$booking['email']}: {$mail->ErrorInfo}");
            
            mysqli_query($conn, "UPDATE email_notifications 
                               SET status = 'failed', 
                                   sent_time = NOW() 
                               WHERE booking_id = '{$booking['id']}'
                               AND type = 'schedule'");
        }
    }
}

// Jalankan fungsi pengingat
try {
    sendPaymentReminders();
    sendScheduleReminders();
} catch (Exception $e) {
    error_log("Error saat mengirim pengingat: " . $e->getMessage());
}
