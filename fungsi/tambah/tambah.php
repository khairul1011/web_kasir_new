<?php
/*
* File: fungsi/tambah.php
* Deskripsi: Menangani proses penambahan data ke database untuk aplikasi kasir.
* Termasuk penambahan produk dan penambahan item ke keranjang.
*/

// Memanggil file konfigurasi database
require_once __DIR__ . '/../config.php'; // Sesuaikan path jika struktur folder berbeda

// Fungsi untuk membersihkan dan mengamankan input
function sanitize_input($data) {
    return htmlentities(trim($data));
}

// --- Tambah Produk (sebelumnya 'barang') ---
if (isset($_GET['produk'])) { // Menggunakan 'produk' sebagai parameter GET
    // Ambil data dari POST dan bersihkan
    $kategori = sanitize_input($_POST['kategori']); // Kolom 'kategori' di tabel 'produk'
    $nama     = sanitize_input($_POST['nama']);     // Kolom 'nama' di tabel 'produk'
    $harga    = sanitize_input($_POST['harga']);    // Kolom 'harga' di tabel 'produk' (diasumsikan harga jual)
    $stok     = sanitize_input($_POST['stok']);     // Kolom 'stok' di tabel 'produk'
    $deskripsi = isset($_POST['deskripsi']) ? sanitize_input($_POST['deskripsi']) : null; // Kolom 'deskripsi' (opsional)
    $outlet_id = isset($_POST['outlet_id']) ? (int)$_POST['outlet_id'] : null; // Kolom 'outlet_id' (opsional)

    // Data yang akan di-insert
    $data_produk = [
        $nama,
        $kategori,
        $harga,
        $stok,
        $deskripsi,
        $outlet_id
    ];

    // Query SQL untuk INSERT data ke tabel 'produk'
    // Kolom 'id' adalah AUTO_INCREMENT, jadi tidak perlu disertakan dalam INSERT
    $sql = 'INSERT INTO produk (nama, kategori, harga, stok, deskripsi, outlet_id) VALUES (?, ?, ?, ?, ?, ?)';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_produk);
        // Redirect setelah berhasil menambahkan data
        echo '<script>window.location="../../index.php?page=produk&success=tambah-data"</script>';
    } catch (PDOException $e) {
        // Tangani error jika terjadi masalah saat insert
        error_log("Error adding product: " . $e->getMessage());
        echo '<script>alert("Gagal menambahkan produk: ' . $e->getMessage() . '");window.location="../../index.php?page=produk&error=gagal-tambah"</script>';
    }
}

// --- Tambah Item ke Keranjang (sebelumnya 'jual') ---
if (isset($_GET['keranjang'])) { // Menggunakan 'keranjang' sebagai parameter GET
    $produk_id = (int)sanitize_input($_GET['id']); // ID produk dari GET
    $user_id   = (int)sanitize_input($_GET['user_id']); // ID user/kasir dari GET

    // Dapatkan informasi produk dari database
    $sql_produk = 'SELECT stok, harga FROM produk WHERE id = ?';
    $row_produk = $db->prepare($sql_produk);
    $row_produk->execute(array($produk_id));
    $produk_info = $row_produk->fetch();

    if ($produk_info) {
        if ($produk_info['stok'] > 0) {
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
                $data_keranjang = [
                    $user_id,
                    $produk_id,
                    $qty
                ];
                $sql_insert_keranjang = 'INSERT INTO keranjang (user_id, produk_id, qty) VALUES (?, ?, ?)';
                $row_insert_keranjang = $db->prepare($sql_insert_keranjang);
                $row_insert_keranjang->execute($data_keranjang);
            }

            // Kurangi stok produk
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

// Catatan: Bagian untuk menambah kategori dihilangkan karena skema SQL Anda
// tidak memiliki tabel 'kategori' terpisah. Kolom 'kategori' ada di tabel 'produk'.
// Jika Anda ingin menambahkan kategori sebagai entitas terpisah,
// Anda perlu membuat tabel 'kategori' di database Anda.

?>
