<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "db_futsal_sayan1";

// Membuat koneksi ke database
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
