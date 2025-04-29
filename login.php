<?php 
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Masuk ke Akun Anda</h2>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                Username
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="username" 
                   type="text" 
                   name="username" 
                   required>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                Password
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="password" 
                   type="password" 
                   name="password" 
                   required>
        </div>

        <div class="flex items-center justify-between">
            <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full"
                    type="submit">
                Masuk
            </button>
        </div>
    </form>

    <p class="text-center mt-4 text-gray-600">
        Belum punya akun? 
        <a href="register.php" class="text-green-600 hover:text-green-800">
            Daftar di sini
        </a>
    </p>

    <!-- Informasi Login Admin -->
    <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-center text-sm text-gray-600">
            Admin Login:<br>
            Username: admin<br>
            Password: admin123
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
