<?php
if (!isset($view)) {
    require_once __DIR__ . '/../../../config.php';
    require_once __DIR__ . '/../../../fungsi/view/view.php';
    $view = new View($db);
}

// --- PANGGIL FUNGSI UNTUK MENGAMBIL DATA ---
$laporan_data = $view->getLaporanData($_GET);

// --- Tentukan Judul Periode Laporan ---
$periode_laporan = "Keseluruhan";
if (!empty($_GET['bulan']) && !empty($_GET['tahun'])) {
    $nama_bulan = date("F", mktime(0, 0, 0, (int)$_GET['bulan'], 10));
    $periode_laporan = "Bulan {$nama_bulan} {$_GET['tahun']}";
} else if (!empty($_GET['hari'])) {
    $periode_laporan = "Tanggal " . date("d F Y", strtotime($_GET['hari']));
}

// --- KALKULASI TOTAL ---
$total_terjual_item = array_sum(array_column($laporan_data, 'jumlah'));
$total_terjual_rp = array_sum(array_column($laporan_data, 'total'));
?>

<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Laporan Penjualan</h1>
<p class="text-gray-600 dark:text-gray-400 mb-6">Menampilkan laporan untuk periode: <span class="font-semibold"><?php echo $periode_laporan; ?></span></p>

<form method="GET" action="index.php">
    <input type="hidden" name="page" value="laporan">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Cari Laporan Per Bulan</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="pilihBulan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Bulan</label>
                <select name="bulan" id="pilihBulan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Bulan</option>
                    <?php for ($m = 1; $m <= 12; ++$m): ?>
                        <option value="<?php echo $m; ?>" <?php echo (isset($_GET['bulan']) && $_GET['bulan'] == $m) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="pilihTahun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Tahun</label>
                <select name="tahun" id="pilihTahun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Tahun</option>
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo (isset($_GET['tahun']) && $_GET['tahun'] == $y) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-blue-900 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Cari
                </button>
                <a href="index.php?page=laporan" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-800 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12c0 2.21.892 4.202 2.388 5.618M18 19v-5h-.582m-15.356-2A8.001 8.001 0 0120 12c0-2.21-.892-4.202-2.388-5.618"></path>
                    </svg>Refresh
                </a>
                 <a href="index.php?page=laporan&export=true&bulan=<?php echo htmlspecialchars($_GET['bulan'] ?? ''); ?>&tahun=<?php echo htmlspecialchars($_GET['tahun'] ?? ''); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-900 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Excel
                </a>
            </div>
        </div>
    </div>
</form>

<form method="GET" action="index.php">
    <input type="hidden" name="page" value="laporan">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Cari Laporan Per Hari</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="pilihHari" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Hari</label>
                <div class="relative max-w-sm">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                        </svg>
                    </div>
                    <input name="hari" datepicker datepicker-format="yyyy-mm-dd" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Pilih tanggal">
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-blue-900 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Cari
                </button>
                <a href="index.php?page=laporan" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-800 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12c0 2.21.892 4.202 2.388 5.618M18 19v-5h-.582m-15.356-2A8.001 8.001 0 0120 12c0-2.21-.892-4.202-2.388-5.618"></path>
                    </svg>
                    Refresh
                </a>
                 <a href="index.php?page=laporan&export=true&hari=<?php echo htmlspecialchars($_GET['hari'] ?? ''); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-900 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel
                 </a>
            </div>
            <div></div>
        </div>
    </div>
</form>

<div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-6">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">ID Barang</th>
                <th scope="col" class="px-6 py-3">Nama Barang</th>
                <th scope="col" class="px-6 py-3">Jumlah</th>
                <th scope="col" class="px-6 py-3">Total</th>
                <th scope="col" class="px-6 py-3">Kasir</th>
                <th scope="col" class="px-6 py-3">Tanggal Input</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($laporan_data)): ?>
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td colspan="7" class="px-6 py-4 text-center">Tidak ada data laporan untuk periode yang dipilih.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1;
                foreach ($laporan_data as $row): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4"><?php echo $no++; ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['id_barang']); ?></td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['jumlah']); ?></td>
                        <td class="px-6 py-4">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['kasir'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date("d M Y, H:i", strtotime($row['tanggal_input'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr class="bg-gray-100 dark:bg-gray-700 font-semibold text-gray-900 dark:text-white">
                <td colspan="3" class="px-6 py-4 text-right">Total Terjual</td>
                <td class="px-6 py-4"><?php echo htmlspecialchars($total_terjual_item); ?></td>
                <td class="px-6 py-4">Rp <?php echo number_format($total_terjual_rp, 0, ',', '.'); ?></td>
                <td colspan="2" class="px-6 py-4"></td>
            </tr>
        </tbody>
    </table>
</div>