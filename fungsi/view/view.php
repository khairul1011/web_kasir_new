<?php
/*
* PROSES TAMPIL DATA
* File ini berisi kelas View untuk mengambil data dari database.
* Fungsi-fungsi di dalamnya disesuaikan dengan struktur tabel dari kasir-app.sql.
*/

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

    /**
     * Mengambil data pengguna berdasarkan ID untuk proses edit.
     *
     * @param int $id ID pengguna.
     * @return array Hasil query dalam bentuk array asosiatif tunggal.
     */
    public function user_edit($id)
    {
        // Sesuaikan dengan nama tabel 'users' di database Anda
        $sql = "SELECT * FROM users WHERE id = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mengambil informasi outlet (toko).
     * Diasumsikan ada satu entri outlet dengan ID 1.
     *
     * @return array Hasil query dalam bentuk array asosiatif tunggal.
     */
    public function outlet()
    {
        // Sesuaikan dengan nama tabel 'outlet' di database Anda
        $sql = "SELECT * FROM outlet WHERE id = '1'";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
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
     * Mengambil data produk dengan stok kurang dari atau sama dengan 3.
     *
     * @return array Hasil query dalam bentuk array.
     */
    public function produk_stok_rendah()
    {
        // Sesuaikan dengan nama tabel 'produk' di database Anda
        $sql = "SELECT * FROM produk WHERE stok <= 3 ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil data produk berdasarkan ID untuk proses edit.
     *
     * @param int $id ID produk.
     * @return array Hasil query dalam bentuk array asosiatif tunggal.
     */
    public function produk_edit($id)
    {
        // Sesuaikan dengan nama tabel 'produk' di database Anda
        $sql = "SELECT * FROM produk WHERE id = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mencari produk berdasarkan ID, nama, atau deskripsi.
     *
     * @param string $cari Kata kunci pencarian.
     * @return array Hasil query dalam bentuk array.
     */
    public function produk_cari($cari)
    {
        // Sesuaikan dengan nama tabel 'produk' di database Anda
        $sql = "SELECT * FROM produk WHERE id LIKE ? OR nama LIKE ? OR deskripsi LIKE ?";
        $row = $this->db->prepare($sql);
        $param = "%{$cari}%";
        $row->execute(array($param, $param, $param));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghasilkan ID produk baru secara otomatis (BR001, BR002, dst.).
     * Ini adalah logika yang sama dengan contoh Anda, disesuaikan untuk 'id' dari tabel 'produk'.
     *
     * @return string Format ID produk baru.
     */
    public function produk_id_otomatis()
    {
        $sql = 'SELECT id FROM produk ORDER BY id DESC LIMIT 1'; // Ambil ID terakhir
        $row = $this->db->prepare($sql);
        $row->execute();
        $last_id = $row->fetchColumn(); // Ambil hanya kolom ID

        if ($last_id) {
            // Asumsi ID produk adalah integer, kita akan membuat format string 'BR' + ID
            $next_id_num = (int)$last_id + 1;
            // Format menjadi BR001, BR010, BR100, dst.
            if ($next_id_num < 10) {
                $format = 'BR00' . $next_id_num;
            } elseif ($next_id_num < 100) {
                $format = 'BR0' . $next_id_num;
            } else {
                $format = 'BR' . $next_id_num;
            }
        } else {
            // Jika belum ada produk, mulai dari BR001
            $format = 'BR001';
        }
        return $format;
    }


    /**
     * Mengambil data kategori produk berdasarkan nama kategori untuk proses edit.
     * Karena 'kategori' adalah kolom di tabel 'produk', kita akan mencari produk berdasarkan kategori.
     * Jika Anda ingin mengedit nama kategori, ini akan lebih kompleks.
     * Untuk saat ini, fungsi ini akan mengembalikan produk-produk dengan kategori tertentu.
     *
     * @param string $kategori Nama kategori.
     * @return array Hasil query dalam bentuk array.
     */
    public function kategori_produk_by_nama($kategori)
    {
        $sql = "SELECT * FROM produk WHERE kategori = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($kategori));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghitung jumlah kategori unik.
     *
     * @return int Jumlah kategori unik.
     */
    public function kategori_row_count()
    {
        $sql = "SELECT COUNT(DISTINCT kategori) AS total_kategori FROM produk WHERE kategori IS NOT NULL AND kategori != ''";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchColumn();
        return $hasil;
    }

    /**
     * Menghitung jumlah total produk.
     *
     * @return int Jumlah total produk.
     */
    public function produk_row_count()
    {
        $sql = "SELECT COUNT(*) FROM produk";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchColumn();
        return $hasil;
    }

    /**
     * Menghitung total stok dari semua produk.
     *
     * @return array Hasil query dalam bentuk array asosiatif tunggal (kolom 'jml').
     */
    public function produk_total_stok()
    {
        $sql = "SELECT SUM(stok) as jml FROM produk";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menghitung total harga beli dari semua produk.
     * Catatan: Tabel 'produk' memiliki 'harga', bukan 'harga_beli'.
     * Saya akan menggunakan 'harga' sebagai harga beli untuk tujuan ini.
     * Jika ada kolom harga beli terpisah, perlu disesuaikan.
     *
     * @return array Hasil query dalam bentuk array asosiatif tunggal (kolom 'beli').
     */
    public function produk_total_harga_beli()
    {
        $sql = "SELECT SUM(harga) as beli FROM produk"; // Menggunakan 'harga' sebagai pengganti 'harga_beli'
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mengambil data transaksi untuk bulan saat ini.
     * Menggunakan tabel `transaksi` dan `detail_transaksi`.
     * Join dengan `produk` untuk mendapatkan nama produk.
     * Join dengan `users` (sebagai pengganti `member`) untuk mendapatkan nama user.
     *
     * @return array Hasil query dalam bentuk array.
     */
    public function transaksi_bulan_ini()
    {
        $sql = "SELECT
                    t.id AS transaksi_id,
                    t.kode_transaksi,
                    t.total AS total_transaksi,
                    t.metode_pembayaran,
                    t.tanggal,
                    dt.qty,
                    dt.subtotal,
                    p.nama AS nama_produk,
                    p.harga AS harga_produk,
                    u.nama AS nama_user
                FROM
                    transaksi t
                INNER JOIN
                    detail_transaksi dt ON t.id = dt.transaksi_id
                INNER JOIN
                    produk p ON dt.produk_id = p.id
                LEFT JOIN
                    users u ON t.user_id = u.id
                WHERE
                    DATE_FORMAT(t.tanggal, '%m-%Y') = ?
                ORDER BY
                    t.tanggal DESC, t.id DESC"; // Urutkan berdasarkan tanggal transaksi dan ID transaksi
        $row = $this->db->prepare($sql);
        $row->execute(array(date('m-Y')));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil data transaksi berdasarkan periode (bulan-tahun).
     *
     * @param string $periode Periode dalam format 'MM-YYYY'.
     * @return array Hasil query dalam bentuk array.
     */
    public function transaksi_by_periode($periode)
    {
        $sql = "SELECT
                    t.id AS transaksi_id,
                    t.kode_transaksi,
                    t.total AS total_transaksi,
                    t.metode_pembayaran,
                    t.tanggal,
                    dt.qty,
                    dt.subtotal,
                    p.nama AS nama_produk,
                    p.harga AS harga_produk,
                    u.nama AS nama_user
                FROM
                    transaksi t
                INNER JOIN
                    detail_transaksi dt ON t.id = dt.transaksi_id
                INNER JOIN
                    produk p ON dt.produk_id = p.id
                LEFT JOIN
                    users u ON t.user_id = u.id
                WHERE
                    DATE_FORMAT(t.tanggal, '%m-%Y') = ?
                ORDER BY
                    t.tanggal ASC, t.id ASC";
        $row = $this->db->prepare($sql);
        $row->execute(array($periode));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil data transaksi berdasarkan hari.
     * Parameter $hari diharapkan dalam format 'YYYY-MM-DD'.
     *
     * @param string $hari Tanggal dalam format 'YYYY-MM-DD'.
     * @return array Hasil query dalam bentuk array.
     */
    public function transaksi_by_hari($hari)
    {
        // Format tanggal dari 'YYYY-MM-DD' menjadi 'D MMMM YYYY' untuk pencarian LIKE
        // Contoh: '2025-05-29' menjadi '29 May 2025'
        $timestamp = strtotime($hari);
        $formatted_date = date('j F Y', $timestamp); // j untuk tanggal tanpa leading zero, F untuk nama bulan lengkap

        $param = "%{$formatted_date}%";

        $sql = "SELECT
                    t.id AS transaksi_id,
                    t.kode_transaksi,
                    t.total AS total_transaksi,
                    t.metode_pembayaran,
                    t.tanggal,
                    dt.qty,
                    dt.subtotal,
                    p.nama AS nama_produk,
                    p.harga AS harga_produk,
                    u.nama AS nama_user
                FROM
                    transaksi t
                INNER JOIN
                    detail_transaksi dt ON t.id = dt.transaksi_id
                INNER JOIN
                    produk p ON dt.produk_id = p.id
                LEFT JOIN
                    users u ON t.user_id = u.id
                WHERE
                    DATE_FORMAT(t.tanggal, '%e %M %Y') LIKE ? -- Sesuaikan format tanggal di sini
                ORDER BY
                    t.tanggal ASC, t.id ASC";
        $row = $this->db->prepare($sql);
        $row->execute(array($param));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghitung total penjualan (total dari semua transaksi).
     *
     * @return array Hasil query dalam bentuk array asosiatif tunggal (kolom 'bayar').
     */
    public function total_penjualan()
    {
        $sql = "SELECT SUM(total) as bayar FROM transaksi";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menghitung total nilai stok (harga * stok) dari semua produk.
     *
     * @return array Hasil query dalam bentuk array asosiatif tunggal (kolom 'byr').
     */
    public function total_nilai_stok()
    {
        $sql = "SELECT SUM(harga * stok) as byr FROM produk";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mengambil semua data produk dengan pagination.
     *
     * @param int $limit Jumlah produk per halaman.
     * @param int $offset Offset (mulai dari item ke berapa).
     * @return array Hasil query dalam bentuk array.
     */
    public function produk_pagination($limit, $offset)
    {
        // PERBAIKAN KRITIS: Sisipkan $limit dan $offset langsung ke query
        // Pastikan nilai sudah divalidasi sebagai integer sebelum mencapai sini
        // (yang sudah kita lakukan di modules/barang/index.php)
        $sql = "SELECT * FROM produk ORDER BY id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $row = $this->db->prepare($sql);
        $row->execute(); // Tidak ada parameter di sini karena sudah disisipkan
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghitung total jumlah produk untuk keperluan pagination.
     *
     * @return int Total jumlah produk.
     */
    public function produk_row_count_total()
    {
        $sql = "SELECT COUNT(*) FROM produk";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchColumn();
        return $hasil;
    }

    /**
     * Menghitung jumlah total pelanggan.
     *
     * @return int Jumlah total pelanggan.
     */
    public function pelanggan_row_count()
    {
        $sql = "SELECT COUNT(*) FROM pelanggan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchColumn();
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
    public function produk_pagination_with_search($limit, $offset, $cari = null)
    {
        $sql = "SELECT * FROM produk ";
        $params = [];
        if ($cari) {
            $sql .= "WHERE nama LIKE ? OR kategori LIKE ? OR deskripsi LIKE ? OR id LIKE ? ";
            $param_like = "%{$cari}%";
            $params = [$param_like, $param_like, $param_like, $param_like];
        }
        $sql .= "ORDER BY id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $row = $this->db->prepare($sql);
        $row->execute($params);
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghitung total jumlah produk untuk keperluan pagination dan pencarian.
     *
     * @param string|null $cari Kata kunci pencarian opsional.
     * @return int Total jumlah produk.
     */
    public function produk_row_count_total_with_search($cari = null)
    {
        $sql = "SELECT COUNT(*) FROM produk ";
        $params = [];
        if ($cari) {
            $sql .= "WHERE nama LIKE ? OR kategori LIKE ? OR deskripsi LIKE ? OR id LIKE ? ";
            $param_like = "%{$cari}%";
            $params = [$param_like, $param_like, $param_like, $param_like];
        }
        
        $row = $this->db->prepare($sql);
        $row->execute($params);
        $hasil = $row->fetchColumn();
        return $hasil;
    }

    // --- FUNGSI BARU UNTUK HALAMAN TRANSAKSI ---

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
}

