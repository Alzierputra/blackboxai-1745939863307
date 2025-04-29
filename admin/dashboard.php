<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Mengambil statistik
$query_total_booking = "SELECT COUNT(*) as total FROM booking";
$result_total_booking = mysqli_query($conn, $query_total_booking);
$total_booking = mysqli_fetch_assoc($result_total_booking)['total'];

$query_pending = "SELECT COUNT(*) as total FROM booking WHERE status_pembayaran = 'pending'";
$result_pending = mysqli_query($conn, $query_pending);
$total_pending = mysqli_fetch_assoc($result_pending)['total'];

$query_confirmed = "SELECT COUNT(*) as total FROM booking WHERE status_pembayaran = 'dikonfirmasi'";
$result_confirmed = mysqli_query($conn, $query_confirmed);
$total_confirmed = mysqli_fetch_assoc($result_confirmed)['total'];

// Mengambil booking terbaru
$query_recent = "SELECT b.*, u.nama as nama_user, l.nama as nama_lapangan 
                FROM booking b 
                JOIN users u ON b.user_id = u.id 
                JOIN lapangan l ON b.lapangan_id = l.id 
                ORDER BY b.tanggal_booking DESC 
                LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Futsal Sayan</title>
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Dashboard Admin</h2>
            
            <!-- Filter dan Export -->
            <div class="flex items-center space-x-4">
                <form action="" method="GET" class="flex items-center space-x-2">
                    <input type="date" name="dari" class="border rounded px-2 py-1" value="<?php echo isset($_GET['dari']) ? $_GET['dari'] : ''; ?>">
                    <span>s/d</span>
                    <input type="date" name="sampai" class="border rounded px-2 py-1" value="<?php echo isset($_GET['sampai']) ? $_GET['sampai'] : ''; ?>">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </form>
                
                <?php
                $export_url = "export_excel.php";
                if (isset($_GET['dari']) && isset($_GET['sampai'])) {
                    $export_url .= "?dari=" . $_GET['dari'] . "&sampai=" . $_GET['sampai'];
                }
                ?>
                <a href="<?php echo $export_url; ?>" class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
            </div>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500">Total Booking</p>
                        <p class="text-2xl font-semibold"><?php echo $total_booking; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500">Menunggu Konfirmasi</p>
                        <p class="text-2xl font-semibold"><?php echo $total_pending; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500">Booking Dikonfirmasi</p>
                        <p class="text-2xl font-semibold"><?php echo $total_confirmed; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Terbaru -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-xl font-semibold">Booking Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lapangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($booking = mysqli_fetch_assoc($result_recent)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $booking['nama_user']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $booking['nama_lapangan']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo date('d/m/Y', strtotime($booking['tanggal_main'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $status_class = '';
                                    switch($booking['status_pembayaran']) {
                                        case 'pending':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'dikonfirmasi':
                                            $status_class = 'bg-green-100 text-green-800';
                                            break;
                                        case 'dibatalkan':
                                            $status_class = 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $status_class; ?>">
                                        <?php echo ucfirst($booking['status_pembayaran']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
