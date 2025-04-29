<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Ambil parameter
$tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($conn, $_GET['tanggal']) : date('Y-m-d');
$lapangan_id = isset($_GET['lapangan_id']) ? mysqli_real_escape_string($conn, $_GET['lapangan_id']) : '';

if (empty($lapangan_id)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

// Ambil jadwal booking
$jadwal = getJadwalLapangan($conn, $lapangan_id, $tanggal);

// Format jadwal untuk response JSON
$formatted_jadwal = array_map(function($booking) {
    return [
        'nama_user' => $booking['nama_user'],
        'jam_mulai' => formatJam($booking['jam_mulai']),
        'jam_selesai' => formatJam($booking['jam_selesai']),
        'status_pembayaran' => $booking['status_pembayaran']
    ];
}, $jadwal);

// Return response
header('Content-Type: application/json');
echo json_encode([
    'tanggal' => $tanggal,
    'lapangan_id' => $lapangan_id,
    'jadwal' => $formatted_jadwal
]);
?>
