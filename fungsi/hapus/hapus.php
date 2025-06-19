<?php
/*
* File: fungsi/hapus/hapus.php
* Deskripsi: Menangani proses penghapusan data dari database untuk aplikasi kasir.
* Termasuk penghapusan produk, item keranjang, dan data transaksi/laporan.
*/

// Memanggil file konfigurasi database
require_once __DIR__ . '/../../config.php'; // Sesuaikan path jika struktur folder berbeda

// Fungsi untuk membersihkan dan mengamankan input
function sanitize_input($data) {
    return htmlentities(trim($data));
}

// --- Hapus Produk (sebelumnya 'barang') ---
if (isset($_GET['produk'])) { // Menggunakan 'produk' sebagai parameter GET
    $produk_id = (int)sanitize_input($_GET['id']); // ID produk yang akan dihapus

    // Data yang akan dihapus
    $data_produk = [$produk_id];

    try {
        // Cek apakah produk ini ada di detail_transaksi atau keranjang
        // Jika ada, Anda mungkin ingin mencegah penghapusan atau menghapus entri terkait terlebih dahulu.
        // Untuk saat ini, kita akan langsung mencoba menghapus. Jika ada foreign key constraint, ini akan gagal.

        // Hapus produk dari tabel 'produk'
        $sql = 'DELETE FROM produk WHERE id=?';
        $row = $db->prepare($sql);
        $row->execute($data_produk);

        echo '<script>window.location="../../index.php?page=produk&remove=hapus-data"</script>';
    } catch (PDOException $e) {
        error_log("Error deleting product: " . $e->getMessage());
        echo '<script>alert("Gagal menghapus produk. Mungkin produk ini terkait dengan transaksi atau keranjang yang ada. (' . $e->getMessage() . ')");window.location="../../index.php?page=produk&error=gagal-hapus"</script>';
    }
}

// --- Hapus Item dari Keranjang (sebelumnya 'jual') ---
if (isset($_GET['keranjang_item'])) { // Menggunakan 'keranjang_item' sebagai parameter GET
    $keranjang_item_id = (int)sanitize_input($_GET['id']); // ID item di tabel keranjang

    // Ambil produk_id dan qty dari item keranjang yang akan dihapus
    $sql_get_item_info = 'SELECT produk_id, qty FROM keranjang WHERE id=?';
    $row_get_item_info = $db->prepare($sql_get_item_info);
    $row_get_item_info->execute(array($keranjang_item_id));
    $item_info = $row_get_item_info->fetch();

    if ($item_info) {
        $produk_id_terkait = $item_info['produk_id'];
        $qty_dikembalikan = $item_info['qty'];

        try {
            // Hapus item dari tabel 'keranjang'
            $sql_delete_keranjang = 'DELETE FROM keranjang WHERE id=?';
            $row_delete_keranjang = $db->prepare($sql_delete_keranjang);
            $row_delete_keranjang->execute(array($keranjang_item_id));

            // Kembalikan stok produk
            $sql_update_stok = 'UPDATE produk SET stok = stok + ? WHERE id=?';
            $row_update_stok = $db->prepare($sql_update_stok);
            $row_update_stok->execute(array($qty_dikembalikan, $produk_id_terkait));

            echo '<script>window.location="../../index.php?page=jual"</script>';
        } catch (PDOException $e) {
            error_log("Error deleting cart item: " . $e->getMessage());
            echo '<script>alert("Gagal menghapus item dari keranjang: ' . $e->getMessage() . '");window.location="../../index.php?page=jual"</script>';
        }
    } else {
        echo '<script>alert("Item keranjang tidak ditemukan!");window.location="../../index.php?page=jual"</script>';
    }
}

// --- Hapus Semua Item di Keranjang (sebelumnya 'penjualan') ---
if (isset($_GET['clear_keranjang'])) { // Menggunakan 'clear_keranjang' sebagai parameter GET
    try {
        // Ambil semua item di keranjang untuk mengembalikan stok
        $sql_get_all_keranjang = 'SELECT produk_id, qty FROM keranjang';
        $row_get_all_keranjang = $db->prepare($sql_get_all_keranjang);
        $row_get_all_keranjang->execute();
        $all_items = $row_get_all_keranjang->fetchAll();

        foreach ($all_items as $item) {
            $sql_update_stok = 'UPDATE produk SET stok = stok + ? WHERE id=?';
            $row_update_stok = $db->prepare($sql_update_stok);
            $row_update_stok->execute(array($item['qty'], $item['produk_id']));
        }

        // Hapus semua data dari tabel 'keranjang'
        $sql = 'DELETE FROM keranjang';
        $row = $db->prepare($sql);
        $row->execute();

        echo '<script>window.location="../../index.php?page=jual"</script>';
    } catch (PDOException $e) {
        error_log("Error clearing cart: " . $e->getMessage());
        echo '<script>alert("Gagal mengosongkan keranjang: ' . $e->getMessage() . '");window.location="../../index.php?page=jual"</script>';
    }
}

