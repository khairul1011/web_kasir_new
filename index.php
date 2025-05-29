<?php
// web-kasir-new/index.php

session_start(); // Pastikan session dimulai

// Definisikan path dasar
define('APP_ROOT_PATH', __DIR__);
define('ADMIN_PATH', APP_ROOT_PATH . '/admin');
define('MODULE_PATH', ADMIN_PATH . '/module');
define('INCLUDE_PATH', ADMIN_PATH . '/template/includes');

// Mengabaikan logika login dan langsung memuat halaman admin
// require 'config.php'; // Uncomment ini jika Anda punya config.php di root

// Sertakan layout utama admin (home.php)
include INCLUDE_PATH . '/home.php';

?>