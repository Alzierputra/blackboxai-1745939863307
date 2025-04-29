<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendVerificationEmail($to_email, $to_name, $verification_code) {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi Server
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Ganti dengan SMTP server Anda
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // Ganti dengan email Anda
        $mail->Password   = 'your-app-password'; // Ganti dengan password aplikasi email Anda
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Pengirim
        $mail->setFrom('your-email@gmail.com', 'Futsal Sayan');
        $mail->addAddress($to_email, $to_name);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = 'Verifikasi Akun Futsal Sayan';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #2C5F2D;">Verifikasi Akun Futsal Sayan</h2>
            <p>Halo ' . htmlspecialchars($to_name) . ',</p>
            <p>Terima kasih telah mendaftar di Futsal Sayan. Untuk menyelesaikan pendaftaran, masukkan kode verifikasi berikut:</p>
            <div style="background-color: #f4f4f4; padding: 15px; text-align: center; margin: 20px 0;">
                <h1 style="color: #2C5F2D; letter-spacing: 5px; margin: 0;">' . $verification_code . '</h1>
            </div>
            <p>Kode ini akan kadaluarsa dalam 10 menit.</p>
            <p>Jika Anda tidak merasa mendaftar di Futsal Sayan, abaikan email ini.</p>
            <hr style="border: 1px solid #eee; margin: 20px 0;">
            <p style="color: #666; font-size: 12px;">Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
