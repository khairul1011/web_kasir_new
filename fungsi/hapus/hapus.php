<?php
/*
* File: fungsi/hapus/hapus.php
* Deskripsi: Menangani semua aksi penghapusan data.
* 1. Hapus Produk (dari halaman barang)
* 2. Hapus Item dari Keranjang (dari halaman jual - jika menggunakan keranjang DB)
*/

require_once __DIR__ . '/../../config.php';
session_start();

// --- HAPUS PRODUK HALAMAN BARANG) ---
if (isset($_GET['produk']) && !empty($_GET['id'])) {

    $produk_id = (int)$_GET['id'];

    try {
        // Hapus produk dari database berdasarkan ID
        $sql = 'DELETE FROM produk WHERE id = ?';
        $row = $db->prepare($sql);
        $row->execute([$produk_id]);

        // Set pesan sukses di session untuk ditampilkan di halaman produk
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Data produk berhasil dihapus!'
        ];
    } catch (PDOException $e) {
        // Jika terjadi error (misalnya karena produk terkait dengan transaksi),
        // tangkap error tersebut dan kirim pesan gagal.
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus produk. Mungkin produk ini sudah pernah ada dalam transaksi.'
        ];
    }

    // Arahkan kembali ke halaman manajemen barang
    header('Location: ../../index.php?page=barang');
    exit;
}


// =================================================================
// --- KODE LAMA ANDA UNTUK KERANJANG - TETAP AMAN DI SINI ---
// --- Ini tidak akan berjalan kecuali dipanggil dari form lain ---
// =================================================================

class HapusController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function deleteItem($postData)
    {
        $keranjang_id = (int)$postData['keranjang_id'];

        $stmt_info = $this->db->prepare("SELECT qty, produk_id FROM keranjang WHERE id = ?");
        $stmt_info->execute([$keranjang_id]);
        $item = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $stmt_stok = $this->db->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
            $stmt_stok->execute([$item['qty'], $item['produk_id']]);
            $stmt_delete = $this->db->prepare("DELETE FROM keranjang WHERE id = ?");
            $stmt_delete->execute([$keranjang_id]);
        }
    }

    public function resetCart()
    {
        $user_id = 1; // Sesuaikan dengan sesi login Anda
        $stmt_items = $this->db->prepare("SELECT qty, produk_id FROM keranjang WHERE user_id = ?");
        $stmt_items->execute([$user_id]);
        $items_to_reset = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items_to_reset as $item) {
            $stmt_stok = $this->db->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?");
            $stmt_stok->execute([$item['qty'], $item['produk_id']]);
        }

        $stmt_clear = $this->db->prepare("DELETE FROM keranjang WHERE user_id = ?");
        $stmt_clear->execute([$user_id]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new HapusController($db);
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'delete_item') {
        $controller->deleteItem($_POST);
    } elseif ($action === 'reset_cart') {
        $controller->resetCart();
    }

    header('Location: ../../index.php?page=jual');
    exit;
}

// Jika tidak ada aksi yang cocok, redirect ke halaman utama
header('Location: ../../index.php');
exit;
