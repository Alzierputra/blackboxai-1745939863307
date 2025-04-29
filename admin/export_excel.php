<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Set header untuk download file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Booking_Futsal_Sayan_" . date('Y-m-d') . ".xls");

// Filter berdasarkan tanggal jika ada
$where = "";
if (isset($_GET['dari']) && isset($_GET['sampai'])) {
    $dari = mysqli_real_escape_string($conn, $_GET['dari']);
    $sampai = mysqli_real_escape_string($conn, $_GET['sampai']);
    $where = "WHERE b.tanggal_main BETWEEN '$dari' AND '$sampai'";
}

// Query untuk mengambil data booking
$query = "SELECT 
            b.id as booking_id,
            u.nama as nama_pelanggan,
            u.telepon,
            l.nama as nama_lapangan,
            b.tanggal_main,
            b.jam_mulai,
            b.jam_selesai,
            b.total_harga,
            b.metode_pembayaran,
            b.status_pembayaran,
            b.tanggal_booking
          FROM booking b
          JOIN users u ON b.user_id = u.id
          JOIN lapangan l ON b.lapangan_id = l.id
          $where
          ORDER BY b.tanggal_booking DESC";

$result = mysqli_query($conn, $query);
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="9" style="text-align: center; font-size: 16px;">
                LAPORAN BOOKING FUTSAL SAYAN BEKASI
            </th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center;">
                Periode: <?php echo isset($_GET['dari']) ? date('d/m/Y', strtotime($_GET['dari'])) : 'Semua'; ?> 
                s/d 
                <?php echo isset($_GET['sampai']) ? date('d/m/Y', strtotime($_GET['sampai'])) : 'Semua'; ?>
            </th>
        </tr>
        <tr>
            <th>No.</th>
            <th>ID Booking</th>
            <th>Nama Pelanggan</th>
            <th>Telepon</th>
            <th>Lapangan</th>
            <th>Tanggal Main</th>
            <th>Jam</th>
            <th>Total Harga</th>
            <th>Metode Pembayaran</th>
            <th>Status</th>
            <th>Tanggal Booking</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $total = 0;
        while($row = mysqli_fetch_assoc($result)): 
            $total += $row['total_harga'];
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td>#<?php echo $row['booking_id']; ?></td>
            <td><?php echo $row['nama_pelanggan']; ?></td>
            <td><?php echo $row['telepon']; ?></td>
            <td><?php echo $row['nama_lapangan']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($row['tanggal_main'])); ?></td>
            <td><?php echo date('H:i', strtotime($row['jam_mulai'])) . ' - ' . date('H:i', strtotime($row['jam_selesai'])); ?></td>
            <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
            <td><?php echo $row['metode_pembayaran'] == 'transfer' ? 'Transfer Bank' : 'Bayar di Tempat'; ?></td>
            <td><?php echo ucfirst($row['status_pembayaran']); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_booking'])); ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="7" style="text-align: right;"><strong>Total Pendapatan:</strong></td>
            <td colspan="4"><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
        </tr>
    </tbody>
</table>
