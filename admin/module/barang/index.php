<?php
/*
* File: modules/barang/index.php
* Deskripsi: Halaman pengelolaan produk untuk aplikasi kasir.
* Menampilkan daftar produk, serta menyediakan fitur tambah, edit, dan hapus.
* Menambahkan modal untuk fitur tambah produk, pagination, dan fungsi pencarian OTOMATIS.
* File ini berfungsi sebagai halaman utama dan juga endpoint AJAX (mengembalikan HTML penuh).
*/

// Asumsi $db dan $view sudah tersedia dari file utama (misalnya index.php)
// yang meng-include config.php dan fungsi/view.php

// Pastikan $view object sudah diinisialisasi
if (!isset($view) || !($view instanceof View)) {
    // Fallback inisialisasi jika modul diakses langsung
    require_once __DIR__ . '/../../../config.php';
    require_once __DIR__ . '/../../../fungsi/view/view.php';
    $view = new View($db);
}

// --- LOGIKA PENCARIAN & PAGINATION START ---
$cari_keyword = isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : null; // Ambil kata kunci pencarian

$batas = 10; // Jumlah produk per halaman
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1; // Halaman saat ini, default ke 1

// Pastikan halaman tidak kurang dari 1
if ($halaman < 1) {
    $halaman = 1;
}

$halaman_awal = ($halaman > 0) ? ($halaman * $batas) - $batas : 0; // Offset

// Ambil total jumlah produk berdasarkan pencarian
$jumlah_data = $view->produk_row_count_total_with_search($cari_keyword);
$total_halaman = ceil($jumlah_data / $batas); // Hitung total halaman

// Pastikan halaman tidak melebihi total halaman (jika ada produk)
if ($halaman > $total_halaman && $total_halaman > 0) {
    $halaman = $total_halaman;
    $halaman_awal = ($halaman - 1) * $batas; // Sesuaikan offset
} elseif ($total_halaman == 0) {
    $halaman = 0; // Tidak ada halaman jika tidak ada produk
    $halaman_awal = 0;
}

$previous = $halaman - 1;
$next = $halaman + 1;

// Ambil data produk untuk halaman saat ini, termasuk pencarian
$daftarProduk = $view->produk_pagination_with_search($batas, $halaman_awal, $cari_keyword);

// Inisialisasi nomor urut awal untuk tabel
$nomor = $halaman_awal + 1; 
// --- LOGIKA PENCARIAN & PAGINATION END ---


