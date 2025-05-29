<?php
if (!defined('APP_ROOT_PATH')) {
    define('APP_ROOT_PATH', dirname(dirname(dirname(__DIR__))));
    define('ADMIN_PATH', APP_ROOT_PATH . '/admin');
    define('MODULE_PATH', ADMIN_PATH . '/module');
    define('INCLUDE_PATH', ADMIN_PATH . '/template/includes');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'POS Kasir Admin'; ?></title>
    <link href="/web-kasir-new/assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">