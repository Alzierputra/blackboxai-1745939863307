<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses konfirmasi pembayaran
if (isset($_POST['konfirmasi'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $query = "UPDATE booking SET status_pembayaran = 'dikonfirmasi' WHERE id = '$booking_id'";
    mysqli_query($conn, $query);
    $success = "Pembayaran berhasil dikonfirmasi!";
}

// Proses pembatalan booking
if (isset($_POST['batalkan'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $query = "UPDATE booking SET status_pembayaran = 'dibatalkan' WHERE id = '$booking_id'";
    mysqli_query($conn, $query);
    $success = "Booking berhasil dibatalkan!";
}

// Mengambil data booking yang pending
$query = "SELECT b.*, u.nama as nama_user, u.telepon, l.nama as nama_lapangan 
          FROM booking b 
          JOIN users u ON b.user_id = u.id 
          JOIN lapangan l ON b.lapangan_id = l.id 
          WHERE b.status_pembayaran = 'pending' 
          ORDER BY b.tanggal_booking DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Futsal Sayan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="dashboard.php" class="text-2xl font-bold">Admin Futsal Sayan</a>
                <div class="space-x-4">
                    <a href="dashboard.php" class="hover:text-green-200">Dashboard</a>
                    <a href="konfirmasi.php" class="hover:text-green-200">Konfirmasi Pembayaran</a>
                    <a href="lapangan.php" class="hover:text-green-200">Kelola Lapangan</a>
                    <a href="../logout.php" class="hover:text-green-200">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-6">Konfirmasi Pembayaran</h2>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Booking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lapangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Main</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($booking = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="px-6 py-4">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4"><?php echo $booking['nama_user']; ?></td>
                                <td class="px-6 py-4"><?php echo $booking['telepon']; ?></td>
                                <td class="px-6 py-4"><?php echo $booking['nama_lapangan']; ?></td>
                                <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($booking['tanggal_main'])); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    echo date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                                         date('H:i', strtotime($booking['jam_selesai'])); 
                                    ?>
                                </td>
                                <td class="px-6 py-4">Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    switch($booking['metode_pembayaran']) {
                                        case 'transfer':
                                            echo '<span class="flex items-center"><i class="fas fa-university mr-2"></i>Transfer Bank</span>';
                                            break;
                                        case 'qris':
                                            echo '<span class="flex items-center"><i class="fas fa-qrcode mr-2"></i>QRIS</span>';
                                            break;
                                        case 'cod':
                                            echo '<span class="flex items-center"><i class="fas fa-money-bill-wave mr-2"></i>Bayar di Tempat</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" 
                                                    name="konfirmasi" 
                                                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                Konfirmasi
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" 
                                                    name="batalkan" 
                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                    onclick="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                                                Batalkan
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-600">Tidak ada pembayaran yang perlu dikonfirmasi.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
