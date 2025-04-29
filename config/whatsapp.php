<?php
// Konfigurasi WhatsApp Gateway
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/v1/messages'); // Ganti dengan URL API WhatsApp yang sesuai
define('WHATSAPP_API_KEY', 'your_api_key_here'); // Ganti dengan API key Anda

/**
 * Fungsi untuk mengirim pesan WhatsApp
 * @param string $phone_number Nomor telepon penerima
 * @param string $message Isi pesan
 * @return array Response dari API
 */
function sendWhatsAppMessage($phone_number, $message) {
    $phone_number = formatPhoneNumber($phone_number);
    
    $data = [
        'phone' => $phone_number,
        'message' => $message,
        'api_key' => WHATSAPP_API_KEY
    ];

    $ch = curl_init(WHATSAPP_API_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("WhatsApp API Error: " . $error);
        return ['success' => false, 'error' => $error];
    }
    
    return json_decode($response, true);
}

/**
 * Fungsi untuk memformat nomor telepon
 * @param string $phone_number Nomor telepon
 * @return string Nomor telepon yang sudah diformat
 */
function formatPhoneNumber($phone_number) {
    // Hapus karakter non-digit
    $phone_number = preg_replace('/[^0-9]/', '', $phone_number);
    
    // Jika dimulai dengan 0, ganti dengan 62
    if (substr($phone_number, 0, 1) === '0') {
        $phone_number = '62' . substr($phone_number, 1);
    }
    
    return $phone_number;
}

/**
 * Template pesan untuk verifikasi
 * @param string $nama Nama pengguna
 * @param string $code Kode verifikasi
 * @return string
 */
function getVerificationMessageTemplate($nama, $code) {
    return "Halo {$nama},\n\n"
         . "Kode verifikasi pendaftaran akun Futsal Sayan Anda adalah: *{$code}*\n\n"
         . "Kode ini berlaku selama 10 menit.\n"
         . "Jangan bagikan kode ini kepada siapapun.";
}

/**
 * Template pesan untuk pengingat pembayaran
 * @param array $booking Data booking
 * @return string
 */
function getPaymentReminderTemplate($booking) {
    return "Halo {$booking['nama']},\n\n"
         . "Reminder untuk pembayaran booking lapangan:\n"
         . "Lapangan: {$booking['nama_lapangan']}\n"
         . "Jadwal: {$booking['tanggal_main']} {$booking['jam_mulai']}\n"
         . "Total: Rp " . number_format($booking['total_harga'], 0, ',', '.') . "\n"
         . "Batas pembayaran: {$booking['batas_bayar']}\n\n"
         . "Silakan lakukan pembayaran segera.";
}

/**
 * Template pesan untuk pengingat jadwal main
 * @param array $booking Data booking
 * @return string
 */
function getScheduleReminderTemplate($booking) {
    return "Halo {$booking['nama']},\n\n"
         . "Reminder jadwal main Anda:\n"
         . "Lapangan: {$booking['nama_lapangan']}\n"
         . "Tanggal: {$booking['tanggal_main']}\n"
         . "Jam: {$booking['jam_mulai']} - {$booking['jam_selesai']}\n\n"
         . "Selamat bermain!";
}
