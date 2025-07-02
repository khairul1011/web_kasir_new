<?php

require_once __DIR__ . '/../../config.php';
session_start();

// Fungsi untuk membersihkan input
function sanitize_input($data)
{
    return htmlentities(trim($data));
}

// --- FUNGSI HALAMAN BARANG ---
// --- TAMBAH PRODUK BARU (DARI HALAMAN BARANG) ---
if (isset($_GET['produk'])) {
    $kategori  = sanitize_input($_POST['kategori']);
    $nama      = sanitize_input($_POST['nama']);
    $harga     = sanitize_input($_POST['harga']);
    $stok      = sanitize_input($_POST['stok']);
    $deskripsi = isset($_POST['deskripsi']) ? sanitize_input($_POST['deskripsi']) : null;
    $outlet_id = isset($_POST['outlet_id']) ? (int)$_POST['outlet_id'] : null;

    $data_produk = [$nama, $kategori, $harga, $stok, $deskripsi, $outlet_id];
    $sql = 'INSERT INTO produk (nama, kategori, harga, stok, deskripsi, outlet_id) VALUES (?, ?, ?, ?, ?, ?)';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_produk);
        echo '<script>window.location="../../index.php?page=barang&success=tambah-data"</script>';
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        echo '<script>alert("Gagal menambahkan produk.");window.location="../../index.php?page=barang&error=gagal-tambah"</script>';
    }
    exit;
}

// --- FUNGSI HALAMAN TRANSAKSI ---
// --- PROSES PEMBAYARAN FINAL (DARI HALAMAN JUAL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cart_data'])) {

    $user_id = $_SESSION['user_id']; // Sesuaikan dengan sesi login Anda

    // 1. Ambil data keranjang dari input hidden dan ubah dari JSON ke array PHP
    $cart_json = $_POST['cart_data'];
    $keranjang_items = json_decode($cart_json, true);

    // Pastikan data valid
    if (json_last_error() === JSON_ERROR_NONE && !empty($keranjang_items)) {

        // 2. Hitung total belanja dari data yang diterima
        $total_bayar = 0;
        foreach ($keranjang_items as $item) {
            $total_bayar += $item['subtotal'];
        }

        // 3. Mulai Transaksi Database (agar aman)
        $db->beginTransaction();
        try {
            // 4. Masukkan 1 baris data ke tabel 'transaksi'
            $kode_transaksi = 'TRX' . time();
            $stmt_trans = $db->prepare("INSERT INTO transaksi (kode_transaksi, user_id, total, metode_pembayaran, tanggal) VALUES (?, ?, ?, 'Tunai', NOW())");
            $stmt_trans->execute([$kode_transaksi, $user_id, $total_bayar]);
            $transaksi_id = $db->lastInsertId();

            // Siapkan query untuk detail dan stok
            $stmt_detail = $db->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, subtotal) VALUES (?, ?, ?, ?)");
            $stmt_stok = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

            // 5. Looping untuk setiap barang di keranjang
            foreach ($keranjang_items as $item) {
                // Masukkan ke 'detail_transaksi'
                $stmt_detail->execute([$transaksi_id, $item['id'], $item['quantity'], $item['subtotal']]);
                // Kurangi stok di tabel 'produk'
                $stmt_stok->execute([$item['quantity'], $item['id']]);
            }

            // 6. Jika semua berhasil, simpan permanen
            $db->commit();
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Transaksi dengan kode $kode_transaksi berhasil!"
            ];
        } catch (Exception $e) {
            // 7. Jika ada 1 saja yang gagal, batalkan semua
            $db->rollBack();
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => "Error: Transaksi Gagal. " . $e->getMessage()
            ];
        }
    } else {
        $_SESSION['flash_message_error'] = "Data keranjang tidak valid atau kosong.";
    }

    // Arahkan kembali ke halaman penjualan dengan pesan notifikasi
    header('Location: ../../index.php?page=jual');
    exit;
}

// Jika tidak ada aksi yang cocok di file ini, redirect ke halaman utama
header('Location: ../../index.php');
exit;
