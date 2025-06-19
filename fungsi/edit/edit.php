<?php
/*
* File: fungsi/edit/edit.php
* Deskripsi: Menangani proses pengeditan data di database untuk aplikasi kasir.
* Termasuk edit pengaturan outlet, stok produk, detail produk, profil user, dan password user.
*/

// Memanggil file konfigurasi database
require_once __DIR__ . '/../../config.php'; // Sesuaikan path jika struktur folder berbeda

// Fungsi untuk membersihkan dan mengamankan input
function sanitize_input($data) {
    return htmlentities(trim($data));
}

// --- Edit Pengaturan Outlet (sebelumnya 'toko') ---
if (isset($_GET['pengaturan'])) {
    $nama_outlet = sanitize_input($_POST['namatoko']); // Menggunakan 'namatoko' dari form, mapping ke 'nama' di tabel 'outlet'
    $alamat_outlet = sanitize_input($_POST['alamat']);  // Mapping ke 'alamat' di tabel 'outlet'
    $id_outlet = '1'; // Diasumsikan ID outlet yang diedit adalah 1

    // Data yang akan di-update
    $data_outlet = [
        $nama_outlet,
        $alamat_outlet,
        $id_outlet
    ];

    // Query SQL untuk UPDATE data di tabel 'outlet'
    // Kolom 'kontak' dan 'pemilik' tidak ada di tabel 'outlet' Anda, jadi dihilangkan.
    $sql = 'UPDATE outlet SET nama=?, alamat=? WHERE id=?';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_outlet);
        echo '<script>window.location="../../index.php?page=pengaturan&success=edit-data"</script>';
    } catch (PDOException $e) {
        error_log("Error updating outlet settings: " . $e->getMessage());
        echo '<script>alert("Gagal mengedit pengaturan: ' . $e->getMessage() . '");window.location="../../index.php?page=pengaturan&error=gagal-edit"</script>';
    }
}

// --- Edit Stok Produk (sebelumnya 'barang' dan 'stok') ---
if (isset($_GET['stok'])) {
    $restok = (int)sanitize_input($_POST['restok']); // Jumlah stok yang akan ditambahkan
    $produk_id = (int)sanitize_input($_POST['id']);  // ID produk

    // Ambil stok produk saat ini
    $sql_get_stok = 'SELECT stok FROM produk WHERE id=?';
    $row_get_stok = $db->prepare($sql_get_stok);
    $row_get_stok->execute(array($produk_id));
    $hasil_stok = $row_get_stok->fetch();

    if ($hasil_stok) {
        $stok_saat_ini = $hasil_stok['stok'];
        $stok_baru = $restok + $stok_saat_ini;

        // Data yang akan di-update
        $data_update_stok = [
            $stok_baru,
            $produk_id
        ];

        // Query SQL untuk UPDATE stok di tabel 'produk'
        $sql_update_stok = 'UPDATE produk SET stok=? WHERE id=?';
        $row_update_stok = $db->prepare($sql_update_stok);

        try {
            $row_update_stok->execute($data_update_stok);
            echo '<script>window.location="../../index.php?page=produk&success-stok=stok-data"</script>';
        } catch (PDOException $e) {
            error_log("Error updating product stock: " . $e->getMessage());
            echo '<script>alert("Gagal mengedit stok produk: ' . $e->getMessage() . '");window.location="../../index.php?page=produk&error=gagal-edit-stok"</script>';
        }
    } else {
        echo '<script>alert("Produk tidak ditemukan!");window.location="../../index.php?page=produk"</script>';
    }
}

