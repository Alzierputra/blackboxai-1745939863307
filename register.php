<?php 
include 'includes/header.php';
require_once 'config/whatsapp.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        
        // Cek apakah username sudah ada
        $check_query = "SELECT * FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            // Generate kode verifikasi
            $verification_code = generateVerificationCode();
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Insert user baru
            $query = "INSERT INTO users (nama, telepon, alamat, username, password, verification_code, verification_expires) 
                     VALUES ('$nama', '$telepon', '$alamat', '$username', '$password', '$verification_code', '$expires')";
            
            if (mysqli_query($conn, $query)) {
                // Kirim kode verifikasi via WhatsApp
                $response = sendWhatsAppVerification($telepon, $nama, $verification_code);
                
                if ($response['success']) {
                    // Redirect ke halaman verifikasi
                    $_SESSION['temp_user_id'] = mysqli_insert_id($conn);
                    header('Location: verify.php');
                    exit();
                } else {
                    $error = "Gagal mengirim kode verifikasi. Silakan coba lagi.";
                    // Hapus user jika gagal kirim kode
                    mysqli_query($conn, "DELETE FROM users WHERE id = " . mysqli_insert_id($conn));
                }
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Daftar Akun Baru</h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="space-y-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">
                Nama Lengkap
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="nama" 
                   type="text" 
                   name="nama" 
                   required>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="telepon">
                Nomor WhatsApp
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-600">+62</span>
                <input class="shadow appearance-none border rounded w-full py-2 pl-12 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="telepon" 
                       type="tel" 
                       name="telepon" 
                       pattern="[0-9]+" 
                       placeholder="8xxxxxxxxxx"
                       required>
            </div>
            <p class="text-sm text-gray-500 mt-1">Contoh: 81234567890 (tanpa angka 0 di depan)</p>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="alamat">
                Alamat
            </label>
            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      id="alamat" 
                      name="alamat" 
                      rows="3" 
                      required></textarea>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                Username
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="username" 
                   type="text" 
                   name="username" 
                   required>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                Password
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="password" 
                   type="password" 
                   name="password" 
                   required>
        </div>

        <div>
            <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                    type="submit"
                    name="register">
                Daftar
            </button>
        </div>
    </form>

    <p class="text-center mt-4 text-gray-600">
        Sudah punya akun? 
        <a href="login.php" class="text-green-600 hover:text-green-800">
            Masuk di sini
        </a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
