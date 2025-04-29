<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="relative bg-green-600 text-white py-16 mb-8">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl">
            <h1 class="text-4xl font-bold mb-4">Selamat Datang di Futsal Sayan Bekasi</h1>
            <p class="text-xl mb-8">Nikmati pengalaman bermain futsal dengan fasilitas terbaik dan harga terjangkau</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-100 transition duration-300">Daftar Sekarang</a>
            <?php else: ?>
                <a href="booking.php" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-100 transition duration-300">Booking Lapangan</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fasilitas Section -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">Fasilitas Kami</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <i class="fas fa-parking text-4xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Parkir Luas</h3>
            <p class="text-gray-600">Area parkir yang luas dan aman untuk kendaraan anda</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <i class="fas fa-shower text-4xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Kamar Mandi & Ruang Ganti</h3>
            <p class="text-gray-600">Fasilitas kamar mandi dan ruang ganti yang bersih</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <i class="fas fa-store text-4xl text-green-600 mb-4"></i>
            <h3 class="text-xl font-semibold mb-2">Kantin</h3>
            <p class="text-gray-600">Tersedia kantin dengan berbagai makanan dan minuman</p>
        </div>
    </div>
</div>

<!-- Daftar Lapangan -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">Lapangan Kami</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php
        require_once 'includes/functions.php';
        
        $query = "SELECT * FROM lapangan";
        $result = mysqli_query($conn, $query);
        $tanggal_sekarang = date('Y-m-d');
        $jam_sekarang = date('H:i:s');
        
        while($lapangan = mysqli_fetch_assoc($result)):
            // Cek status lapangan saat ini
            $status = cekStatusLapangan($conn, $lapangan['id'], $tanggal_sekarang, $jam_sekarang);
            
            // Ambil jadwal hari ini
            $jadwal_hari_ini = getJadwalLapangan($conn, $lapangan['id'], $tanggal_sekarang);
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="assets/images/<?php echo $lapangan['gambar']; ?>" alt="<?php echo $lapangan['nama']; ?>" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?php echo $lapangan['nama']; ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo $lapangan['deskripsi']; ?></p>
                    
                    <!-- Status Lapangan -->
                    <div class="mb-4">
                        <p class="font-semibold mb-2">Status Saat Ini:</p>
                        <?php if($status['tersedia']): ?>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-check-circle mr-1"></i> Tersedia
                            </span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-clock mr-1"></i> 
                                <?php echo $status['keterangan']; ?>
                                (<?php echo formatJam($status['jam_mulai']); ?> - <?php echo formatJam($status['jam_selesai']); ?>)
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Jadwal Hari Ini -->
                    <?php if(!empty($jadwal_hari_ini)): ?>
                    <div class="mb-4">
                        <p class="font-semibold mb-2">Jadwal Hari Ini:</p>
                        <div class="space-y-2">
                            <?php foreach($jadwal_hari_ini as $jadwal): ?>
                                <div class="bg-gray-50 p-2 rounded text-sm">
                                    <span class="font-medium"><?php echo formatJam($jadwal['jam_mulai']); ?> - <?php echo formatJam($jadwal['jam_selesai']); ?></span>
                                    <span class="text-gray-600"> • <?php echo $jadwal['nama_user']; ?></span>
                                    <?php if($jadwal['status_pembayaran'] == 'pending'): ?>
                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full ml-2">Menunggu Pembayaran</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="flex justify-between items-center">
                        <span class="text-green-600 font-semibold">Rp <?php echo number_format($lapangan['harga_per_jam'], 0, ',', '.'); ?>/jam</span>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                            <a href="booking.php?lapangan=<?php echo $lapangan['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-300">
                                <i class="fas fa-calendar-plus mr-1"></i> Booking
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Cara Booking Section -->
<div class="mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">Cara Booking Lapangan</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">1</div>
            <h3 class="font-semibold mb-2">Daftar/Login</h3>
            <p class="text-gray-600">Buat akun atau login jika sudah memiliki akun</p>
        </div>
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">2</div>
            <h3 class="font-semibold mb-2">Pilih Lapangan</h3>
            <p class="text-gray-600">Pilih lapangan yang ingin anda sewa</p>
        </div>
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">3</div>
            <h3 class="font-semibold mb-2">Pilih Jadwal</h3>
            <p class="text-gray-600">Pilih tanggal dan jam yang tersedia</p>
        </div>
        <div class="text-center">
            <div class="bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">4</div>
            <h3 class="font-semibold mb-2">Pembayaran</h3>
            <p class="text-gray-600">Lakukan pembayaran via transfer atau COD</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
