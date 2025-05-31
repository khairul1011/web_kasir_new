<?php
/*
* File: modules/barang/get_products_ajax.php
* Deskripsi: Endpoint backend untuk mengambil data produk secara asinkron (AJAX).
* Mengembalikan data produk dan informasi pagination dalam format JSON.
* File ini tidak meng-include header/footer HTML.
*/

// Pastikan tidak ada output sebelum JSON
header('Content-Type: application/json');

// Memanggil file konfigurasi database (path dari get_products_ajax.php)
require_once __DIR__ . '/../../../config.php';
// Memanggil kelas View (path dari get_products_ajax.php)
require_once __DIR__ . '/../../../fungsi/view/view.php';

// Buat instance dari kelas View
$view = new View($db);

// --- LOGIKA PENCARIAN & PAGINATION ---
$cari_keyword = isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : null;
$batas = 10; // Jumlah produk per halaman
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;

if ($halaman < 1) {
    $halaman = 1;
}

$halaman_awal = ($halaman > 0) ? ($halaman * $batas) - $batas : 0;

$jumlah_data = $view->produk_row_count_total_with_search($cari_keyword);
$total_halaman = ceil($jumlah_data / $batas);

if ($halaman > $total_halaman && $total_halaman > 0) {
    $halaman = $total_halaman;
    $halaman_awal = ($halaman - 1) * $batas;
} elseif ($total_halaman == 0) {
    $halaman = 0;
    $halaman_awal = 0;
}

$previous = $halaman - 1;
$next = $halaman + 1;

$daftarProduk = $view->produk_pagination_with_search($batas, $halaman_awal, $cari_keyword);
$nomor_urut_awal = $halaman_awal + 1;

// Siapkan HTML untuk tbody
$tbody_html = '';
if (!empty($daftarProduk)) {
    foreach ($daftarProduk as $index => $produk) {
        $nomor = $nomor_urut_awal + $index; // Penomoran yang benar untuk halaman AJAX
        $tbody_html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">' . $nomor . '</td>';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">' . htmlspecialchars($produk['id']) . '</td>';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">' . htmlspecialchars($produk['nama']) . '</td>';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">' . htmlspecialchars($produk['kategori']) . '</td>';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">Rp ' . number_format($produk['harga'], 2, ',', '.') . '</td>';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">' . htmlspecialchars($produk['stok']) . '</td>';
        $tbody_html .= '<td class="py-3 px-4 whitespace-nowrap text-sm">';
        $tbody_html .= '    <a href="index.php?page=produk-edit&id=' . htmlspecialchars($produk['id']) . '" class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-md text-xs transition duration-200 mr-2">';
        $tbody_html .= '        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>';
        $tbody_html .= '        Edit';
        $tbody_html .= '    </a>';
        // Menggunakan URL absolut dari root web untuk link hapus
        $hapus_url_web = '/web-kasir-new/fungsi/hapus/hapus.php?produk&id=' . htmlspecialchars($produk['id']);
        $tbody_html .= '    <a href="' . $hapus_url_web . '" onclick="return confirm(\'Apakah Anda yakin ingin menghapus produk ini?\');" class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md text-xs transition duration-200">';
        $tbody_html .= '        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
        $tbody_html .= '        Hapus';
        $tbody_html .= '    </a>';
        $tbody_html .= '</td>';
        $tbody_html .= '</tr>';
    }
} else {
    $tbody_html = '<tr><td colspan="7" class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">Tidak ada produk yang tersedia.</td></tr>';
}

// Siapkan HTML untuk navigasi pagination
$pagination_html = '<ul class="flex items-center -space-x-px h-8 text-sm">';
// Previous Button
$pagination_html .= '<li>';
// Gunakan data-page untuk JS, href="#" untuk mencegah reload
$pagination_html .= '<a href="#" data-page="' . max(1, $previous) . '" class="pagination-link flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white ' . (($halaman <= 1) ? 'opacity-50 cursor-not-allowed pointer-events-none' : '') . '">';
$pagination_html .= '<span class="sr-only">Previous</span>';
$pagination_html .= '<svg class="w-2.5 h-2.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/></svg>';
$pagination_html .= '</a>';
$pagination_html .= '</li>';

// Page Numbers
for ($x = 1; $x <= $total_halaman; $x++) {
    $pagination_html .= '<li>';
    $pagination_html .= '<a href="#" data-page="' . $x . '" class="pagination-link flex items-center justify-center px-3 h-8 leading-tight border border-gray-300 ' . (($x === $halaman) ? 'z-10 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white') . '">';
    $pagination_html .= $x;
    $pagination_html .= '</a>';
    $pagination_html .= '</li>';
}

// Next Button
$pagination_html .= '<li>';
$pagination_html .= '<a href="#" data-page="' . min($total_halaman, $next) . '" class="pagination-link flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white ' . (($halaman >= $total_halaman || $total_halaman == 0) ? 'opacity-50 cursor-not-allowed pointer-events-none' : '') . '">';
$pagination_html .= '<span class="sr-only">Next</span>';
$pagination_html .= '<svg class="w-2.5 h-2.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>';
$pagination_html .= '</a>';
$pagination_html .= '</li>';
$pagination_html .= '</ul>';