// --- Edit Detail Produk (sebelumnya 'barang') ---
if (isset($_GET['produk'])) { // Menggunakan 'produk' sebagai parameter GET
    $produk_id = (int)sanitize_input($_POST['id']);
    $kategori  = sanitize_input($_POST['kategori']); // Kolom 'kategori' di tabel 'produk'
    $nama      = sanitize_input($_POST['nama']);     // Kolom 'nama' di tabel 'produk'
    $harga     = sanitize_input($_POST['harga']);    // Kolom 'harga' di tabel 'produk' (diasumsikan harga jual)
    $stok      = (int)sanitize_input($_POST['stok']); // Kolom 'stok' di tabel 'produk'
    $deskripsi = isset($_POST['deskripsi']) ? sanitize_input($_POST['deskripsi']) : null; // Kolom 'deskripsi' (opsional)
    $outlet_id = isset($_POST['outlet_id']) ? (int)$_POST['outlet_id'] : null; // Kolom 'outlet_id' (opsional)

    // Data yang akan di-update
    $data_produk = [
        $kategori,
        $nama,
        $harga,
        $stok,
        $deskripsi,
        $outlet_id,
        $produk_id
    ];

    // Query SQL untuk UPDATE data di tabel 'produk'
    // Kolom 'merk', 'harga_beli', 'satuan_barang', 'tgl_update' tidak ada di tabel 'produk' Anda.
    $sql = 'UPDATE produk SET kategori=?, nama=?, harga=?, stok=?, deskripsi=?, outlet_id=? WHERE id=?';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_produk);
        echo '<script>window.location="../../index.php?page=produk/edit&id=' . $produk_id . '&success=edit-data"</script>';
    } catch (PDOException $e) {
        error_log("Error updating product details: " . $e->getMessage());
        echo '<script>alert("Gagal mengedit detail produk: ' . $e->getMessage() . '");window.location="../../index.php?page=produk/edit&id=' . $produk_id . '&error=gagal-edit"</script>';
    }
}

// --- Edit Profil User (sebelumnya 'profil' untuk 'member') ---
if (isset($_GET['profil'])) {
    $user_id = (int)sanitize_input($_POST['id']);
    $nama_user = sanitize_input($_POST['nama']); // Kolom 'nama' di tabel 'users'

    // Data yang akan di-update
    $data_user_profil = [
        $nama_user,
        $user_id
    ];

    // Query SQL untuk UPDATE data di tabel 'users'
    // Kolom 'alamat_member', 'telepon', 'email', 'NIK' tidak ada di tabel 'users' Anda.
    $sql = 'UPDATE users SET nama=? WHERE id=?';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_user_profil);
        echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
    } catch (PDOException $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        echo '<script>alert("Gagal mengedit profil user: ' . $e->getMessage() . '");window.location="../../index.php?page=user&error=gagal-edit-profil"</script>';
    }
}

// --- Edit Password User (sebelumnya 'pass' untuk 'login') ---
if (isset($_GET['pass'])) {
    $user_id = (int)sanitize_input($_POST['id']);
    $username = sanitize_input($_POST['user']);
    $password = sanitize_input($_POST['pass']); // Password baru (belum di-hash)

    // Data yang akan di-update
    $data_user_pass = [
        $username,
        md5($password), // Menggunakan MD5 sesuai contoh Anda. Pertimbangkan password_hash() untuk keamanan lebih.
        $user_id
    ];

    // Query SQL untuk UPDATE data di tabel 'users'
    $sql = 'UPDATE users SET username=?, password=? WHERE id=?';
    $row = $db->prepare($sql);

    try {
        $row->execute($data_user_pass);
        echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
    } catch (PDOException $e) {
        error_log("Error updating user password: " . $e->getMessage());
        echo '<script>alert("Gagal mengedit password user: ' . $e->getMessage() . '");window.location="../../index.php?page=user&error=gagal-edit-password"</script>';
    }
}

