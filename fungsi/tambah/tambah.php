<?php
/*
* File: fungsi/tambah/tambah.php
* Deskripsi: Menangani proses penambahan data ke database untuk aplikasi kasir.
* Termasuk penambahan produk dan penambahan item ke keranjang.
*/

// Memanggil file konfigurasi database
// Path yang benar: Dari fungsi/tambah/ naik 2 level ke root, lalu ke config.php
require_once __DIR__ . '/../../config.php'; 

// Fungsi untuk membersihkan dan mengamankan input
function sanitize_input($data) {
    return htmlentities(trim($data));
}

// --- Tambah Produk ---
if (isset($_GET['produk'])) { 
    $kategori = sanitize_input($_POST['kategori']);     
    $nama     = sanitize_input($_POST['nama']);         
    $harga    = sanitize_input($_POST['harga']);        
    $stok     = sanitize_input($_POST['stok']);         
    $deskripsi = isset($_POST['deskripsi']) ? sanitize_input($_POST['deskripsi']) : null; 
    $outlet_id = isset($_POST['outlet_id']) ? (int)$_POST['outlet_id'] : null; 

    $data_produk = [
        $nama,
        $kategori,
        $harga,
        $stok,
        $deskripsi,
        $outlet_id
    ];

    $sql = 'INSERT INTO produk (nama, kategori, harga, stok, deskripsi, outlet_id) VALUES (?, ?, ?, ?, ?, ?)';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_produk);
       echo '<script>window.location="../../index.php?page=barang&success=tambah-data"</script>';
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        echo '<script>alert("Gagal menambahkan produk: ' . $e->getMessage() . '");window.location="../../index.php?page=barang&error=gagal-tambah"</script>';
    }
}

// --- Tambah Item ke Keranjang ---
if (isset($_GET['keranjang'])) { 
    $produk_id = (int)sanitize_input($_GET['id']); 
    $user_id   = (int)sanitize_input($_GET['user_id']); 

    $sql_produk = 'SELECT stok, harga FROM produk WHERE id = ?';
    $row_produk = $db->prepare($sql_produk);
    $row_produk->execute(array($produk_id));
    $produk_info = $row_produk->fetch();

    if ($produk_info) {
        if ($produk_info['stok'] > 0) {
            $sql_cek_keranjang = 'SELECT id, qty FROM keranjang WHERE user_id = ? AND produk_id = ?';
            $row_cek_keranjang = $db->prepare($sql_cek_keranjang);
            $row_cek_keranjang->execute(array($user_id, $produk_id));
            $item_keranjang = $row_cek_keranjang->fetch();

            if ($item_keranjang) {
                $new_qty = $item_keranjang['qty'] + 1;
                $sql_update_keranjang = 'UPDATE keranjang SET qty = ? WHERE id = ?';
                $row_update_keranjang = $db->prepare($sql_update_keranjang);
                $row_update_keranjang->execute(array($new_qty, $item_keranjang['id']));
            } else {
                $qty = 1; 
                $data_keranjang = [
                    $user_id,
                    $produk_id,
                    $qty
                ];
                $sql_insert_keranjang = 'INSERT INTO keranjang (user_id, produk_id, qty) VALUES (?, ?, ?)';
                $row_insert_keranjang = $db->prepare($sql_insert_keranjang);
                $row_insert_keranjang->execute($data_keranjang);
            }

            $sql_update_stok = 'UPDATE produk SET stok = stok - 1 WHERE id = ?';
            $row_update_stok = $db->prepare($sql_update_stok);
            $row_update_stok->execute(array($produk_id));

            echo '<script>window.location="../../index.php?page=jual&success=item-ditambah"</script>';
        } else {
            echo '<script>alert("Stok Produk Habis!");window.location="../../index.php?page=jual#keranjang"</script>';
        }
    } else {
        echo '<script>alert("Produk tidak ditemukan!");window.location="../../index.php?page=jual"</script>';
    }
}
