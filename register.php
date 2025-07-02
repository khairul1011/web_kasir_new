<?php
session_start();

// Jika user sudah login, arahkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Ambil pesan notifikasi dari session jika ada
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aplikasi Kasir</title>
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
                Buat Akun Baru
            </h1>

            <?php if ($flash_message): ?>
            <div class="p-4 mb-4 text-sm rounded-lg <?php echo $flash_message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                <span class="font-medium"><?php echo htmlspecialchars($flash_message['message']); ?></span>
            </div>
            <?php endif; ?>
            
            <form class="space-y-4 md:space-y-6" action="fungsi/register_process.php" method="POST">
                <div>
                    <label for="nama" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" placeholder="John Doe" required>
                </div>
                <div>
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                    <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" placeholder="john_doe" required>
                </div>
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" required>
                </div>
                <input type="hidden" name="role" value="kasir">

                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Buat Akun</button>
                <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                    Sudah punya akun? <a href="login.php" class="font-medium text-blue-600 hover:underline dark:text-blue-500">Login di sini</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>