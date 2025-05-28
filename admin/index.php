<?php
// POS-KASIR-PHP/admin/index.php

// Define base paths for easier includes and file existence checks
// __DIR__ akan merujuk pada direktori 'admin/'
define('ADMIN_ROOT_PATH', __DIR__);
define('MODULE_PATH', ADMIN_ROOT_PATH . '/module');
define('TEMPLATE_PATH', ADMIN_ROOT_PATH . '/template');
define('LAYOUT_PATH', TEMPLATE_PATH . '/layouts');
define('INCLUDE_PATH', TEMPLATE_PATH . '/includes');

// --- Routing Logika ---

// Ambil path dari URL setelah "/admin/"
// Contoh: Jika URL adalah http://localhost/POS-KASIR-PHP/admin/module/barang/edit?id=1
// $_SERVER['REQUEST_URI'] akan berisi '/POS-KASIR-PHP/admin/module/barang/edit?id=1'
// Kita perlu menghapus '/POS-KASIR-PHP/admin/' dari string tersebut
$request_uri = strtok($_SERVER['REQUEST_URI'], '?'); // Hapus query string
$base_uri_admin = '/POS-KASIR-PHP/admin/'; // Sesuaikan ini dengan path folder kamu di web server
                                            // Jika POS-KASIR-PHP langsung di htdocs, maka bisa '/admin/'

// Jika path diakhiri dengan '/', hapus untuk konsistensi
$path = trim(str_replace($base_uri_admin, '', $request_uri), '/');

// Default route jika tidak ada path yang spesifik setelah /admin/
if (empty($path)) {
    $path = 'module/dashboard/index'; // Asumsi ada modul 'dashboard'
}

// Pisahkan path menjadi segmen (misalnya "module", "barang", "index")
$segments = explode('/', $path);

$content_view_file = ''; // Path relatif dari ADMIN_ROOT_PATH ke file view
$title = 'POS Kasir Admin Panel'; // Judul default

// Logika untuk menentukan file konten yang akan dimuat
// Contoh: admin/module/barang/index.php atau admin/module/jual/tambah.php
if (isset($segments[0]) && $segments[0] === 'module' && isset($segments[1])) {
    $module_name = $segments[1]; // e.g., 'barang'
    $page_name = isset($segments[2]) && !empty($segments[2]) ? $segments[2] : 'index'; // e.g., 'index', 'details', 'edit'

    $content_view_file = 'module/' . $module_name . '/' . $page_name . '.php';

    // Set judul berdasarkan modul dan halaman
    $title = ucwords(str_replace('-', ' ', $module_name)) . ' - POS Kasir Admin';
    if ($page_name !== 'index') {
        $title = ucwords(str_replace('-', ' ', $page_name)) . ' ' . ucwords(str_replace('-', ' ', $module_name)) . ' - POS Kasir Admin';
    }

    // Periksa apakah file modul benar-benar ada
    if (!file_exists(ADMIN_ROOT_PATH . '/' . $content_view_file)) {
        // Jika file modul tidak ditemukan, fallback ke 404 atau dashboard
        header("HTTP/1.0 404 Not Found");
        $content_view_file = 'template/pages/404.php'; // Asumsi kamu punya file ini di template/pages/404.php
        $title = 'Halaman Tidak Ditemukan - POS Kasir Admin';
    }

} else {
    // Jika path tidak dimulai dengan 'module/', atau tidak valid, arahkan ke 404
    header("HTTP/1.0 404 Not Found");
    $content_view_file = 'template/pages/404.php'; // Asumsi kamu punya file ini
    $title = 'Halaman Tidak Ditemukan - POS Kasir Admin';
}

// --- Memuat Layout Utama Admin ---
// File layout utama akan menyertakan konten berdasarkan $content_view_file
include LAYOUT_PATH . '/admin_main.php';

// --- Catatan tentang folder `fungsi/` ---
// Folder `fungsi/` berisi skrip-skrip yang mungkin melakukan operasi CRUD (edit.php, hapus.php, tambah.php).
// Skrip-skrip ini *tidak boleh* dipanggil langsung melalui router ini.
// Mereka seharusnya dipanggil sebagai:
// 1. Target dari form HTML (action="path/ke/fungsi/tambah.php")
// 2. Link langsung (href="path/ke/fungsi/hapus/hapus.php?id=...")
// 3. Atau lebih baik lagi, logika ini diintegrasikan ke dalam file modul (e.g., barang/edit.php)
//    di mana setelah proses, dilakukan redirect kembali ke halaman modul.
//
// Untuk `fungsi/`, kamu perlu memastikan web server bisa mengaksesnya.
// Jika mereka di luar `public/`, kamu harus berhati-hati dengan akses langsung.
// Cara paling aman adalah:
//    a. Pindahkan `fungsi/` ke dalam `public/` (kurang disarankan untuk keamanan).
//    b. Buatlah sebuah "proxy" PHP di `public/` yang kemudian meng-include file dari `fungsi/`.
//    c. Yang terbaik: Integrasikan logika CRUD ke dalam file modul itu sendiri (misalnya, `barang/edit.php`
//       akan menampilkan form DAN memproses POST request untuk edit). Ini membuat modul lebih mandiri.
?>