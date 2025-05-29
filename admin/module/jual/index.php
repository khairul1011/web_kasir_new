<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Keranjang Penjualan</h1>

<div class="flex-1">
    <div class=" max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="rounded-lg shadow-md overflow-hidden bg-white dark:bg-gray-800">
                <div class="bg-blue-600 text-white px-4 py-3 font-semibold flex items-center rounded-t-lg">
                    <span class="mr-2">🔍</span> Cari Barang
                </div>
                <div class="p-4">
                    <input type="text" id="inputCariBarang" class="w-full border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white p-2.5 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Masukkan kode atau nama barang" />
                </div>
            </div>
            <div class="rounded-lg shadow-md overflow-hidden bg-white dark:bg-gray-800">
                <div class="bg-blue-600 text-white px-4 py-3 font-semibold flex items-center rounded-t-lg">
                    <span class="mr-2">📋</span> Hasil Pencarian
                </div>
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-2 py-3">ID</th>
                                <th scope="col" class="px-2 py-3">Nama</th>
                                <th scope="col" class="px-2 py-3">Harga</th>
                                <th scope="col" class="px-2 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tabelHasilPencarian">
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada hasil pencarian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between bg-blue-600 text-white px-4 py-3 rounded-t-lg">
            <h3 class="font-semibold">🛒 KASIR</h3>
            <form action="#" method="POST"> <button type="submit" class="bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900 text-white px-3 py-1 rounded-lg text-sm font-semibold">RESET KERANJANG</button>
            </form>
        </div>
        <div class="p-4">
            <label for="tanggal" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Tanggal</label>
            <input type="text" id="tanggal" value="29 May 2025, 05:20" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 cursor-not-allowed" readonly />

            <div class="overflow-x-auto mt-4">
                <table class="min-w-full table-auto border border-gray-200 dark:border-gray-700 text-sm text-center text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-400 uppercase">
                        <tr>
                            <th scope="col" class="px-2 py-2">No</th>
                            <th scope="col" class="px-2 py-2">Nama Barang</th>
                            <th scope="col" class="px-2 py-2">Jumlah</th>
                            <th scope="col" class="px-2 py-2">Total</th>
                            <th scope="col" class="px-2 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-2 py-2">1</td>
                            <td class="px-2 py-2">Barang A</td>
                            <td class="px-2 py-2">
                                <input type="number" value="2" min="1" class="w-16 p-1.5 border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm text-center" />
                            </td>
                            <td class="px-2 py-2">Rp50.000</td>
                            <td class="px-2 py-2">
                                <button class="font-medium text-red-600 dark:text-red-500 hover:underline text-sm">Hapus</button>
                            </td>
                        </tr>
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                            <td colspan="5" class="py-4 text-gray-500 dark:text-gray-400">Keranjang kosong (Jika tidak ada item)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div>
                    <label for="totalSemua" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Total Semua</label>
                    <input type="text" id="totalSemua" value="Rp50.000" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 cursor-not-allowed" readonly />
                </div>
                <div>
                    <label for="bayar" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Bayar</label>
                    <input type="number" id="bayar" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Masukan uang bayar" required />
                </div>
                <div class="flex flex-col">
                    <label for="kembali" class="text-sm font-medium text-gray-900 dark:text-white block mb-2">Kembali</label>
                    <input type="text" id="kembali" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500 cursor-not-allowed" readonly />
                </div>
            </div>

            <div class="mt-6 flex justify-end items-center gap-3">
                <form action="#" method="POST"> <input type="hidden" name="bayar" id="inputBayar" />
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 dark:bg-green-700 dark:hover:bg-green-800 dark:focus:ring-green-900 text-white font-medium rounded-lg text-sm">
                        <svg class="w-5 h-5 me-2 -ms-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.077 14.285a.8.8 0 011.144 0L13 10.375l-.895-.895L9.67 11.758a.8.8 0 01-1.138 0L6.685 9.758l-.895.895 2.287 2.287z"></path></svg>
                        Bayar & Cetak
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Hanya bagian JavaScript yang relevan dengan interaksi UI (tanpa PHP di dalamnya)
    // dan tanpa AJAX fetch, untuk fokus pada tampilan.
    document.addEventListener('DOMContentLoaded', function() {
        // Contoh untuk inputCariBarang, tanpa fetch ke backend
        document.getElementById('inputCariBarang').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                console.log('Cari barang:', this.value);
                // Di sini nanti akan ada logika AJAX fetch
                // Untuk demo tampilan, kita bisa isi statis atau kosongkan
                document.getElementById('tabelHasilPencarian').innerHTML = `
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                        <td class="px-2 py-2">PROD001</td>
                        <td class="px-2 py-2">Barang Contoh</td>
                        <td class="px-2 py-2">Rp25.000</td>
                        <td class="px-2 py-2">
                            <button class="font-medium text-blue-600 dark:text-blue-500 hover:underline text-sm">Add</button>
                        </td>
                    </tr>
                `;
            }
        });

        const bayarInput = document.getElementById('bayar');
        const kembaliInput = document.getElementById('kembali');
        const totalSemuaElement = document.getElementById('totalSemua');

        bayarInput.addEventListener('input', function() {
            const bayar = parseFloat(this.value);
            // Untuk demo tampilan, ambil total dari value yang sudah ada
            const totalString = totalSemuaElement.value.replace('Rp', '').replace(/\./g, '').replace(/,/g, '.');
            const total = parseFloat(totalString);

            if (!isNaN(bayar) && !isNaN(total) && bayar >= total) {
                kembaliInput.value = 'Rp' + (bayar - total).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                kembaliInput.style.color = '';
            } else if (!isNaN(bayar) && !isNaN(total) && bayar < total) {
                kembaliInput.value = 'Uang kurang!';
                kembaliInput.style.color = 'red';
            } else {
                kembaliInput.value = '';
                kembaliInput.style.color = '';
            }
        });
    });
</script>