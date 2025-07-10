<?php

require_once __DIR__ . '/../../config.php';
session_start();

class EditController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function sanitize_input($data)
    {
        return htmlentities(trim($data));
    }

    private function searchProduk()
    {
        $cari = trim(strip_tags($_POST['keyword']));
        if ($cari == '') return;

        $sql = "SELECT id, nama, harga FROM produk WHERE (id LIKE ? OR nama LIKE ?) AND stok > 0";
        $row = $this->db->prepare($sql);
        $param = "%{$cari}%";
        $row->execute([$param, $param]);
        $hasil_pencarian = $row->fetchAll(PDO::FETCH_ASSOC);

        // Kode untuk mencetak HTML tabel hasil pencarian (sudah benar)
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

    private function updateProduk()
    {
        $id = (int)$this->sanitize_input($_POST['id']);
        $nama = $this->sanitize_input($_POST['nama']);
        $harga = $this->sanitize_input($_POST['harga']);
        $stok = (int)$this->sanitize_input($_POST['stok']);
        $deskripsi = $this->sanitize_input($_POST['deskripsi']);
        
        $kategori_pilihan = $this->sanitize_input($_POST['kategori']);
        $kategori_baru = !empty($_POST['kategori_baru']) ? $this->sanitize_input($_POST['kategori_baru']) : '';
        $kategori = !empty($kategori_baru) ? $kategori_baru : $kategori_pilihan;

        $data = [$nama, $kategori, $harga, $stok, $deskripsi, $id];
        $sql = "UPDATE produk SET nama=?, kategori=?, harga=?, stok=?, deskripsi=? WHERE id=?";
        
        $row = $this->db->prepare($sql);
        $row->execute($data);

        // Flash message dihapus
        header('Location: ../../index.php?page=barang');
    }

    private function updateProfil()
    {
        $user_id = (int)$this->sanitize_input($_POST['id']);
        $nama    = $this->sanitize_input($_POST['nama']);
        $email   = $this->sanitize_input($_POST['email']);
        $nohp    = $this->sanitize_input($_POST['nohp']);
        $alamat  = $this->sanitize_input($_POST['alamat']);

        $data = [$nama, $email, $nohp, $alamat, $user_id];
        $sql = 'UPDATE users SET nama=?, email=?, nohp=?, alamat=? WHERE id=?';
        
        $row = $this->db->prepare($sql);
        try {
            $row->execute($data);
            // Flash message dihapus
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            // Flash message dihapus
        }
        header('Location: ../../index.php?page=user');
    }


    private function gantiPassword()
    {
        $user_id = (int)$this->sanitize_input($_POST['id']);
        $password = $_POST['pass'];
        
        if (empty($password)) {
            // Flash message dihapus, langsung redirect
            header('Location: ../../index.php?page=user');
            exit;
        }

        $hashed_password = md5($password);
        $sql = 'UPDATE users SET password=? WHERE id=?';
        $row = $this->db->prepare($sql);
        $row->execute([$hashed_password, $user_id]);

        // Flash message dihapus
        header('Location: ../../index.php?page=user');
    }

    private function gantiFoto()
    {
        $user_id = (int)$_POST['id'];
    
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = __DIR__ . '/../../assets/img/profil/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024;

            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_size = $_FILES['foto']['size'];
            $file_type = mime_content_type($file_tmp);
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $new_file_name = 'profil-' . $user_id . '-' . uniqid() . '.' . $file_ext;
                $destination = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $sql = "UPDATE users SET foto = ? WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$new_file_name, $user_id]);
                }
            }
        }
        header('Location: ../../index.php?page=user');
    }


    public function handleRequest()
    {
        // Gunakan key pertama dari $_GET untuk menentukan aksi
        $action = key($_GET); 
        
        switch ($action) {
            case 'cari_barang':
                $this->searchProduk();
                break;
            case 'produk':
                $this->updateProduk();
                break;
            case 'profil':
                $this->updateProfil();
                break;
            case 'pass':
                $this->gantiPassword();
                break;
            case 'foto':
                $this->gantiFoto();
                break;
            default:
                // Redirect jika tidak ada aksi yang cocok
                header('Location: ../../index.php');
                break;
        }
        exit; // Hentikan eksekusi setelah aksi selesai
    }
}

$controller = new EditController($db);
$controller->handleRequest();
?>