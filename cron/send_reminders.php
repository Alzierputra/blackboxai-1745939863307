<?php
require_once '../config/database.php';
require_once '../config/whatsapp.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Kirim pengingat pembayaran
 */
function sendPaymentReminders() {
    global $conn;
    
    // Ambil semua booking yang pending dan belum lewat batas waktu
    $query = "SELECT b.*, u.nama, u.telepon, l.nama as nama_lapangan 
             FROM booking b
             JOIN users u ON b.user_id = u.id
             JOIN lapangan l ON b.lapangan_id = l.id
             JOIN reminders r ON b.id = r.booking_id
             WHERE b.status_pembayaran = 'pending'
             AND r.type = 'payment'
             AND r.status = 'pending'
             AND r.scheduled_time <= NOW()";
             
    $result = mysqli_query($conn, $query);
    
    while ($booking = mysqli_fetch_assoc($result)) {
        // Kirim pesan WhatsApp
        $message = getPaymentReminderTemplate($booking);
        $response = sendWhatsAppMessage($booking['telepon'], $message);
        
        // Update status pengingat
        $status = $response['success'] ? 'sent' : 'failed';
        $reminder_id = $booking['reminder_id'];
        
        mysqli_query($conn, "UPDATE reminders 
                           SET status = '$status', 
                               sent_time = NOW() 
                           WHERE id = '$reminder_id'");
                           
        // Log aktivitas
        $log_message = $response['success'] ? 
            "Berhasil mengirim pengingat pembayaran ke {$booking['nama']}" : 
            "Gagal mengirim pengingat pembayaran ke {$booking['nama']}: " . $response['error'];
        error_log($log_message);
    }
}

/**
 * Kirim pengingat jadwal main
 */
function sendScheduleReminders() {
    global $conn;
    
    // Ambil semua booking yang sudah dikonfirmasi dan mendekati jadwal main
    $query = "SELECT b.*, u.nama, u.telepon, l.nama as nama_lapangan 
             FROM booking b
             JOIN users u ON b.user_id = u.id
             JOIN lapangan l ON b.lapangan_id = l.id
             JOIN reminders r ON b.id = r.booking_id
             WHERE b.status_pembayaran = 'dikonfirmasi'
             AND r.type = 'schedule'
             AND r.status = 'pending'
             AND r.scheduled_time <= NOW()";
             
    $result = mysqli_query($conn, $query);
    
    while ($booking = mysqli_fetch_assoc($result)) {
        // Kirim pesan WhatsApp
        $message = getScheduleReminderTemplate($booking);
        $response = sendWhatsAppMessage($booking['telepon'], $message);
        
        // Update status pengingat
        $status = $response['success'] ? 'sent' : 'failed';
        $reminder_id = $booking['reminder_id'];
        
        mysqli_query($conn, "UPDATE reminders 
                           SET status = '$status', 
                               sent_time = NOW() 
                           WHERE id = '$reminder_id'");
                           
        // Log aktivitas
        $log_message = $response['success'] ? 
            "Berhasil mengirim pengingat jadwal ke {$booking['nama']}" : 
            "Gagal mengirim pengingat jadwal ke {$booking['nama']}: " . $response['error'];
        error_log($log_message);
    }
}

// Jalankan fungsi pengingat
try {
    sendPaymentReminders();
    sendScheduleReminders();
} catch (Exception $e) {
    error_log("Error saat mengirim pengingat: " . $e->getMessage());
}
