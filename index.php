<?php
// web-kasir-new/index.php

// session_start(); // Baris ini di-komentar sesuai permintaan

// Definisikan path dasar
define('APP_ROOT_PATH', __DIR__);
define('ADMIN_PATH', APP_ROOT_PATH . '/admin');
define('MODULE_PATH', ADMIN_PATH . '/module');
define('INCLUDE_PATH', ADMIN_PATH . '/template/includes');

define('BASE_URL', '/web-kasir-new'); // Sesuaikan dengan folder proyek Anda

// Sertakan config.php (yang berisi koneksi database $db)
// Path: dari APP_ROOT_PATH (yaitu web-kasir-new/) ke config.php (di root)
require_once APP_ROOT_PATH . '/config.php';

// Sertakan kelas View
// Path: dari APP_ROOT_PATH (yaitu web-kasir-new/) ke fungsi/view/view.php
// INI ADALAH BARIS KRITIS YANG HARUS SESUAI DENGAN LOKASI ASLI FOLDER FUNGSI
$view_file_path = APP_ROOT_PATH . '/fungsi/view/view.php';

// --- BARIS DEBUGGING ---
// Akan menampilkan path yang sedang dicoba oleh PHP.
// JANGAN HAPUS BARIS INI DULU. Kita perlu melihat outputnya.
echo "";
// --- AKHIR BARIS DEBUGGING ---

require_once $view_file_path;

// Buat instance dari kelas View
// Variabel $db datang dari config.php
$view = new View($db);

// Sertakan layout utama admin (home.php)
// INCLUDE_PATH sudah didefinisikan sebelumnya dan mengarah ke admin/template/includes/
include INCLUDE_PATH . '/home.php';