// --- Edit Kuantitas Item di Keranjang (sebelumnya 'jual') ---
if (isset($_GET['keranjang_qty'])) { // Menggunakan 'keranjang_qty' untuk menghindari konflik dengan 'jual' di tambah.php
    $keranjang_item_id = (int)sanitize_input($_POST['id']); // ID item di tabel keranjang
    $produk_id_keranjang = (int)sanitize_input($_POST['id_produk']); // ID produk yang terkait dengan item keranjang
    $jumlah_baru = (int)sanitize_input($_POST['jumlah']); // Kuantitas baru yang diinginkan

    // Ambil informasi stok produk dan kuantitas saat ini di keranjang
    $sql_get_info = "SELECT p.stok, k.qty
                     FROM produk p
                     JOIN keranjang k ON p.id = k.produk_id
                     WHERE k.id = ?";
    $row_get_info = $db->prepare($sql_get_info);
    $row_get_info->execute(array($keranjang_item_id));
    $info_item = $row_get_info->fetch();

    if ($info_item) {
        $stok_produk_tersedia = $info_item['stok'] + $info_item['qty']; // Stok + qty yang sudah ada di keranjang
        $qty_lama = $info_item['qty'];

        if ($jumlah_baru <= $stok_produk_tersedia) {
            // Hitung selisih stok yang perlu disesuaikan
            $selisih_qty = $jumlah_baru - $qty_lama;

            // Update kuantitas di keranjang
            $sql_update_qty_keranjang = 'UPDATE keranjang SET qty=? WHERE id=?';
            $row_update_qty_keranjang = $db->prepare($sql_update_qty_keranjang);
            $row_update_qty_keranjang->execute(array($jumlah_baru, $keranjang_item_id));

            // Sesuaikan stok produk di tabel produk
            $sql_update_stok_produk = 'UPDATE produk SET stok = stok - ? WHERE id=?';
            $row_update_stok_produk = $db->prepare($sql_update_stok_produk);
            $row_update_stok_produk->execute(array($selisih_qty, $produk_id_keranjang));

            echo '<script>window.location="../../index.php?page=jual#keranjang"</script>';
        } else {
            echo '<script>alert("Jumlah melebihi stok produk yang tersedia!");window.location="../../index.php?page=jual#keranjang"</script>';
        }
    } else {
        echo '<script>alert("Item keranjang tidak ditemukan!");window.location="../../index.php?page=jual"</script>';
    }
}

// --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI ---

