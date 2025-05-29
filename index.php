<?php
// web-kasir-new/index.php (Sekarang sebagai router utama)

// Definisikan path dasar untuk memudahkan include dan pengecekan keberadaan file
// __DIR__ akan merujuk pada direktori 'web-kasir-new/'
define('APP_ROOT_PATH', __DIR__); // Root aplikasi keseluruhan
define('ADMIN_PATH', APP_ROOT_PATH . '/admin'); // Direktori admin
define('MODULE_PATH', ADMIN_PATH . '/module');
define('TEMPLATE_PATH', ADMIN_PATH . '/template');
define('LAYOUT_PATH', TEMPLATE_PATH . '/layouts');
define('INCLUDE_PATH', TEMPLATE_PATH . '/includes');
define('HOME_PATH', INCLUDE_PATH . '/home.php'); // Path ke layout utama admin (sekarang home.php)

// --- Logika Routing ---

// Ambil path dari URL setelah base URL aplikasi Anda
// Contoh: Jika URL adalah http://localhost/web-kasir-new/admin/module/barang/edit?id=1
// $_SERVER['REQUEST_URI'] akan berisi '/web-kasir-new/admin/module/barang/edit?id=1'
$request_uri = strtok($_SERVER['REQUEST_URI'], '?'); // Hapus query string
$base_uri_app = '/web-kasir-new/'; // Sesuaikan ini dengan path folder kamu di web server
                                     // Jika web-kasir-new langsung di htdocs, maka bisa '/'

// Hapus base URI dari request URI untuk mendapatkan path yang relevan
$path = trim(str_replace($base_uri_app, '', $request_uri), '/');

$content_view_file = ''; // Path relatif dari ADMIN_PATH ke file view untuk admin panel
$title = 'POS Kasir'; // Judul default aplikasi

// Pisahkan path menjadi segmen (misalnya "admin", "module", "dashboard")
$segments = explode('/', $path);

// Logika untuk menentukan file konten yang akan dimuat
if (empty($path) || $path === 'admin' || $path === 'admin/index') {
    // Jika tidak ada path, atau hanya '/admin', atau '/admin/index', arahkan ke dashboard admin (Manajemen Barang)
    $content_view_file = 'module/barang/index.php'; // Konten dari admin/module/barang/index.php
    $title = 'Dashboard Admin - POS Kasir';
} elseif (isset($segments[0]) && $segments[0] === 'admin') {
    // Jika path dimulai dengan 'admin/', ini adalah rute untuk panel admin
    // Hapus segmen 'admin' dari path untuk mendapatkan path modul yang sebenarnya
    array_shift($segments);
    $admin_path_remaining = implode('/', $segments);

    // Default ke dashboard jika tidak ada modul yang ditentukan setelah /admin/
    if (empty($admin_path_remaining)) {
        $content_view_file = 'module/barang/index.php';
        $title = 'Dashboard Admin - POS Kasir';
    } elseif (isset($segments[0]) && $segments[0] === 'module' && isset($segments[1])) {
        $module_name = $segments[1]; // e.g., 'barang'
        $page_name = isset($segments[2]) && !empty($segments[2]) ? $segments[2] : 'index'; // e.g., 'index', 'details', 'edit'

        $content_view_file = 'module/' . $module_name . '/' . $page_name . '.php';

        // Set judul berdasarkan modul dan halaman
        $title = ucwords(str_replace('-', ' ', $module_name)) . ' - POS Kasir Admin';
        if ($page_name !== 'index') {
            $title = ucwords(str_replace('-', ' ', $page_name)) . ' ' . ucwords(str_replace('-', ' ', $module_name)) . ' - POS Kasir Admin';
        }

        // Periksa apakah file modul benar-benar ada di dalam folder admin
        if (!file_exists(ADMIN_PATH . '/' . $content_view_file)) {
            // Jika file modul tidak ditemukan, fallback ke 404
            header("HTTP/1.0 404 Not Found");
            $content_view_file = 'template/pages/404.php'; // Asumsi ada file ini di admin/template/pages/404.php
            $title = 'Halaman Tidak Ditemukan - POS Kasir Admin';
        }
    } else {
        // Jika path admin tidak valid, arahkan ke 404
        header("HTTP/1.0 404 Not Found");
        $content_view_file = 'template/pages/404.php';
        $title = 'Halaman Tidak Ditemukan - POS Kasir Admin';
    }
} else {
    // Ini adalah rute untuk bagian front-end (jika ada, atau fallback 404)
    // Untuk saat ini, kita bisa arahkan ke 404 jika bukan rute admin
    header("HTTP/1.0 404 Not Found");
    $content_view_file = 'admin/template/pages/404.php'; // Menggunakan 404 dari admin
    $title = 'Halaman Tidak Ditemukan - POS Kasir';
}

// --- Memuat Layout Utama Admin (home.php) ---
// File layout utama akan menyertakan konten berdasarkan $content_view_file
include HOME_PATH;

// Catatan: Pastikan bahwa file-file yang di-include seperti admin_navbar.php, admin_sidebar.php,
// dan admin_footer.php di dalam home.php menggunakan path yang benar relatif terhadap home.php,
// atau path absolut yang dimulai dari APP_ROOT_PATH.
?>