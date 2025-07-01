<?php
require_once __DIR__ . '/../../config.php';
// session_start();

function sanitize_input($data) {
    return htmlentities(trim($data));
}

// Bagian untuk menangani Pencarian AJAX
if (!empty($_GET['cari_barang'])) {
    
    $cari = trim(strip_tags($_POST['keyword']));
    
    if ($cari != '') {
        $sql = "SELECT id, nama, harga FROM produk WHERE (id LIKE ? OR nama LIKE ?) AND stok > 0";
        $row = $db->prepare($sql);
        $param = "%{$cari}%";
        $row->execute([$param, $param]);
        $hasil_pencarian = $row->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-3">Nama Barang</th>
                    <th scope="col" class="px-4 py-3">Harga</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($hasil_pencarian)):
                foreach ($hasil_pencarian as $hasil): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white product-name">
                            <?php echo htmlspecialchars($hasil['nama']);?>
                        </td>
                        <td class="px-4 py-3 product-price" data-price="<?php echo $hasil['harga'];?>">
                            Rp.<?php echo number_format($hasil['harga']);?>,-
                        </td>
                        <td class="px-4 py-3">
                            <button type="button" 
                                    data-produk-id="<?php echo $hasil['id'];?>" 
                                    class="add-to-cart-btn font-medium text-blue-600 dark:text-blue-500 hover:underline">
                               + Tambah
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="bg-white dark:bg-gray-800">
                    <td colspan="3" class="px-4 py-3 text-center text-gray-500">Produk tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
    }
    exit;
}

//   LOGIKA UNTUK MENANGANI SUBMIT DARI FORM EDIT PRODUK
if (isset($_GET['produk'])) {
    $id        = (int)sanitize_input($_POST['id']);
    $nama      = sanitize_input($_POST['nama']);
    $harga     = sanitize_input($_POST['harga']);
    $stok      = (int)sanitize_input($_POST['stok']);
    $kategori  = sanitize_input($_POST['kategori']);
    $deskripsi = sanitize_input($_POST['deskripsi']);

    $data = [$nama, $kategori, $harga, $stok, $deskripsi, $id];
    $sql = "UPDATE produk SET nama=?, kategori=?, harga=?, stok=?, deskripsi=? WHERE id=?";
    
    $row = $db->prepare($sql);
    $row->execute($data);

    // Redirect dengan pesan sukses
    echo '<script>window.location="../../index.php?page=barang&success=edit-data";</script>';
    exit;
}

// --- Blok kode di bawah ini tidak akan terpengaruh dan tetap aman ---
// --- Blok ini akan dijalankan jika BUKAN permintaan pencarian AJAX ---

$user_id = 1;

// Aksi Update Kuantitas (dari form POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $keranjang_id = (int)$_POST['keranjang_id'];
    $new_qty = (int)$_POST['qty'];
    
    // Logika untuk update kuantitas di keranjang dan stok produk
    $stmt_old = $db->prepare("SELECT qty, produk_id FROM keranjang WHERE id = ?");
    $stmt_old->execute([$keranjang_id]);
    $item = $stmt_old->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $old_qty = $item['qty'];
        $produk_id = $item['produk_id'];
        
        $stmt_update_keranjang = $db->prepare("UPDATE keranjang SET qty = ? WHERE id = ?");
        $stmt_update_keranjang->execute([$new_qty, $keranjang_id]);
        
        $selisih_qty = $new_qty - $old_qty;
        $stmt_update_stok = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
        $stmt_update_stok->execute([$selisih_qty, $produk_id]);
    }
}

// Aksi Hapus Item (dari link GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete_item') {
    $keranjang_id = (int)$_GET['keranjang_id'];

    // Logika untuk hapus item dan kembalikan stok
    $stmt_info = $db->prepare("SELECT qty, produk_id FROM keranjang WHERE id = ?");
    $stmt_info->execute([$keranjang_id]);
    $item = $stmt_info->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $stmt_stok = $db->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
        $stmt_stok->execute([$item['qty'], $item['produk_id']]);
        
        $stmt_delete = $db->prepare("DELETE FROM keranjang WHERE id = ?");
        $stmt_delete->execute([$keranjang_id]);
    }
}

// Aksi Reset Keranjang (dari link GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'reset_cart') {
    // Logika untuk reset keranjang dan kembalikan semua stok
    $stmt_items = $db->prepare("SELECT qty, produk_id FROM keranjang WHERE user_id = ?");
    $stmt_items->execute([$user_id]);
    $items_to_reset = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items_to_reset as $item) {
        $stmt_stok = $db->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
        $stmt_stok->execute([$item['qty'], $item['produk_id']]);
    }
    
    $stmt_clear = $db->prepare("DELETE FROM keranjang WHERE user_id = ?");
    $stmt_clear->execute([$user_id]);
}

// Setelah semua jenis proses selesai, redirect kembali ke halaman penjualan
header('Location: ../../index.php?page=jual');
exit;
?>