<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Sayan Bekasi</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
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
                <a href="index.php" class="text-2xl font-bold">Futsal Sayan</a>
                <div class="space-x-4">
                    <a href="index.php" class="hover:text-green-200">Beranda</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a href="admin/dashboard.php" class="hover:text-green-200">Dashboard Admin</a>
                        <?php else: ?>
                            <a href="booking.php" class="hover:text-green-200">Booking</a>
                            <a href="riwayat.php" class="hover:text-green-200">Riwayat</a>
                        <?php endif; ?>
                        <a href="logout.php" class="hover:text-green-200">Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-green-200">Masuk</a>
                        <a href="register.php" class="hover:text-green-200">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
