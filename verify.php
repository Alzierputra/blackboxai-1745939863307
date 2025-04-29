<?php
include 'includes/header.php';
require_once 'config/email.php';

// Redirect jika tidak ada session temp_user_id
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: register.php');
    exit();
}

$user_id = $_SESSION['temp_user_id'];

// Ambil data user
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Cek apakah kode sudah expired
if ($user['verification_expires'] < date('Y-m-d H:i:s')) {
    // Hapus user jika kode expired
    mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");
    unset($_SESSION['temp_user_id']);
    $_SESSION['error'] = "Kode verifikasi telah kadaluarsa. Silakan daftar kembali.";
    header('Location: register.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify'])) {
        $code = mysqli_real_escape_string($conn, $_POST['verification_code']);
        
        if ($code === $user['verification_code']) {
            // Update status verifikasi
            mysqli_query($conn, "UPDATE users 
                               SET is_verified = 1, 
                                   verification_code = NULL, 
                                   verification_expires = NULL 
                               WHERE id = '$user_id'");
            
            // Hapus session temporary
            unset($_SESSION['temp_user_id']);
            
            $success = "Verifikasi berhasil! Silakan login.";
            // Redirect ke login setelah 3 detik
            header("refresh:3;url=login.php");
        } else {
            $error = "Kode verifikasi salah. Silakan coba lagi.";
        }
    } elseif (isset($_POST['resend'])) {
        // Generate kode baru
        $new_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Update kode verifikasi
        mysqli_query($conn, "UPDATE users 
                           SET verification_code = '$new_code', 
                               verification_expires = '$expires' 
                           WHERE id = '$user_id'");
        
        // Kirim ulang email
        if (sendVerificationEmail($user['email'], $user['nama'], $new_code)) {
            $success = "Kode verifikasi baru telah dikirim ke email Anda.";
        } else {
            $error = "Gagal mengirim ulang kode. Silakan coba lagi.";
        }
    }
}
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6 mt-8">
    <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Verifikasi Email</h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-6">
        <p class="text-gray-600">
            Kami telah mengirim kode verifikasi ke email Anda:<br>
            <span class="font-semibold"><?php echo $user['email']; ?></span>
        </p>
    </div>

    <form method="POST" action="" class="space-y-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="verification_code">
                Masukkan Kode Verifikasi
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 text-center text-2xl tracking-widest leading-tight focus:outline-none focus:shadow-outline"
                   id="verification_code" 
                   type="text" 
                   name="verification_code"
                   maxlength="6"
                   pattern="[0-9]{6}"
                   placeholder="000000"
                   required>
        </div>

        <div>
            <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                    type="submit"
                    name="verify">
                Verifikasi
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-gray-600 mb-2">Tidak menerima kode?</p>
        <form method="POST" action="">
            <button type="submit" 
                    name="resend"
                    class="text-green-600 hover:text-green-800 font-semibold">
                Kirim Ulang Kode
            </button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            Kode verifikasi akan kadaluarsa dalam 
            <span id="countdown" class="font-semibold"></span>
        </p>
    </div>
</div>

<script>
// Hitung mundur
function startCountdown() {
    const expiresTime = new Date("<?php echo $user['verification_expires']; ?>").getTime();
    
    const countdown = setInterval(function() {
        const now = new Date().getTime();
        const distance = expiresTime - now;
        
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("countdown").innerHTML = minutes + "m " + seconds + "s";
        
        if (distance < 0) {
            clearInterval(countdown);
            document.getElementById("countdown").innerHTML = "EXPIRED";
            window.location.href = 'register.php';
        }
    }, 1000);
}

// Format input kode verifikasi
document.getElementById('verification_code').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 6);
});

// Mulai countdown saat halaman dimuat
startCountdown();
</script>

<?php include 'includes/footer.php'; ?>
