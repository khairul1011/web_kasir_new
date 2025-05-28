<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Manajemen Barang</h1>

<div class="flex justify-between items-center mb-4">
    <p class="text-gray-700 dark:text-gray-300">Daftar semua produk yang tersedia.</p>
    <a href="/admin/module/barang/tambah" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-800">
        <svg class="me-2 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
        Tambah Barang
    </a>
</div>

<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Nama Barang</th>
                <th scope="col" class="px-6 py-3">Harga</th>
                <th scope="col" class="px-6 py-3">Stok</th>
                <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Laptop Asus ROG
                </th>
                <td class="px-6 py-4">Rp 15.000.000</td>
                <td class="px-6 py-4">10</td>
                <td class="px-6 py-4">
                    <a href="/admin/module/barang/edit?id=1" class="font-medium text-blue-600 dark:text-blue-500 hover:underline me-3">Edit</a>
                    <a href="/fungsi/hapus/hapus.php?module=barang&id=1" class="font-medium text-red-600 dark:text-red-500 hover:underline" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                </td>
            </tr>
            </tbody>
    </table>
</div>