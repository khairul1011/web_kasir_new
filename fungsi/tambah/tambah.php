<?php

require_once __DIR__ . '/../../config.php';
session_start();

class TambahController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Bersihkan input dari pengguna.
     */
    private function sanitize_input($data)
    {
        return htmlentities(trim($data));
    }


    private function addProduk()
    {
        $kategori_pilihan = $this->sanitize_input($_POST['kategori']);
        $kategori_baru    = $this->sanitize_input($_POST['kategori_baru']);
        $kategori = !empty($kategori_baru) ? $kategori_baru : $kategori_pilihan;

        $nama      = $this->sanitize_input($_POST['nama']);
        $harga     = $this->sanitize_input($_POST['harga']);
        $stok      = $this->sanitize_input($_POST['stok']);
        $deskripsi = isset($_POST['deskripsi']) ? $this->sanitize_input($_POST['deskripsi']) : null;
        $outlet_id = isset($_POST['outlet_id']) ? (int)$_POST['outlet_id'] : null;

        $data_produk = [$nama, $kategori, $harga, $stok, $deskripsi, $outlet_id];
        $sql = 'INSERT INTO produk (nama, kategori, harga, stok, deskripsi, outlet_id) VALUES (?, ?, ?, ?, ?, ?)';
        
        try {
            $row = $this->db->prepare($sql);
            $row->execute($data_produk);
        } catch (PDOException $e) {
            error_log("Error adding product: " . $e->getMessage());
        }
    
        echo '<script>window.location="../../index.php?page=barang";</script>';
    }


    private function processPayment()
    {
        $user_id = $_SESSION['user_id'] ?? 1;
        $cart_json = $_POST['cart_data'];
        $keranjang_items = json_decode($cart_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($keranjang_items)) {
            header('Location: ../../index.php?page=jual');
            exit;
        }

        $total_bayar = array_sum(array_column($keranjang_items, 'subtotal'));
        
        $this->db->beginTransaction();
        try {
            $kode_transaksi = 'TRX' . time();
            $stmt_trans = $this->db->prepare("INSERT INTO transaksi (kode_transaksi, user_id, total, metode_pembayaran, tanggal) VALUES (?, ?, ?, 'Tunai', NOW())");
            $stmt_trans->execute([$kode_transaksi, $user_id, $total_bayar]);
            $transaksi_id = $this->db->lastInsertId();

            $stmt_detail = $this->db->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, subtotal) VALUES (?, ?, ?, ?)");
            $stmt_stok = $this->db->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

            foreach ($keranjang_items as $item) {
                $stmt_detail->execute([$transaksi_id, $item['id'], $item['quantity'], $item['subtotal']]);
                $stmt_stok->execute([$item['quantity'], $item['id']]);
            }

            $this->db->commit();
           
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Payment process error: " . $e->getMessage());
        }
        header('Location: ../../index.php?page=jual');
    }


    public function handleRequest()
    {
        if (isset($_GET['produk'])) {
            $this->addProduk();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cart_data'])) {
            $this->processPayment();
        } else {
            header('Location: ../../index.php');
        }
        exit;
    }
}

$controller = new TambahController($db);
$controller->handleRequest();