// Ambil semua kategori unik untuk dropdown di modal
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
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <form id="searchForm" method="GET" action="index.php" class="relative w-full sm:w-64">
                <input type="hidden" name="page" value="barang">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input type="search" name="cari" id="searchInput" class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Cari produk..." value="<?php echo htmlspecialchars($cari_keyword ?? ''); ?>" />
                </form>

            <button data-modal-target="crud-modal" data-modal-toggle="crud-modal" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition duration-200 w-full sm:w-auto" type="button">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Produk Baru
            </button>
        </div>
    </div>

    <div id="productTableContainer" class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider rounded-tl-lg">No.</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">ID Produk</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nama Produk</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Kategori</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Harga</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Stok</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody id="productTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($daftarProduk)): ?>
                    <?php foreach ($daftarProduk as $produk): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"><?php echo $nomor++; ?></td>
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
                                <a href="<?php echo APP_ROOT_PATH; ?>/fungsi/hapus/hapus.php?produk&id=<?php echo htmlspecialchars($produk['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md text-xs transition duration-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">Tidak ada produk yang tersedia.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation" class="flex justify-center mt-6" id="paginationNav">
        <ul class="flex items-center -space-x-px h-8 text-sm">
            <li>
                <a href="#" data-page="<?php echo max(1, $previous); ?>" class="pagination-link flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white
                    <?php echo ($halaman <= 1) ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
                    <span class="sr-only">Previous</span>
                    <svg class="w-2.5 h-2.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                    </svg>
                </a>
            </li>

            <?php for ($x = 1; $x <= $total_halaman; $x++): ?>
            <li>
                <a href="#" data-page="<?php echo $x; ?>" class="pagination-link flex items-center justify-center px-3 h-8 leading-tight border border-gray-300
                    <?php echo ($x === $halaman) ? 'z-10 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'; ?>">
                    <?php echo $x; ?>
                </a>
            </li>
            <?php endfor; ?>

            <li>
                <a href="#" data-page="<?php echo min($total_halaman, $next); ?>" class="pagination-link flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white
                    <?php echo ($halaman >= $total_halaman || $total_halaman == 0) ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
                    <span class="sr-only">Next</span>
                    <svg class="w-2.5 h-2.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                </a>
            </li>
        </ul>
    </nav>


    <div id="crud-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-auto max-h-[90vh]">
        <div class="relative p-4 w-full max-w-md h-auto">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Tambah Produk Baru
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="crud-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <form class="p-4 md:p-5" method="POST" action="/web-kasir-new/fungsi/tambah/tambah.php?produk">
                    <div class="grid gap-4 mb-4 grid-cols-2">
                        <div class="col-span-2">
                            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Produk</label>
                            <input type="text" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Masukkan nama produk" required="">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="harga" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga</label>
                            <input type="number" name="harga" id="harga" step="0.01" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Contoh: 2999.00" required="">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="stok" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Stok</label>
                            <input type="number" name="stok" id="stok" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Jumlah stok" required="">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="kategori" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kategori</label>
                            <select name="kategori" id="kategori" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($daftarKategori as $kategori): ?>
                                    <option value="<?php echo htmlspecialchars($kategori['kategori']); ?>">
                                        <?php echo htmlspecialchars($kategori['kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label for="deskripsi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi Produk</label>
                            <textarea name="deskripsi" id="deskripsi" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Tulis deskripsi produk di sini"></textarea>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="outlet_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ID Outlet (Opsional)</label>
                            <input type="number" name="outlet_id" id="outlet_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="ID Outlet">
                        </div>
                    </div>
                    <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        Tambah Produk
                    </button>
                </form>

            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const productTableBody = document.getElementById('productTableBody');
    const paginationNav = document.getElementById('paginationNav');
    const searchForm = document.getElementById('searchForm'); 
    let searchTimeout;

    // Fungsi untuk mengambil dan memperbarui produk via AJAX
    function fetchProducts(page = 1, searchQuery = '') {
        // Tampilkan indikator loading (opsional)
        productTableBody.innerHTML = '<tr><td colspan="7" class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">Memuat data...</td></tr>';
        paginationNav.innerHTML = ''; // Kosongkan pagination saat loading

        // Bangun URL untuk permintaan AJAX
        // Tetap arahkan ke halaman ini sendiri (index.php?page=barang)
        const url = `index.php?page=barang&halaman=${page}${searchQuery ? '&cari=' + encodeURIComponent(searchQuery) : ''}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Penting: Memberi tahu PHP ini adalah permintaan AJAX
            }
        })
        .then(response => {
            // Ubah ini dari .json() menjadi .text() karena responsnya sekarang HTML
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text(); // Harapkan respons HTML
        })
        .then(html => {
            // Buat elemen DOM sementara untuk mem-parse HTML yang diterima
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Ekstrak tbody dan pagination dari dokumen sementara
            const newProductTableBody = doc.getElementById('productTableBody');
            const newPaginationNav = doc.getElementById('paginationNav');

            if (newProductTableBody && newPaginationNav) {
                productTableBody.innerHTML = newProductTableBody.innerHTML;
                paginationNav.innerHTML = newPaginationNav.innerHTML;
            } else {
                // Fallback jika elemen tidak ditemukan dalam respons HTML
                productTableBody.innerHTML = '<tr><td colspan="7" class="py-3 px-4 text-center text-red-600 dark:text-red-400">Gagal mengekstrak data dari respons.</td></tr>';
                paginationNav.innerHTML = '';
            }
            
            // Pasang kembali event listener untuk tautan pagination yang baru
            attachPaginationListeners();
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productTableBody.innerHTML = '<tr><td colspan="7" class="py-3 px-4 text-center text-red-600 dark:text-red-400">Gagal memuat data produk.</td></tr>';
            paginationNav.innerHTML = '';
        });
    }

    // Fungsi untuk memasang event listener ke tautan pagination
    function attachPaginationListeners() {
        const paginationLinks = paginationNav.querySelectorAll('.pagination-link');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Mencegah perilaku default tautan (reload halaman penuh)
                const page = this.dataset.page;
                const currentSearchQuery = searchInput.value;
                fetchProducts(page, currentSearchQuery);
            });
        });
    }

    // Event listener untuk input pencarian (debounce)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout); // Hapus timeout sebelumnya
            searchTimeout = setTimeout(() => {
                const query = this.value;
                fetchProducts(1, query); // Selalu kembali ke halaman 1 saat pencarian baru
            }, 500); // Waktu debounce: 500ms (setengah detik)
        });
    }

    // Event listener untuk form submission (mencegah reload halaman saat Enter ditekan)
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah perilaku default form submission
            // Karena sudah ada event 'input' dengan debounce, kita tidak perlu memanggil fetchProducts di sini
            // Cukup pastikan form tidak di-submit secara normal
        });
    }

    // Pasang listener pada saat halaman dimuat pertama kali (untuk tautan pagination yang sudah ada)
    attachPaginationListeners();
});
</script>
