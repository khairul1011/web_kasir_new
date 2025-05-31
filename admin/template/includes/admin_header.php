    <?php
    // admin/template/includes/admin_header.php
    // Definisi path APP_ROOT_PATH, ADMIN_PATH, MODULE_PATH, INCLUDE_PATH
    // seharusnya sudah dilakukan di index.php utama.
    // Tidak perlu mendefinisikan ulang di sini.
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title ?? 'POS Kasir Admin'; ?></title>
        <link href="/web-kasir-new/assets/css/style.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <style>
            /* Custom styles for Inter font */
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">
    