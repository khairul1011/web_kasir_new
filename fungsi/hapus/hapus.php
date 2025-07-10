<?php

require_once __DIR__ . '/../../config.php';
session_start();

class HapusController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function deleteProduk()
    {
        if (empty($_GET['id'])) {
            $this->redirectWithError('ID produk tidak ditemukan.');
        }

        $produk_id = (int)$_GET['id'];

        try {
          
            $sql = 'DELETE FROM produk WHERE id = ?';
            $row = $this->db->prepare($sql);
            $row->execute([$produk_id]);


            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Data produk berhasil dihapus!'
            ];

        } catch (PDOException $e) {
            
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Gagal menghapus produk. Mungkin produk ini sudah ada dalam transaksi.'
            ];
            error_log("Delete product error: " . $e->getMessage());
        }

        header('Location: ../../index.php?page=barang');
    }


    private function redirectWithError($message)
    {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => $message];
        header('Location: ../../index.php?page=barang');
        exit;
    }


    public function handleRequest()
    {
        if (isset($_GET['produk'])) {
            $this->deleteProduk();
        } else {
            header('Location: ../../index.php');
        }
        exit;
    }
}


$controller = new HapusController($db);
$controller->handleRequest();