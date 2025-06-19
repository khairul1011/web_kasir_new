<?php
/*
* File: fungsi/tambah/tambah.php
* Deskripsi: Menangani proses penambahan data ke database untuk aplikasi kasir.
* Termasuk penambahan produk dan penambahan item ke keranjang, serta finalisasi transaksi.
*/

// Memanggil file konfigurasi database
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../fungsi/view/view.php'; // Tambahkan ini untuk mengakses fungsi View

// Fungsi untuk membersihkan dan mengamankan input
function sanitize_input($data)
{
    return htmlentities(trim($data));
}

// Buat instance View untuk mengakses data
$view = new View($db);

// ... (Bagian Tambah Produk yang sudah ada sebelumnya TETAP SAMA) ...
if (isset($_GET['produk'])) {
    // ... (kode untuk tambah produk) ...
}

// --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI ---

// Tambah Item ke Keranjang (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['add_to_keranjang'])) {
    $produk_id = (int)sanitize_input($_POST['produk_id']);
    $user_id   = (int)sanitize_input($_POST['user_id']); // Asumsi user_id dikirim dari frontend

    // Dapatkan informasi produk (stok, harga)
    $sql_produk = 'SELECT stok, harga FROM produk WHERE id = ?';
    $row_produk = $db->prepare($sql_produk);
    $row_produk->execute(array($produk_id));
    $produk_info = $row_produk->fetch();

    if ($produk_info) {
        if ($produk_info['stok'] > 0) {
            try {
                $db->beginTransaction(); // Mulai transaksi database

                // Cek apakah produk sudah ada di keranjang untuk user ini
                $sql_cek_keranjang = 'SELECT id, qty FROM keranjang WHERE user_id = ? AND produk_id = ?';
                $row_cek_keranjang = $db->prepare($sql_cek_keranjang);
                $row_cek_keranjang->execute(array($user_id, $produk_id));
                $item_keranjang = $row_cek_keranjang->fetch();

                if ($item_keranjang) {
                    // Jika produk sudah ada, update kuantitasnya
                    $new_qty = $item_keranjang['qty'] + 1;
                    $sql_update_keranjang = 'UPDATE keranjang SET qty = ? WHERE id = ?';
                    $row_update_keranjang = $db->prepare($sql_update_keranjang);
                    $row_update_keranjang->execute(array($new_qty, $item_keranjang['id']));
                } else {
                    // Jika produk belum ada, tambahkan sebagai item baru di keranjang
                    $qty = 1; // Default kuantitas 1 saat pertama kali ditambahkan
                    $data_keranjang = [$user_id, $produk_id, $qty];
                    $sql_insert_keranjang = 'INSERT INTO keranjang (user_id, produk_id, qty) VALUES (?, ?, ?)';
                    $row_insert_keranjang = $db->prepare($sql_insert_keranjang);
                    $row_insert_keranjang->execute($data_keranjang);
                }

                // Kurangi stok produk
                $sql_update_stok = 'UPDATE produk SET stok = stok - 1 WHERE id = ?';
                $row_update_stok = $db->prepare($sql_update_stok);
                $row_update_stok->execute(array($produk_id));

                $db->commit(); // Commit transaksi

                echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang.']);
            } catch (PDOException $e) {
                $db->rollBack(); // Rollback transaksi jika ada error
                error_log("Error adding to cart: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan produk ke keranjang: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok produk habis!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan!']);
    }
    exit; // Penting: Hentikan eksekusi setelah mengirim JSON
}

// Finalisasi Transaksi (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['finalize_transaction'])) {
    $user_id = (int)sanitize_input($_POST['user_id']); // ID user/kasir
    $pelanggan_id = isset($_POST['pelanggan_id']) && $_POST['pelanggan_id'] !== '' ? (int)sanitize_input($_POST['pelanggan_id']) : null; // ID pelanggan (bisa null)
    $metode_pembayaran = sanitize_input($_POST['metode_pembayaran']);

    try {
        $db->beginTransaction(); // Mulai transaksi database

        // 1. Ambil semua item dari keranjang user ini
        $keranjang_items = $view->get_keranjang_items($user_id);
        $total_transaksi = $view->get_keranjang_total($user_id);

        if (empty($keranjang_items)) {
            throw new Exception("Keranjang kosong, tidak dapat memproses transaksi.");
        }

        // 2. Buat entri di tabel 'transaksi'
        $kode_transaksi = 'TRX' . time(); // Contoh kode transaksi sederhana
        $sql_insert_transaksi = 'INSERT INTO transaksi (kode_transaksi, user_id, pelanggan_id, total, metode_pembayaran, tanggal) VALUES (?, ?, ?, ?, ?, NOW())';
        $stmt_transaksi = $db->prepare($sql_insert_transaksi);
        $stmt_transaksi->execute([$kode_transaksi, $user_id, $pelanggan_id, $total_transaksi, $metode_pembayaran]);
        $transaksi_id = $db->lastInsertId(); // Dapatkan ID transaksi yang baru dibuat

        // 3. Pindahkan item dari keranjang ke tabel 'detail_transaksi'
        $sql_insert_detail = 'INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, subtotal) VALUES (?, ?, ?, ?)';
        $stmt_detail = $db->prepare($sql_insert_detail);

        foreach ($keranjang_items as $item) {
            $subtotal = $item['qty'] * $item['harga_produk'];
            $stmt_detail->execute([$transaksi_id, $item['produk_id'], $item['qty'], $subtotal]);
        }

        // 4. Kosongkan keranjang user ini
        $sql_clear_keranjang = 'DELETE FROM keranjang WHERE user_id = ?';
        $stmt_clear = $db->prepare($sql_clear_keranjang);
        $stmt_clear->execute([$user_id]);

        $db->commit(); // Commit transaksi

        echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil diproses!', 'transaksi_id' => $transaksi_id, 'kode_transaksi' => $kode_transaksi]);
    } catch (Exception $e) {
        $db->rollBack(); // Rollback transaksi jika ada error
        error_log("Error finalizing transaction: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
    }
    exit; // Penting: Hentikan eksekusi setelah mengirim JSON

// --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI (Diadaptasi dari tambah_keranjang.php lama) ---

// Tambah Item ke Keranjang (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['add_to_keranjang'])) {
    // Pastikan ini adalah permintaan POST dari form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
        exit;
    }

    $produk_id = (int)sanitize_input($_POST['produk_id']);
    $user_id   = (int)sanitize_input($_POST['user_id']); 
    $qty_to_add = isset($_POST['qty']) ? (int)sanitize_input($_POST['qty']) : 1; // Default 1 jika tidak ada qty

    // Dapatkan informasi produk (stok, harga)
    $sql_produk = 'SELECT stok, harga FROM produk WHERE id = ?';
    $row_produk = $db->prepare($sql_produk);
    $row_produk->execute(array($produk_id));
    $produk_info = $row_produk->fetch();

    if ($produk_info) {
        try {
            $db->beginTransaction(); // Mulai transaksi database

            // Cek apakah produk sudah ada di keranjang untuk user ini
            $sql_cek_keranjang = 'SELECT id, qty FROM keranjang WHERE user_id = ? AND produk_id = ?';
            $row_cek_keranjang = $db->prepare($sql_cek_keranjang);
            $row_cek_keranjang->execute(array($user_id, $produk_id));
            $item_keranjang = $row_cek_keranjang->fetch();

            if ($item_keranjang) {
                // Jika produk sudah ada, update kuantitasnya
                $new_qty_in_cart = $item_keranjang['qty'] + $qty_to_add;
                
                // Cek stok keseluruhan (stok produk di db + qty yang sudah di keranjang)
                if ($produk_info['stok'] + $item_keranjang['qty'] < $new_qty_in_cart) {
                    throw new Exception("Stok produk tidak mencukupi untuk menambahkan kuantitas ini.");
                }

                $sql_update_keranjang = 'UPDATE keranjang SET qty = ? WHERE id = ?';
                $row_update_keranjang = $db->prepare($sql_update_keranjang);
                $row_update_keranjang->execute(array($new_qty_in_cart, $item_keranjang['id']));
            } else {
                // Jika produk belum ada, tambahkan sebagai item baru di keranjang
                if ($produk_info['stok'] < $qty_to_add) {
                    throw new Exception("Stok produk tidak mencukupi untuk menambahkan kuantitas ini.");
                }
                $data_keranjang = [ $user_id, $produk_id, $qty_to_add ];
                $sql_insert_keranjang = 'INSERT INTO keranjang (user_id, produk_id, qty) VALUES (?, ?, ?)';
                $row_insert_keranjang = $db->prepare($sql_insert_keranjang);
                $row_insert_keranjang->execute($data_keranjang);
            }

            // Kurangi stok produk di tabel produk
            $sql_update_stok = 'UPDATE produk SET stok = stok - ? WHERE id = ?';
            $row_update_stok = $db->prepare($sql_update_stok);
            $row_update_stok->execute(array($qty_to_add, $produk_id));

            $db->commit(); // Commit transaksi

            echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang.']);
        } catch (Exception $e) {
            $db->rollBack(); // Rollback transaksi jika ada error
            error_log("Error adding to cart: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan produk ke keranjang: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan!']);
    }
    exit;
}

// Finalisasi Transaksi (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['finalize_transaction'])) {
    // Pastikan ini adalah permintaan POST dari form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
        exit;
    }

    $user_id = (int)sanitize_input($_POST['user_id']);
    $pelanggan_id = isset($_POST['pelanggan_id']) && $_POST['pelanggan_id'] !== '' ? (int)sanitize_input($_POST['pelanggan_id']) : null;
    $metode_pembayaran = sanitize_input($_POST['metode_pembayaran']);

    try {
        $db->beginTransaction();

        $keranjang_items = $view->get_keranjang_items($user_id);
        $total_transaksi = $view->get_keranjang_total($user_id);

        if (empty($keranjang_items)) {
            throw new Exception("Keranjang kosong, tidak dapat memproses transaksi.");
        }

        $kode_transaksi = 'TRX' . time();
        $sql_insert_transaksi = 'INSERT INTO transaksi (kode_transaksi, user_id, pelanggan_id, total, metode_pembayaran, tanggal) VALUES (?, ?, ?, ?, ?, NOW())';
        $stmt_transaksi = $db->prepare($sql_insert_transaksi);
        $stmt_transaksi->execute([$kode_transaksi, $user_id, $pelanggan_id, $total_transaksi, $metode_pembayaran]);
        $transaksi_id = $db->lastInsertId();

        $sql_insert_detail = 'INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, subtotal) VALUES (?, ?, ?, ?)';
        $stmt_detail = $db->prepare($sql_insert_detail);

        foreach ($keranjang_items as $item) {
            $subtotal = $item['qty'] * $item['harga_produk'];
            $stmt_detail->execute([$transaksi_id, $item['produk_id'], $item['qty'], $subtotal]);
        }

        $sql_clear_keranjang = 'DELETE FROM keranjang WHERE user_id = ?';
        $stmt_clear = $db->prepare($sql_clear_keranjang);
        $stmt_clear->execute([$user_id]);

        $db->commit();

        echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil diproses!', 'transaksi_id' => $transaksi_id, 'kode_transaksi' => $kode_transaksi]);

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error finalizing transaction: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
    }
    exit;
}
}
