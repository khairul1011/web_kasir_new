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
    // Path relatif dari home.php ke admin_navbar.php
    include __DIR__ . '/admin_navbar.php';
    ?>

    <?php
    // Sidebar utama
    // Path relatif dari home.php ke admin_sidebar.php
    include __DIR__ . '/admin_sidebar.php';
    ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
            <?php
            // Konten modul atau dashboard akan dimuat di sini
            // Perhatikan bahwa $content_view_file didefinisikan di router (index.php)
            // dan merupakan path RELATIF dari ADMIN_PATH.
            if (isset($content_view_file) && file_exists(ADMIN_PATH . '/' . $content_view_file)) {
                include ADMIN_PATH . '/' . $content_view_file;
            } else {
                echo '<p class="text-center text-gray-700 dark:text-gray-300">Konten tidak ditemukan atau sedang dalam pengembangan.</p>';
            }
            ?>
        </div>
    </div>

    <?php
    // Admin Footer
    // Path relatif dari home.php ke admin_footer.php
    include __DIR__ . '/admin_footer.php';
    ?>

    <script src="/web-kasir-new/node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="/web-kasir-new/assets/js/main.js"></script>
</body>
</html>