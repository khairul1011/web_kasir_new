<?php
class View
{
    protected $db;

    /**
     * Constructor untuk kelas View.
     * Menginisialisasi koneksi database.
     *
     * @param PDO $db Objek PDO yang sudah terkoneksi ke database.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Mengambil semua data pengguna (users).
     *
     * @return array Hasil query dalam bentuk array.
     */
    public function users()
    {
        // Sesuaikan dengan nama tabel 'users' di database Anda
        $sql = "SELECT * FROM users";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    public function getNavbarUserData($userId)
    {
        // Panggil fungsi yang sudah ada untuk mengambil data user
        $userData = $this->user_edit($userId);

        // Tentukan path foto profil
        $fotoPath = BASE_URL . '/assets/img/profil/default.png'; // Foto default
        if (!empty($userData['foto']) && file_exists(APP_ROOT_PATH . '/assets/img/profil/' . $userData['foto'])) {
            $fotoPath = BASE_URL . '/assets/img/profil/' . $userData['foto'];
        }

        // Kembalikan semua data yang dibutuhkan dalam satu array
        return [
            'data' => $userData,
            'foto_path' => $fotoPath
        ];
    }

    /**
     * Mengambil data pengguna berdasarkan ID untuk proses edit.
     *
     * @param int $id ID pengguna.
     * @return array Hasil query dalam bentuk array asosiatif tunggal.
     */
    // di dalam file fungsi/view/view.php

    public function user_edit($id)
    {
        // Pastikan query ini mengambil semua kolom yang diperlukan, TERMASUK 'foto'
        $sql = "SELECT username, nama, email, nohp, alamat, foto FROM users WHERE id = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        return $row->fetch();
    }

    /**
     * Mengambil semua data kategori produk.
     * Catatan: Tabel 'produk' di SQL Anda memiliki kolom 'kategori' sebagai VARCHAR,
     * bukan tabel 'kategori' terpisah. Fungsi ini akan mengambil kategori unik dari tabel produk.
     * Jika Anda memiliki tabel kategori terpisah, query ini perlu disesuaikan.
     *
     * @return array Hasil query dalam bentuk array.
     */
    public function kategori()
    {
        // Mengambil kategori unik dari tabel 'produk'
        $sql = "SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    // --- FUNGSI HALAMAN TRANSAKSI ---

    /**
     * Mengambil item-item di keranjang untuk user tertentu.
     * Join dengan tabel produk untuk detail produk.
     *
     * @param int $user_id ID user/kasir yang sedang login.
     * @return array Daftar item di keranjang.
     */
    public function get_keranjang_items($user_id)
    {
        $sql = "SELECT k.id AS keranjang_id, k.produk_id, k.qty,
                       p.nama AS nama_produk, p.harga AS harga_produk, p.stok AS stok_produk
                FROM keranjang k
                JOIN produk p ON k.produk_id = p.id
                WHERE k.user_id = ?";
        $row = $this->db->prepare($sql);
        $row->execute([$user_id]);
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghitung total harga di keranjang untuk user tertentu.
     *
     * @param int $user_id ID user/kasir yang sedang login.
     * @return float|int Total harga di keranjang.
     */
    public function get_keranjang_total($user_id)
    {
        $sql = "SELECT SUM(k.qty * p.harga) AS total_keranjang
                FROM keranjang k
                JOIN produk p ON k.produk_id = p.id
                WHERE k.user_id = ?";
        $row = $this->db->prepare($sql);
        $row->execute([$user_id]);
        $hasil = $row->fetch();
        return $hasil['total_keranjang'] ?? 0;
    }

    /**
     * Mengambil semua data pelanggan.
     * @return array Daftar pelanggan.
     */
    public function get_all_pelanggan()
    {
        $sql = "SELECT * FROM pelanggan ORDER BY nama ASC";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil detail transaksi berdasarkan ID transaksi.
     *
     * @param int $transaksi_id ID transaksi.
     * @return array Detail transaksi.
     */
    public function get_detail_transaksi($transaksi_id)
    {
        $sql = "SELECT dt.id, dt.qty, dt.subtotal,
                       p.nama AS nama_produk, p.harga AS harga_satuan
                FROM detail_transaksi dt
                JOIN produk p ON dt.produk_id = p.id
                WHERE dt.transaksi_id = ?";
        $row = $this->db->prepare($sql);
        $row->execute([$transaksi_id]);
        $hasil = $row->fetchAll();
        return $hasil;
    }

    public function generate_search_result_html($keyword = '')
    {
        if (!empty($keyword)) {
            $stmt = $this->db->prepare("SELECT id, nama, harga FROM produk WHERE (nama LIKE ? OR id LIKE ?) AND stok > 0");
            $search_keyword = '%' . $keyword . '%';
            $stmt->execute([$search_keyword, $search_keyword]);
        } else {
            $stmt = $this->db->prepare("SELECT id, nama, harga FROM produk WHERE stok > 0 LIMIT 10");
            $stmt->execute();
        }
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html_output = '';

        if (!empty($products)) {
            foreach ($products as $produk) {
                $html_output .= '<tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">';
                $html_output .= '  <td class="px-2 py-2 font-medium text-gray-900 dark:text-white">' . htmlspecialchars($produk['nama']) . '</td>';
                $html_output .= '  <td class="px-2 py-2">Rp ' . number_format($produk['harga'], 0, ',', '.') . '</td>';
                $html_output .= '  <td class="px-2 py-2">';

                // ==========================================================
                // PERBAIKAN DI BARIS INI: action diubah ke 'fungsi/tambah/tambah.php'
                // ==========================================================
                $html_output .= '      <form method="POST" action="fungsi/tambah/tambah.php">';

                $html_output .= '          <input type="hidden" name="action" value="add_to_cart">';
                $html_output .= '          <input type="hidden" name="produk_id" value="' . $produk['id'] . '">';
                $html_output .= '          <button type="submit" class="font-medium text-blue-600 dark:text-blue-500 hover:underline text-sm">+ Tambah</button>';
                $html_output .= '      </form>';
                $html_output .= '  </td>';
                $html_output .= '</tr>';
            }
        } else {
            $html_output = '<tr><td colspan="3" class="py-4 text-center text-gray-500 dark:text-gray-400">Produk tidak ditemukan.</td></tr>';
        }

        return $html_output;
    }

    public function getLaporanData($filters = [])
    {
        $sql = "SELECT 
                    detail_transaksi.id as detail_id,
                    produk.id as id_barang,
                    produk.nama as nama_barang,
                    detail_transaksi.qty as jumlah,
                    detail_transaksi.subtotal as total,
                    users.nama as kasir,
                    transaksi.tanggal as tanggal_input
                FROM detail_transaksi
                JOIN transaksi ON detail_transaksi.transaksi_id = transaksi.id
                JOIN produk ON detail_transaksi.produk_id = produk.id
                LEFT JOIN users ON transaksi.user_id = users.id";

        $params = [];
        $where_clauses = [];

        // Filter berdasarkan Bulan dan Tahun
        if (!empty($filters['bulan']) && !empty($filters['tahun'])) {
            $bulan = sprintf("%02d", (int)$filters['bulan']);
            $tahun = (int)$filters['tahun'];
            $where_clauses[] = "DATE_FORMAT(transaksi.tanggal, '%Y-%m') = ?";
            $params[] = "$tahun-$bulan";
        }
        // Filter berdasarkan Hari
        else if (!empty($filters['hari'])) {
            $hari = date("Y-m-d", strtotime($filters['hari']));
            $where_clauses[] = "DATE(transaksi.tanggal) = ?";
            $params[] = $hari;
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $sql .= " ORDER BY transaksi.tanggal DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exportLaporanToExcel($filters = [])
    {
        // --- LOGIKA PENGAMBILAN DATA (Tidak ada perubahan di sini) ---
        $sql = "SELECT 
                    produk.id as id_barang,
                    produk.nama as nama_barang,
                    detail_transaksi.qty as jumlah,
                    detail_transaksi.subtotal as total,
                    users.nama as kasir,
                    transaksi.tanggal as tanggal_input
                FROM detail_transaksi
                JOIN transaksi ON detail_transaksi.transaksi_id = transaksi.id
                JOIN produk ON detail_transaksi.produk_id = produk.id
                LEFT JOIN users ON transaksi.user_id = users.id";

        $params = [];
        $where_clauses = [];
        $nama_file = "Laporan Penjualan Keseluruhan";

        if (!empty($filters['bulan']) && !empty($filters['tahun'])) {
            $bulan = sprintf("%02d", (int)$filters['bulan']);
            $tahun = (int)$filters['tahun'];
            $where_clauses[] = "DATE_FORMAT(transaksi.tanggal, '%Y-%m') = ?";
            $params[] = "$tahun-$bulan";
            $nama_bulan = date("F", mktime(0, 0, 0, $bulan, 10));
            $nama_file = "Laporan Penjualan - {$nama_bulan} {$tahun}";
        } else if (!empty($filters['hari'])) {
            $hari = date("Y-m-d", strtotime($filters['hari']));
            $where_clauses[] = "DATE(transaksi.tanggal) = ?";
            $params[] = $hari;
            $nama_file = "Laporan Penjualan - " . date("d F Y", strtotime($hari));
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        $sql .= " ORDER BY transaksi.tanggal ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $laporan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- MEMBUAT FILE CSV YANG DIKENALI EXCEL ---

        // 1. Ubah Content-Type menjadi untuk CSV
        header("Content-Type: text/csv");
        // 2. Ubah ekstensi file menjadi .csv
        header("Content-Disposition: attachment; filename=\"{$nama_file}.csv\"");

        $output = fopen("php://output", "w");

        // Tulis baris header tabel (gunakan koma sebagai pemisah)
        fputcsv($output, [
            'ID Barang',
            'Nama Barang',
            'Jumlah Terjual',
            'Total Penjualan (Rp)',
            'Kasir',
            'Tanggal Input'
        ]);

        // Tulis setiap baris data (gunakan koma sebagai pemisah)
        if (!empty($laporan_data)) {
            foreach ($laporan_data as $row) {
                $data_row = [
                    $row['id_barang'],
                    $row['nama_barang'],
                    $row['jumlah'],
                    $row['total'],
                    $row['kasir'] ?? 'N/A',
                    date("d-m-Y H:i:s", strtotime($row['tanggal_input']))
                ];
                fputcsv($output, $data_row);
            }
        }
        fclose($output);
        exit;
    }

    // --- FUNGSI HALAMAN PRODUK ---
    public function getBarangPageData($getData)
    {
        // This function now correctly passes the filter to the other functions
        $cari_keyword = isset($getData['cari']) ? htmlspecialchars($getData['cari']) : null;
        $filter = isset($getData['filter']) ? htmlspecialchars($getData['filter']) : null; // Recognize the filter

        $batas = 10;
        $halaman = isset($getData['halaman']) ? (int)$getData['halaman'] : 1;
        if ($halaman < 1) $halaman = 1;
        $halaman_awal = ($halaman - 1) * $batas;

        // Pass the filter to the data-fetching functions
        $jumlah_data = $this->produk_row_count_total_with_search($cari_keyword, $filter);
        $total_halaman = ceil($jumlah_data / $batas);

        if ($halaman > $total_halaman && $total_halaman > 0) {
            $halaman = $total_halaman;
            $halaman_awal = ($halaman - 1) * $batas;
        }

        $daftarProduk = $this->produk_pagination_with_search($batas, $halaman_awal, $cari_keyword, $filter);

        // ... (logic for messages remains the same)
        $message = '';
        $message_type = '';
        if (isset($getData['success'])) { /* ... */
        }

        return [
            'daftarProduk' => $daftarProduk,
            'daftarKategori' => $this->kategori(),
            'total_halaman' => $total_halaman,
            'halaman' => $halaman,
            'previous' => $halaman - 1,
            'next' => $halaman + 1,
            'nomor' => $halaman_awal + 1,
            'message' => $message,
            'message_type' => $message_type,
            'cari_keyword' => $cari_keyword,
            'filter' => $filter // Pass the filter to the view
        ];
    }

    /**
     * Mengambil semua data produk.
     *
     * @return array Hasil query dalam bentuk array.
     */
    public function produk()
    {
        // Sesuaikan dengan nama tabel 'produk' di database Anda
        // Tidak ada join dengan tabel kategori karena kategori ada di tabel produk
        $sql = "SELECT * FROM produk ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil semua data produk dengan pagination dan pencarian.
     * Digunakan juga di halaman transaksi untuk mencari produk.
     *
     * @param int $limit Jumlah produk per halaman.
     * @param int $offset Offset (mulai dari item ke berapa).
     * @param string|null $cari Kata kunci pencarian opsional.
     * @return array Hasil query dalam bentuk array.
     */
    public function produk_pagination_with_search($batas, $halaman_awal, $keyword = null, $filter = null)
    {
        $sql = "SELECT * FROM produk";
        $where_clauses = [];
        $params = [];

        if ($keyword) {
            $where_clauses[] = "nama LIKE ?";
            $params[] = '%' . $keyword . '%';
        }

        // ADD THIS LOGIC: If the filter is 'stok_kurang', add a WHERE clause
        if ($filter === 'stok_kurang') {
            $where_clauses[] = "stok < 3";
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $sql .= " ORDER BY id DESC LIMIT ?, ?";

        $params[] = $halaman_awal;
        $params[] = $batas;

        $stmt = $this->db->prepare($sql);
        for ($i = 1; $i <= count($params); $i++) {
            $stmt->bindValue($i, $params[$i - 1], is_int($params[$i - 1]) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menghitung total jumlah produk untuk keperluan pagination dan pencarian.
     *
     * @param string|null $cari Kata kunci pencarian opsional.
     * @return int Total jumlah produk.
     */
    public function produk_row_count_total_with_search($keyword = null, $filter = null)
    {
        $sql = "SELECT COUNT(*) as total FROM produk";
        $where_clauses = [];
        $params = [];

        if ($keyword) {
            $where_clauses[] = "nama LIKE ?";
            $params[] = '%' . $keyword . '%';
        }

        // ADD THIS LOGIC: If the filter is 'stok_kurang', add a WHERE clause
        if ($filter === 'stok_kurang') {
            $where_clauses[] = "stok < 3";
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
