<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'POS Kasir Admin'; ?></title>
    <link href="/web-kasir-new/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">

    <?php
    // Admin Navbar akan mengandung tombol toggle sidebar
    include __DIR__ . '/../includes/admin_navbar.php';
    ?>

    <?php
    // Sidebar utama
    include __DIR__ . '/../includes/admin_sidebar.php';
    ?>

    <div class="p-4 sm:ml-64"> <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14"> <?php
            // Konten modul akan dimuat di sini
            if (isset($content_path) && file_exists(ADMIN_ROOT_PATH . '/' . $content_path)) {
                include ADMIN_ROOT_PATH . '/' . $content_path;
            } else {
                echo '<p class="text-center text-gray-700 dark:text-gray-300">Konten tidak ditemukan atau sedang dalam pengembangan.</p>';
            }
            ?>
        </div>
    </div>

    <?php
    // Admin Footer
    include __DIR__ . '/../includes/admin_footer.php';
    ?>

    <script src="/web-kasir-new/node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="/web-kasir-new/assets/js/main.js"></script>
</body>
</html>