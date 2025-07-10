<?php
class View
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

     public function getDashboardData()
    {
        // Data untuk hari ini
        $today = date('Y-m-d');
        
        // 1. Produk Terjual Hari Ini (Total kuantitas)
        $sql_produk_today = "SELECT SUM(dt.qty) as total 
                             FROM detail_transaksi dt
                             JOIN transaksi t ON dt.transaksi_id = t.id
                             WHERE DATE(t.tanggal) = ?";
        $stmt_produk_today = $this->db->prepare($sql_produk_today);
        $stmt_produk_today->execute([$today]);
        $produk_terjual_hari_ini = $stmt_produk_today->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 2. Pendapatan Hari Ini (Total omzet)
        $sql_omzet_today = "SELECT SUM(total) as total FROM transaksi WHERE DATE(tanggal) = ?";
        $stmt_omzet_today = $this->db->prepare($sql_omzet_today);
        $stmt_omzet_today->execute([$today]);
        $pendapatan_hari_ini = $stmt_omzet_today->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 3. Pendapatan Bulan Ini (Total omzet)
        $current_month = date('Y-m');
        $sql_omzet_month = "SELECT SUM(total) as total FROM transaksi WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?";
        $stmt_omzet_month = $this->db->prepare($sql_omzet_month);
        $stmt_omzet_month->execute([$current_month]);
        $pendapatan_bulan_ini = $stmt_omzet_month->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Kembalikan semua data dalam satu array
        return [
            'produk_terjual_hari_ini' => (int)$produk_terjual_hari_ini,
            'pendapatan_hari_ini' => (float)$pendapatan_hari_ini,
            'pendapatan_bulan_ini' => (float)$pendapatan_bulan_ini
        ];
    }

     public function getChartData()
    {
        // Siapkan array untuk 7 hari terakhir
        $labels = [];
        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); // Format label '01 Jul'
            $revenueData[$date] = 0; // Inisialisasi pendapatan hari itu dengan 0
        }

        // Ambil total pendapatan per hari dari database untuk 7 hari terakhir
        $sql = "SELECT DATE(tanggal) as tanggal_transaksi, SUM(total) as total_harian
                FROM transaksi
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(tanggal)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $daily_totals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Isi array pendapatan dengan data dari database
        foreach ($daily_totals as $row) {
            if (isset($revenueData[$row['tanggal_transaksi']])) {
                $revenueData[$row['tanggal_transaksi']] = (float)$row['total_harian'];
            }
        }

        // Kembalikan data dalam format yang siap digunakan oleh ApexCharts
        return [
            'labels' => array_values($labels),
            'revenue' => array_values($revenueData)
        ];
    }

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

    public function user_edit($id)
    {
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

    // --- FUNGSI HALAMAN LAPORAN ---
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