// --- Hapus Semua Data Laporan/Transaksi (sebelumnya 'laporan') ---
if (isset($_GET['clear_laporan'])) { // Menggunakan 'clear_laporan' sebagai parameter GET
    try {
        // Mulai transaksi untuk memastikan konsistensi data
        $db->beginTransaction();

        // Hapus semua data dari tabel 'detail_transaksi' terlebih dahulu
        // Karena ada foreign key constraint dari detail_transaksi ke transaksi
        $sql_delete_detail = 'DELETE FROM detail_transaksi';
        $row_delete_detail = $db->prepare($sql_delete_detail);
        $row_delete_detail->execute();

        // Hapus semua data dari tabel 'transaksi'
        $sql_delete_transaksi = 'DELETE FROM transaksi';
        $row_delete_transaksi = $db->prepare($sql_delete_transaksi);
        $row_delete_transaksi->execute();

        // Commit transaksi
        $db->commit();

        echo '<script>window.location="../../index.php?page=laporan&remove=hapus"</script>';
    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi error
        $db->rollBack();
        error_log("Error clearing reports/transactions: " . $e->getMessage());
        echo '<script>alert("Gagal menghapus laporan/transaksi: ' . $e->getMessage() . '");window.location="../../index.php?page=laporan&error=gagal-hapus-laporan"</script>';
    }
}

// --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI ---

// Hapus Item dari Keranjang (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['keranjang_item'])) {
    $keranjang_item_id = (int)sanitize_input($_GET['id']); // ID item di tabel keranjang

    // Ambil produk_id dan qty dari item keranjang yang akan dihapus
    $sql_get_item_info = 'SELECT produk_id, qty FROM keranjang WHERE id=?';
    $row_get_item_info = $db->prepare($sql_get_item_info);
    $row_get_item_info->execute(array($keranjang_item_id));
    $item_info = $row_get_item_info->fetch();

    if ($item_info) {
        $produk_id_terkait = $item_info['produk_id'];
        $qty_dikembalikan = $item_info['qty'];

        try {
            $db->beginTransaction(); // Mulai transaksi database

            // Hapus item dari tabel 'keranjang'
            $sql_delete_keranjang = 'DELETE FROM keranjang WHERE id=?';
            $row_delete_keranjang = $db->prepare($sql_delete_keranjang);
            $row_delete_keranjang->execute(array($keranjang_item_id));

            // Kembalikan stok produk
            $sql_update_stok = 'UPDATE produk SET stok = stok + ? WHERE id=?';
            $row_update_stok = $db->prepare($sql_update_stok);
            $row_update_stok->execute(array($qty_dikembalikan, $produk_id_terkait));

            $db->commit(); // Commit transaksi
            echo json_encode(['status' => 'success', 'message' => 'Item berhasil dihapus dari keranjang.']);
        } catch (PDOException $e) {
            $db->rollBack(); // Rollback transaksi jika error
            error_log("Error deleting cart item: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus item dari keranjang: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item keranjang tidak ditemukan!']);
    }
    exit; // Penting: Hentikan eksekusi setelah mengirim JSON
}

// Reset Keranjang untuk User Tertentu (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['clear_keranjang_by_user'])) {
    $user_id = (int)sanitize_input($_POST['user_id']); // ID user dari form

    try {
        $db->beginTransaction(); // Mulai transaksi untuk memastikan konsistensi data

        // Ambil semua item di keranjang user ini untuk mengembalikan stok
        $sql_get_user_keranjang = 'SELECT produk_id, qty FROM keranjang WHERE user_id = ?';
        $row_get_user_keranjang = $db->prepare($sql_get_user_keranjang);
        $row_get_user_keranjang->execute([$user_id]);
        $user_items = $row_get_user_keranjang->fetchAll();

        foreach ($user_items as $item) {
            $sql_update_stok = 'UPDATE produk SET stok = stok + ? WHERE id=?';
            $row_update_stok = $db->prepare($sql_update_stok);
            $row_update_stok->execute(array($item['qty'], $item['produk_id']));
        }

        // Hapus semua data dari tabel 'keranjang' untuk user ini
        $sql_clear_keranjang = 'DELETE FROM keranjang WHERE user_id = ?';
        $row_clear_keranjang = $db->prepare($sql_clear_keranjang);
        $row_clear_keranjang->execute([$user_id]);

        $db->commit(); // Commit transaksi

        echo json_encode(['status' => 'success', 'message' => 'Keranjang berhasil direset.']);
    } catch (PDOException $e) {
        $db->rollBack(); // Rollback transaksi jika terjadi error
        error_log("Error clearing user cart: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal mereset keranjang: ' . $e->getMessage()]);
    }
    exit; // Penting: Hentikan eksekusi setelah mengirim JSON
}

// Catatan:
// - Bagian untuk menghapus kategori dihilangkan karena skema SQL Anda
//   tidak memiliki tabel 'kategori' terpisah. Kolom 'kategori' ada di tabel 'produk'.
