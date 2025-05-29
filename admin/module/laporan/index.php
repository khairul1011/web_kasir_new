<?php
// admin/module/laporan/index.php

// Pastikan koneksi database tersedia di sini jika diperlukan oleh PHP logic Anda.
// Variabel $koneksi seharusnya sudah didefinisikan di index.php atau config.php
// yang di-include oleh index.php, dan kemudian diwariskan ke home.php dan modul ini.
// Contoh untuk debugging jika $koneksi tidak ada:
// if (!isset($koneksi)) {
//     echo "<p class='text-red-500'>Error: Koneksi database tidak ditemukan. Pastikan file koneksi di-include di index.php.</p>";
// }

// Contoh PHP logic (diabaikan untuk tampilan, tapi penting untuk fungsionalitas)
// Anda akan mengisi ini nanti dengan query untuk mengambil data laporan
$laporan_data = [
    ['no' => 1, 'id_barang' => 'BR001', 'nama_barang' => 'Pensil', 'jumlah' => 1, 'modal' => 1500, 'total' => 3000, 'kasir' => 'Fauzan Falah', 'tanggal_input' => '19 May 2025, 22:33'],
    ['no' => 2, 'id_barang' => 'BR002', 'nama_barang' => 'Buku Tulis', 'jumlah' => 2, 'modal' => 5000, 'total' => 12000, 'kasir' => 'Fauzan Falah', 'tanggal_input' => '20 May 2025, 10:00'],
];
$total_terjual_item = array_sum(array_column($laporan_data, 'jumlah'));
$total_modal_rp = 0;
foreach($laporan_data as $row) {
    $total_modal_rp += $row['modal'] * $row['jumlah']; // Hitung total modal berdasarkan jumlah barang
}
$total_terjual_rp = array_sum(array_column($laporan_data, 'total'));
$keuntungan = $total_terjual_rp - $total_modal_rp;
?>

<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Laporan Penjualan</h1>

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Cari Laporan Per Bulan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label for="pilihBulan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Bulan</label>
            <select id="pilihBulan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option selected>Bulan</option>
                <option value="01">Januari</option>
                <option value="02">Februari</option>
                <option value="03">Maret</option>
                <option value="04">April</option>
                <option value="05">Mei</option>
                <option value="06">Juni</option>
                <option value="07">Juli</option>
                <option value="08">Agustus</option>
                <option value="09">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
        </div>
        <div>
            <label for="pilihTahun" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Tahun</label>
            <select id="pilihTahun" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option selected>Tahun</option>
                <?php for ($y = date('Y'); $y >= 2020; $y--) { echo "<option value='{$y}'>{$y}</option>"; } ?>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-blue-900 text-white font-medium rounded-lg text-sm">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Cari
            </button>
            <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-800 text-white font-medium rounded-lg text-sm">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12c0 2.21.892 4.202 2.388 5.618M18 19v-5h-.582m-15.356-2A8.001 8.001 0 0120 12c0-2.21-.892-4.202-2.388-5.618"></path></svg>
                Refresh
            </button>
            <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-900 text-white font-medium rounded-lg text-sm">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Excel
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mt-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Cari Laporan Per Hari</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
            <div>
                <label for="pilihHari" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Hari</label>
                <div class="relative max-w-sm">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                        </svg>
                    </div>
                    <input id="pilihHari" datepicker datepicker-buttons datepicker-autoselect-today type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Pilih tanggal">
                </div>
            </div>
            <div class="flex space-x-2">
                <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-700 dark:hover:bg-blue-800 dark:focus:ring-blue-900 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Cari
                </button>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-800 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12c0 2.21.892 4.202 2.388 5.618M18 19v-5h-.582m-15.356-2A8.001 8.001 0 0120 12c0-2.21-.892-4.202-2.388-5.618"></path></svg>
                    Refresh
                </button>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-900 text-white font-medium rounded-lg text-sm">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Excel
                </button>
            </div>
        </div>
    </div>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-6">
        <div class="pb-4 bg-white dark:bg-gray-800 p-4 rounded-t-lg flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
            <div class="flex items-center space-x-2 text-gray-500 dark:text-gray-400">
                <span>Show</span>
                <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span>entries</span>
            </div>
            <div class="w-full md:w-auto">
                <label for="table-search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" id="table-search" class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-full md:w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search for items">
                </div>
            </div>
        </div>

        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-all-search" class="sr-only">checkbox</label>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3">No <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">ID Barang <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">Nama Barang <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">Jumlah <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">Modal <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">Total <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">Kasir <button class="datatable-sorter">⇅</button></th>
                    <th scope="col" class="px-6 py-3">Tanggal Input <button class="datatable-sorter">⇅</button></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan_data)): ?>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td colspan="9" class="px-3 py-4 text-center">Tidak ada data laporan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laporan_data as $row): ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="w-4 p-4">
                                <div class="flex items-center">
                                    <input id="checkbox-table-search-<?= htmlspecialchars($row['no']) ?>" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-table-search-<?= htmlspecialchars($row['no']) ?>" class="sr-only">checkbox</label>
                                </div>
                            </td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['no']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['id_barang']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['jumlah']) ?></td>
                            <td class="px-6 py-4">Rp<?= number_format($row['modal'], 0, ',', '.') ?>,-</td>
                            <td class="px-6 py-4">Rp<?= number_format($row['total'], 0, ',', '.') ?>,-</td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['kasir']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['tanggal_input']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="bg-white dark:bg-gray-800 font-semibold text-gray-900 dark:text-white border-b dark:border-gray-700">
                    <td colspan="5" class="px-6 py-4 text-right">Total Terjual</td>
                    <td class="px-6 py-4"><?= htmlspecialchars($total_terjual_item) ?></td>
                    <td class="px-6 py-4">Rp<?= number_format($total_modal_rp, 0, ',', '.') ?>,-</td>
                    <td class="px-6 py-4">Rp<?= number_format($total_terjual_rp, 0, ',', '.') ?>,-</td>
                    <td colspan="2" class="px-6 py-4"></td> </tr>
                <tr class="bg-green-100 dark:bg-green-700 font-semibold text-green-800 dark:text-white">
                    <td colspan="7" class="px-6 py-4 text-right">Keuntungan</td>
                    <td colspan="2" class="px-6 py-4 text-right">Rp<?= number_format($keuntungan, 0, ',', '.') ?>,-</td>
                </tr>
            </tbody>
        </table>

        <nav class="flex items-center flex-col md:flex-row justify-between pt-4 mb-5 mr-4 ml-4" aria-label="Table navigation">
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:w-auto text-center md:text-left">
                Showing <span class="font-semibold text-gray-900 dark:text-white">1</span> to <span class="font-semibold text-gray-900 dark:text-white"><?= count($laporan_data) ?></span> of <span class="font-semibold text-gray-900 dark:text-white"><?= count($laporan_data) ?></span> entries
            </span>
            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Previous</a>
                </li>
                <li>
                    <a href="#" aria-current="page" class="flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white">1</a>
                </li>
                <li>
                    <a href="#" class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a>
                </li>
            </ul>
        </nav>
    </div>