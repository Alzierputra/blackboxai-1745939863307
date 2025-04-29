<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses tambah lapangan
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $gambar = mysqli_real_escape_string($conn, $_POST['gambar']);
    
    $query = "INSERT INTO lapangan (nama, deskripsi, harga_per_jam, gambar) 
              VALUES ('$nama', '$deskripsi', '$harga', '$gambar')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Lapangan berhasil ditambahkan!";
    } else {
        $error = "Terjadi kesalahan. Silakan coba lagi.";
    }
}

// Proses update status lapangan
if (isset($_POST['update_status'])) {
    $lapangan_id = mysqli_real_escape_string($conn, $_POST['lapangan_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE lapangan SET status = '$status' WHERE id = '$lapangan_id'";
    
    if (mysqli_query($conn, $query)) {
        $success = "Status lapangan berhasil diupdate!";
    } else {
        $error = "Terjadi kesalahan. Silakan coba lagi.";
    }
}

// Mengambil data lapangan
$query = "SELECT * FROM lapangan ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lapangan - Futsal Sayan</title>
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
            <h2 class="text-2xl font-bold">Kelola Lapangan</h2>
            <button onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Tambah Lapangan
            </button>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Daftar Lapangan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php while($lapangan = mysqli_fetch_assoc($result)): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <img src="../assets/images/<?php echo $lapangan['gambar']; ?>" 
                         alt="<?php echo $lapangan['nama']; ?>" 
                         class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?php echo $lapangan['nama']; ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo $lapangan['deskripsi']; ?></p>
                        <p class="text-green-600 font-semibold mb-4">
                            Rp <?php echo number_format($lapangan['harga_per_jam'], 0, ',', '.'); ?>/jam
                        </p>
                        <form method="POST" class="flex space-x-2">
                            <input type="hidden" name="lapangan_id" value="<?php echo $lapangan['id']; ?>">
                            <select name="status" class="flex-1 border rounded px-3 py-2">
                                <option value="tersedia" <?php echo $lapangan['status'] == 'tersedia' ? 'selected' : ''; ?>>
                                    Tersedia
                                </option>
                                <option value="maintenance" <?php echo $lapangan['status'] == 'maintenance' ? 'selected' : ''; ?>>
                                    Maintenance
                                </option>
                            </select>
                            <button type="submit" 
                                    name="update_status" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Update
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Tambah Lapangan -->
    <div id="modalTambah" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Tambah Lapangan</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')"
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">
                        Nama Lapangan
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                           type="text" 
                           name="nama" 
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="deskripsi">
                        Deskripsi
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                              name="deskripsi" 
                              rows="3" 
                              required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="harga">
                        Harga per Jam
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                           type="number" 
                           name="harga" 
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="gambar">
                        Nama File Gambar
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                           type="text" 
                           name="gambar" 
                           placeholder="contoh: lapangan1.jpg"
                           required>
                </div>

                <div class="flex justify-end">
                    <button type="button" 
                            onclick="document.getElementById('modalTambah').classList.add('hidden')"
                            class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" 
                            name="tambah" 
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
