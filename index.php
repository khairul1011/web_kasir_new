<?php
ob_start();
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

define('APP_ROOT_PATH', __DIR__);
define('ADMIN_PATH', APP_ROOT_PATH . '/admin');
define('MODULE_PATH', ADMIN_PATH . '/module');
define('INCLUDE_PATH', ADMIN_PATH . '/template/includes');
define('BASE_URL', '/web-kasir-new');

require_once APP_ROOT_PATH . '/config.php';

//Kelas View
$view_file_path = APP_ROOT_PATH . '/fungsi/view/view.php';
require_once $view_file_path;

//Instance dari kelas View
$view = new View($db);

if (isset($_GET['page']) && $_GET['page'] == 'laporan' && isset($_GET['export']) && $_GET['export'] == 'true') {
    $view->exportLaporanToExcel($_GET);
}

include INCLUDE_PATH . '/home.php';

ob_end_flush();
