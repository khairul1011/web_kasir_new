<?php
// web-kasir-new/index.php

// Mulai output buffering dan sesi di paling atas
ob_start();
// session_start();

// Definisikan path dasar (sudah benar)
define('APP_ROOT_PATH', __DIR__);
define('ADMIN_PATH', APP_ROOT_PATH . '/admin');
define('MODULE_PATH', ADMIN_PATH . '/module');
define('INCLUDE_PATH', ADMIN_PATH . '/template/includes');
define('BASE_URL', '/web-kasir-new');

// Sertakan config.php
require_once APP_ROOT_PATH . '/config.php';

// Sertakan kelas View (sudah benar)
$view_file_path = APP_ROOT_PATH . '/fungsi/view/view.php';
require_once $view_file_path;

// Buat instance dari kelas View (sudah benar)
$view = new View($db);


if (isset($_GET['page']) && $_GET['page'] == 'laporan' && isset($_GET['export']) && $_GET['export'] == 'true') {
    $view->exportLaporanToExcel($_GET);
}

include INCLUDE_PATH . '/home.php';

// Kirim semua output yang sudah ditahan ke browser
ob_end_flush();