// Edit Kuantitas Item di Keranjang (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['keranjang_qty'])) {
    $keranjang_item_id = (int)sanitize_input($_POST['keranjang_item_id']); // ID item di tabel keranjang
    $produk_id_keranjang = (int)sanitize_input($_POST['produk_id']); // ID produk yang terkait dengan item keranjang
    $jumlah_baru = (int)sanitize_input($_POST['qty_baru']); // Kuantitas baru yang diinginkan

    try {
        $db->beginTransaction(); // Mulai transaksi database

        // Ambil informasi stok produk dan kuantitas saat ini di keranjang
        $sql_get_info = "SELECT p.stok, k.qty
                         FROM produk p
                         JOIN keranjang k ON p.id = k.produk_id
                         WHERE k.id = ?";
        $row_get_info = $db->prepare($sql_get_info);
        $row_get_info->execute(array($keranjang_item_id));
        $info_item = $row_get_info->fetch();

        if ($info_item) {
            $stok_produk_saat_ini = $info_item['stok'];
            $qty_lama = $info_item['qty'];

            // Hitung selisih stok yang perlu disesuaikan
            $selisih_qty = $jumlah_baru - $qty_lama;

            // Periksa apakah stok cukup untuk penambahan kuantitas
            if ($stok_produk_saat_ini - $selisih_qty >= 0) {
                // Update kuantitas di keranjang
                $sql_update_qty_keranjang = 'UPDATE keranjang SET qty=? WHERE id=?';
                $row_update_qty_keranjang = $db->prepare($sql_update_qty_keranjang);
                $row_update_qty_keranjang->execute(array($jumlah_baru, $keranjang_item_id));

                // Sesuaikan stok produk di tabel produk
                $sql_update_stok_produk = 'UPDATE produk SET stok = stok - ? WHERE id=?';
                $row_update_stok_produk = $db->prepare($sql_update_stok_produk);
                $row_update_stok_produk->execute(array($selisih_qty, $produk_id_keranjang));

                $db->commit(); // Commit transaksi
                echo json_encode(['status' => 'success', 'message' => 'Kuantitas keranjang berhasil diperbarui.']);
            } else {
                throw new Exception("Jumlah melebihi stok produk yang tersedia!");
            }
        } else {
            throw new Exception("Item keranjang tidak ditemukan!");
        }
    } catch (Exception $e) {
        $db->rollBack(); // Rollback transaksi jika terjadi error
        error_log("Error updating cart quantity: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit; // Penting: Hentikan eksekusi setelah mengirim JSON
}

// --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI (Diadaptasi dari update_qty lama) ---

// Edit Kuantitas Item di Keranjang (Dipanggil dari AJAX di halaman jual)
if (isset($_GET['keranjang_qty'])) {
    // Pastikan ini adalah permintaan POST dari form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
        exit;
    }

    $keranjang_item_id = (int)sanitize_input($_POST['keranjang_item_id']); // ID item di tabel keranjang
    $produk_id_keranjang = (int)sanitize_input($_POST['produk_id']); // ID produk yang terkait dengan item keranjang
    $jumlah_baru = (int)sanitize_input($_POST['qty_baru']); // Kuantitas baru yang diinginkan

    try {
        $db->beginTransaction(); // Mulai transaksi database

        // Ambil informasi stok produk dan kuantitas saat ini di keranjang
        $sql_get_info = "SELECT p.stok, k.qty
                         FROM produk p
                         JOIN keranjang k ON p.id = k.produk_id
                         WHERE k.id = ?";
        $row_get_info = $db->prepare($sql_get_info);
        $row_get_info->execute(array($keranjang_item_id));
        $info_item = $row_get_info->fetch();

        if ($info_item) {
            $stok_produk_saat_ini = $info_item['stok'];
            $qty_lama = $info_item['qty'];

            // Hitung selisih stok yang perlu disesuaikan
            $selisih_qty = $jumlah_baru - $qty_lama;

            // Periksa apakah stok cukup untuk penambahan kuantitas
            // Stok yang tersedia adalah stok_produk_saat_ini dikurangi selisih_qty
            // Jika selisih_qty positif (menambah qty di keranjang), stok_produk_saat_ini harus cukup
            // Jika selisih_qty negatif (mengurangi qty di keranjang), stok_produk_saat_ini akan bertambah
            if ($stok_produk_saat_ini - $selisih_qty < 0) {
                throw new Exception("Stok produk tidak mencukupi untuk kuantitas yang diminta.");
            }

            // Update kuantitas di keranjang
            $sql_update_qty_keranjang = 'UPDATE keranjang SET qty=? WHERE id=?';
            $row_update_qty_keranjang = $db->prepare($sql_update_qty_keranjang);
            $row_update_qty_keranjang->execute(array($jumlah_baru, $keranjang_item_id));

            // Sesuaikan stok produk di tabel produk
            $sql_update_stok_produk = 'UPDATE produk SET stok = stok - ? WHERE id=?';
            $row_update_stok_produk = $db->prepare($sql_update_stok_produk);
            $row_update_stok_produk->execute(array($selisih_qty, $produk_id_keranjang));

            $db->commit(); // Commit transaksi
            echo json_encode(['status' => 'success', 'message' => 'Kuantitas keranjang berhasil diperbarui.']);
        } else {
            throw new Exception("Item keranjang tidak ditemukan!");
        }
    } catch (Exception $e) {
        $db->rollBack(); // Rollback transaksi jika terjadi error
        error_log("Error updating cart quantity: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit; // Penting: Hentikan eksekusi setelah mengirim JSON
}

// --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI (Diadaptasi dari delete_keranjang.php lama) ---

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
    // Pastikan ini adalah permintaan POST dari form
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Metode permintaan tidak valid.']);
        exit;
    }

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
// - Bagian untuk mengedit kategori dihilangkan karena skema SQL Anda
//   tidak memiliki tabel 'kategori' terpisah. Kolom 'kategori' ada di tabel 'produk'.
// - Bagian untuk mengedit gambar user dihilangkan karena tabel 'users'
//   tidak memiliki kolom 'gambar'.
// - Bagian 'cari_barang' dihilangkan karena ini adalah logika tampilan/pencarian,
//   bukan operasi edit, dan lebih cocok di file 'view.php' atau file terpisah.
