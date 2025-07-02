<?php
require_once __DIR__ . '/../../config.php';
session_start();

function sanitize_input($data)
{
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

    <table class="w-full text-sm text-left rounded-lg text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 rounded-lg uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
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
                            <?php echo htmlspecialchars($hasil['nama']); ?>
                        </td>
                        <td class="px-4 py-3 product-price" data-price="<?php echo $hasil['harga']; ?>">
                            Rp.<?php echo number_format($hasil['harga']); ?>,-
                        </td>
                        <td class="px-4 py-3">
                            <button type="button"
                                data-produk-id="<?php echo $hasil['id']; ?>"
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

$user_id = $_SESSION['user_id'];

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

// ==========================================================
// --- BLOK UNTUK PROSES EDIT PROFIL (VERSI LENGKAP) ---
// ==========================================================
if (isset($_GET['profil'])) {
    session_start();

    // Ambil semua data dari form
    $user_id   = (int)sanitize_input($_POST['id']);
    $nama      = sanitize_input($_POST['nama']);
    $email     = sanitize_input($_POST['email']);
    $nohp      = sanitize_input($_POST['nohp']);
    $alamat    = sanitize_input($_POST['alamat']);

    // Siapkan data dan query SQL
    $data = [$nama, $email, $nohp, $alamat, $user_id];
    $sql = 'UPDATE users SET nama=?, email=?, nohp=?, alamat=? WHERE id=?';

    $row = $db->prepare($sql);

    try {
        $row->execute($data);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Profil berhasil diperbarui!'];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal memperbarui profil.'];
        error_log("Error updating profile: " . $e->getMessage());
    }

    header('Location: ../../index.php?page=user');
    exit;
}

// ==========================================================
// --- BLOK BARU UNTUK PROSES UPLOAD FOTO ---
// ==========================================================
if (isset($_GET['foto'])) {
    $user_id = (int)$_POST['id'];

    // Cek apakah ada file yang diunggah dan tidak ada error
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_dir = __DIR__ . '/../../assets/img/profil/'; // Folder penyimpanan foto
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // Batas ukuran file: 2 MB

        $file_name = $_FILES['foto']['name'];
        $file_size = $_FILES['foto']['size'];
        $file_tmp = $_FILES['foto']['tmp_name'];
        $file_type = mime_content_type($file_tmp);

        // 1. Validasi tipe file
        if (in_array($file_type, $allowed_types)) {
            // 2. Validasi ukuran file
            if ($file_size <= $max_size) {
                // 3. Buat nama file yang unik untuk menghindari penimpaan file
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = 'profil-' . $user_id . '-' . uniqid() . '.' . $file_ext;
                $destination = $upload_dir . $new_file_name;

                // 4. Pindahkan file yang diunggah ke folder tujuan
                if (move_uploaded_file($file_tmp, $destination)) {
                    // 5. Update nama file di database
                    $sql = "UPDATE users SET foto = ? WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$new_file_name, $user_id]);

                    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Foto profil berhasil diperbarui!'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal memindahkan file yang diunggah.'];
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Ukuran file terlalu besar (Maks 2MB).'];
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Tipe file tidak valid (hanya JPG, PNG, GIF).'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Tidak ada file yang dipilih atau terjadi error saat unggah.'];
    }

    // Redirect kembali ke halaman profil
    header('Location: ../../index.php?page=user');
    exit;
}


// ==========================================================
// --- BLOK UNTUK PROSES EDIT PROFIL ---
// ==========================================================
if (isset($_GET['profil'])) {
    $user_id   = (int)sanitize_input($_POST['id']);
    $nama      = sanitize_input($_POST['nama']);
    $email     = sanitize_input($_POST['email']);
    $nohp      = sanitize_input($_POST['nohp']);
    $alamat    = sanitize_input($_POST['alamat']);

    $data = [$nama, $email, $nohp, $alamat, $user_id];
    $sql = 'UPDATE users SET nama=?, email=?, nohp=?, alamat=? WHERE id=?';

    $row = $db->prepare($sql);
    try {
        $row->execute($data);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Profil berhasil diperbarui!'];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal memperbarui profil.'];
    }

    header('Location: ../../index.php?page=user');
    exit;
}

// ==========================================================
// --- BLOK UNTUK PROSES GANTI PASSWORD ---
// ==========================================================
if (isset($_GET['pass'])) {
    session_start();

    $user_id  = (int)sanitize_input($_POST['id']);
    $password = $_POST['pass'];

    // Pastikan password baru tidak kosong
    if (empty($password)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Password baru tidak boleh kosong.'];
        header('Location: ../../index.php?page=user');
        exit;
    }

    // --- PENYESUAIAN DI SINI ---
    // Gunakan md5() agar konsisten dengan sistem login dan register Anda
    $hashed_password = md5($password);

    $sql = 'UPDATE users SET password=? WHERE id=?';
    $row = $db->prepare($sql);

    try {
        $row->execute([$hashed_password, $user_id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Password berhasil diubah!'];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal mengubah password.'];
        error_log("Password change error: " . $e->getMessage());
    }

    header('Location: ../../index.php?page=user');
    exit;
}

// Setelah semua jenis proses selesai, redirect kembali ke halaman penjualan
header('Location: ../../index.php?page=jual');
exit;
?>