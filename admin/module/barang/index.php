<?php
/*
* File: modules/barang/index.php
* Deskripsi: Halaman pengelolaan produk untuk aplikasi kasir.
* Menampilkan daftar produk, serta menyediakan fitur tambah, edit, dan hapus.
*/

// Asumsi $db dan $view sudah tersedia dari file utama (misalnya index.php di root)
// yang meng-include config.php dan fungsi/view.php.
// Blok if ini adalah fallback jika modul diakses langsung atau $view belum terinisialisasi.

if (!isset($view) || !($view instanceof View)) {
    // Path ke config.php:
    // Dari C:\xampp\htdocs\web-kasir-new\admin\module\barang\index.php
    // Naik 3 level: barang/ -> module/ -> admin/ -> web-kasir-new/ (root)
    require_once __DIR__ . '/../../../config.php';

    // Path ke fungsi/view/view.php:
    // Dari C:\xampp\htdocs\web-kasir-new\admin\module\barang\index.php
    // Naik 2 level: barang/ -> module/ -> admin/
    // Lalu masuk ke fungsi/view/view.php
    require_once __DIR__ . '/../../fungsi/view/view.php';
    
    // Buat instance dari kelas View
    $view = new View($db);
}

// Ambil semua data produk
$daftarProduk = $view->produk();

// Ambil semua kategori unik untuk filter atau dropdown tambah/edit
$daftarKategori = $view->kategori();

// Cek pesan sukses/error dari operasi sebelumnya
$message = '';
$message_type = '';

if (isset($_GET['success'])) {
    $message_type = 'success';
    if ($_GET['success'] == 'tambah-data') {
        $message = 'Data produk berhasil ditambahkan!';
    } elseif ($_GET['success'] == 'edit-data') {
        $message = 'Data produk berhasil diperbarui!';
    }
} elseif (isset($_GET['remove'])) {
    $message_type = 'success';
    if ($_GET['remove'] == 'hapus-data') {
        $message = 'Data produk berhasil dihapus!';
    }
} elseif (isset($_GET['error'])) {
    $message_type = 'error';
    if ($_GET['error'] == 'gagal-tambah') {
        $message = 'Gagal menambahkan data produk. Silakan coba lagi.';
    } elseif ($_GET['error'] == 'gagal-edit') {
        $message = 'Gagal memperbarui data produk. Silakan coba lagi.';
    } elseif ($_GET['error'] == 'gagal-hapus') {
        $message = 'Gagal menghapus data produk. Mungkin produk ini terkait dengan transaksi.';
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Produk</h1>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-4 text-sm rounded-lg
        <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; ?>"
        role="alert">
        <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
    </div>
<?php endif; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 sm:mb-0">Daftar Produk</h2>
        <a href="index.php?page=produk-tambah" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Produk Baru
        </a>
    </div>

    <div class="overflow-x-auto">
        <?php if (!empty($daftarProduk)): ?>
            <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider rounded-tl-lg">ID Produk</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nama Produk</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Kategori</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Harga</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Stok</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($daftarProduk as $produk): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($produk['id']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($produk['nama']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($produk['kategori']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">Rp <?php echo number_format($produk['harga'], 2, ',', '.'); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($produk['stok']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm">
                                <a href="index.php?page=produk-edit&id=<?php echo htmlspecialchars($produk['id']); ?>" class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-md text-xs transition duration-200 mr-2">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Edit
                                </a>
                                <a href="fungsi/hapus/hapus.php?produk&id=<?php echo htmlspecialchars($produk['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md text-xs transition duration-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-600 dark:text-gray-400">Tidak ada produk yang tersedia.</p>
        <?php endif; ?>
    </div>
</div>
