<?php
require_once 'config/database.php';
require_once 'config/email.php';
require_once 'includes/functions.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Header
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Email Notifikasi - Futsal Sayan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Kirim Email Notifikasi</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Status Pengiriman:</h2>
            
            <?php
            try {
                // Ambil booking yang perlu dikirim email
                $query = "SELECT b.*, u.nama, u.email, l.nama as nama_lapangan 
                         FROM booking b
                         JOIN users u ON b.user_id = u.id
                         JOIN lapangan l ON b.lapangan_id = l.id
                         JOIN email_notifications e ON b.id = e.booking_id
                         WHERE e.status = 'pending'
                         AND e.scheduled_time <= NOW()
                         ORDER BY e.scheduled_time ASC";
                         
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    echo "<ul class='space-y-2'>";
                    
                    while ($booking = mysqli_fetch_assoc($result)) {
                        // Kirim email sesuai jenis notifikasi
                        if ($booking['status_pembayaran'] == 'pending') {
                            // Kirim pengingat pembayaran
                            $success = sendBookingConfirmationEmail($booking['id']);
                        } else {
                            // Kirim pengingat jadwal
                            $success = sendScheduleReminder($booking['id']);
                        }
                        
                        // Tampilkan status
                        $status_class = $success ? 'text-green-600' : 'text-red-600';
                        $status_text = $success ? 'Berhasil' : 'Gagal';
                        
                        echo "<li class='flex items-center justify-between border-b pb-2'>
                                <span>Email ke: {$booking['email']}</span>
                                <span class='{$status_class} font-semibold'>{$status_text}</span>
                              </li>";
                        
                        // Update status notifikasi
                        $status = $success ? 'sent' : 'failed';
                        mysqli_query($conn, "UPDATE email_notifications 
                                           SET status = '$status', 
                                               sent_time = NOW() 
                                           WHERE booking_id = '{$booking['id']}'");
                    }
                    
                    echo "</ul>";
                } else {
                    echo "<p class='text-gray-600'>Tidak ada email yang perlu dikirim saat ini.</p>";
                }
                
            } catch (Exception $e) {
                echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded'>
                        Error: " . htmlspecialchars($e->getMessage()) . "
                      </div>";
            }
            ?>
            
            <div class="mt-6">
                <a href="admin/dashboard.php" 
                   class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
