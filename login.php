<?php
session_start();

// Jika user sudah login, arahkan langsung ke halaman utama
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Ambil pesan error dari session jika ada
$error_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    if ($flash['type'] === 'error') {
        $error_message = $flash['message'];
    }
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Kasir</title>
    <link href="/web-kasir-new/assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900">

    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
        <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            Aplikasi Kasir
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    Sign in to your account
                </h1>

                <?php if ($error_message): ?>
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400" role="alert">
                        <span class="font-medium">Login Gagal!</span> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-4 md:space-y-6" action="fungsi/auth.php" method="POST">
                    <div>
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                        <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="admin" required>
                    </div>
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                        <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    </div>
                    <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-primary-800">Sign in</button>
                    <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                        Belum punya akun? <a href="register.php" class="font-medium text-primary-600 hover:underline dark:text-primary-500">Daftar di sini</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

</body>

</html>