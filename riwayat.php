<?php 
include 'includes/header.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data booking user
$query = "SELECT b.*, l.nama as nama_lapangan, l.harga_per_jam 
          FROM booking b 
          JOIN lapangan l ON b.lapangan_id = l.id 
          WHERE b.user_id = '$user_id' 
          ORDER BY b.tanggal_booking DESC";
$result = mysqli_query($conn, $query);

// Ambil total booking dan total pembayaran
$total_query = "SELECT COUNT(*) as total_booking, SUM(total_harga) as total_pembayaran 
                FROM booking 
                WHERE user_id = '$user_id'";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
?>

<div class="max-w-4xl mx-auto">
    <!-- Header dan Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-green-600 mb-2">Riwayat Booking</h2>
                <div class="text-gray-600">
                    <p class="mb-2">Lihat dan kelola riwayat booking Anda:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Lihat detail booking dan status pembayaran</li>
                        <li>Cetak atau simpan invoice untuk setiap booking</li>
                        <li>Pantau status pembayaran yang masih pending</li>
                        <li>Akses informasi pembayaran transfer atau QRIS</li>
                    </ul>
                </div>
            </div>
            <div class="text-right">
                <p class="font-semibold">Total Booking: <?php echo $total_data['total_booking']; ?></p>
                <p class="text-green-600 font-semibold">
                    Total Pembayaran: Rp <?php echo number_format($total_data['total_pembayaran'], 0, ',', '.'); ?>
                </p>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                <select name="status" class="rounded border-gray-300 shadow-sm">
                    <option value="semua" <?php echo (!isset($_GET['status']) || $_GET['status'] == 'semua') ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                    <option value="dikonfirmasi" <?php echo (isset($_GET['status']) && $_GET['status'] == 'dikonfirmasi') ? 'selected' : ''; ?>>Pembayaran Diterima</option>
                    <option value="dibatalkan" <?php echo (isset($_GET['status']) && $_GET['status'] == 'dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="rounded border-gray-300 shadow-sm" 
                       value="<?php echo isset($_GET['dari']) ? $_GET['dari'] : ''; ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="rounded border-gray-300 shadow-sm"
                       value="<?php echo isset($_GET['sampai']) ? $_GET['sampai'] : ''; ?>">
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="riwayat.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-sync-alt mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">Detail Booking</th>
                            <th class="px-6 py-3 text-left">Detail Pembayaran</th>
                            <th class="px-6 py-3 text-left">Status & Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while($booking = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-gray-50">
                                <!-- Detail Booking -->
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <p class="font-semibold">#<?php echo $booking['id']; ?> - <?php echo $booking['nama_lapangan']; ?></p>
                                        <p class="text-gray-600">
                                            <i class="far fa-calendar mr-1"></i>
                                            <?php echo date('d/m/Y', strtotime($booking['tanggal_main'])); ?>
                                        </p>
                                        <p class="text-gray-600">
                                            <i class="far fa-clock mr-1"></i>
                                            <?php echo date('H:i', strtotime($booking['jam_mulai'])) . ' - ' . 
                                                 date('H:i', strtotime($booking['jam_selesai'])); ?>
                                        </p>
                                    </div>
                                </td>

                                <!-- Detail Pembayaran -->
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <p class="font-semibold text-green-600">
                                            Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="text-gray-600">
                                            <?php 
                                            $metode_icon = '';
                                            $metode_text = '';
                                            switch($booking['metode_pembayaran']) {
                                                case 'transfer':
                                                    $metode_icon = 'university';
                                                    $metode_text = 'Transfer Bank';
                                                    break;
                                                case 'qris':
                                                    $metode_icon = 'qrcode';
                                                    $metode_text = 'QRIS';
                                                    break;
                                                case 'cod':
                                                    $metode_icon = 'money-bill-wave';
                                                    $metode_text = 'Bayar di Tempat';
                                                    break;
                                            }
                                            ?>
                                            <i class="fas fa-<?php echo $metode_icon; ?> mr-1"></i>
                                            <?php echo $metode_text; ?>
                                        </p>
                                        <?php if($booking['metode_pembayaran'] == 'transfer'): ?>
                                            <p class="text-sm text-gray-500">BCA: 1234567890</p>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Status & Invoice -->
                                <td class="px-6 py-4">
                                    <div class="space-y-3">
                                        <?php 
                                        $status_class = '';
                                        $status_icon = '';
                                        $status_text = '';
                                        
                                        switch($booking['status_pembayaran']) {
                                            case 'pending':
                                                $status_class = 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                                                $status_icon = 'clock';
                                                $status_text = 'Menunggu Pembayaran';
                                                break;
                                            case 'dikonfirmasi':
                                                $status_class = 'bg-green-100 text-green-800 border border-green-300';
                                                $status_icon = 'check-circle';
                                                $status_text = 'Pembayaran Diterima';
                                                break;
                                            case 'dibatalkan':
                                                $status_class = 'bg-red-100 text-red-800 border border-red-300';
                                                $status_icon = 'times-circle';
                                                $status_text = 'Dibatalkan';
                                                break;
                                        }
                                        ?>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 rounded-full <?php echo $status_class; ?> text-sm">
                                                <i class="fas fa-<?php echo $status_icon; ?> mr-1"></i>
                                                <?php echo $status_text; ?>
                                            </span>
                                        </div>

                                        <div class="flex space-x-2">
                                            <a href="invoice.php?booking_id=<?php echo $booking['id']; ?>" 
                                               class="flex items-center justify-center bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                <i class="fas fa-eye mr-1"></i> Lihat
                                            </a>
                                            <a href="invoice.php?booking_id=<?php echo $booking['id']; ?>&print=true" 
                                               target="_blank"
                                               class="flex items-center justify-center bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                                <i class="fas fa-print mr-1"></i> Cetak
                                            </a>
                                        </div>

                                        <?php if($booking['status_pembayaran'] == 'pending' && $booking['metode_pembayaran'] != 'cod'): ?>
                                            <p class="text-xs text-red-600">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Harap selesaikan pembayaran dalam 2 jam
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-600">Anda belum memiliki riwayat booking.</p>
            <a href="booking.php" class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Booking Sekarang
            </a>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Informasi Pembayaran Transfer -->
<div class="max-w-4xl mx-auto mt-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-green-600 mb-4">Informasi Pembayaran Transfer</h3>
        <div class="space-y-2">
            <p><strong>Bank:</strong> Bank BCA</p>
            <p><strong>No. Rekening:</strong> 1234567890</p>
            <p><strong>Atas Nama:</strong> Futsal Sayan Bekasi</p>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <p>Catatan:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Mohon transfer sesuai dengan total harga booking</li>
                <li>Sertakan nomor booking pada berita transfer</li>
                <li>Konfirmasi pembayaran akan diproses dalam 1x24 jam</li>
                <li>Untuk pembayaran COD, silakan melakukan pembayaran di tempat minimal 30 menit sebelum jadwal main</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
