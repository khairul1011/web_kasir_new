<?php
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    // Hapus pesan dari session agar tidak tampil lagi
    unset($_SESSION['flash_message']);
}
?>

<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Kasir Penjualan</h1>

<?php if ($flash_message): ?>
<div class="p-4 mb-4 text-sm rounded-lg 
    <?php echo $flash_message['type'] === 'success' 
        ? 'bg-green-100 text-green-800 dark:bg-gray-800 dark:text-green-400' 
        : 'bg-red-100 text-red-800 dark:bg-gray-800 dark:text-red-400'; 
    ?>" 
    role="alert">
  <span class="font-medium"><?php echo htmlspecialchars($flash_message['message']); ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="rounded-lg shadow-md bg-white dark:bg-gray-800 h-fit">
        <div class="bg-blue-600 text-white px-4 py-3 font-semibold flex items-center rounded-t-lg">Cari Barang</div>
        <div class="p-4">
            <label for="inputCariBarang" class="sr-only">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input type="search" id="inputCariBarang" class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Ketik Nama Barang, lalu Enter..." />
            </div>
        </div>
    </div>
    <div class="rounded-lg shadow-md bg-white dark:bg-gray-800 h-fit">
        <div class="bg-blue-600 text-white px-4 py-3 font-semibold flex items-center rounded-t-lg">Hasil Pencarian</div>
        <div class="p-4 min-h-[150px] rounded-lg">
            <div id="tunggu"></div>
            <div id="hasil_cari" class="overflow-x-auto rounded-lg"><p class="text-center text-gray-500 rounded-lg dark:text-gray-400 py-4">Hasil pencarian akan tampil di sini.</p></div>
        </div>
    </div>
</div>

<div class="mt-6 rounded-lg shadow-md bg-white dark:bg-gray-800">
    <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 px-4 py-3 rounded-t-lg">
        <h5 class="font-semibold text-lg text-gray-900 dark:text-white">KERANJANG</h5>
        <button id="reset-keranjang-btn" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">RESET</button>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto rounded-lg">
            <table class="w-full text-sm text-center rounded-lg text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 rounded-lg uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">No</th>
                        <th scope="col" class="px-4 py-3 text-left">Nama</th>
                        <th scope="col" class="px-4 py-3 w-32">Jumlah</th>
                        <th scope="col" class="px-4 py-3 text-right">Total</th>
                        <th scope="col" class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="cart-items-body">
                    <tr><td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">Keranjang kosong</td></tr>
                </tbody>
            </table>
        </div>
        
        <hr class="my-4 border-gray-200 dark:border-gray-700">

        <form id="payment-form" method="POST" action="fungsi/tambah/tambah.php">
            <input type="hidden" name="cart_data" id="cart-data-input">
            
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="font-medium text-sm text-gray-900 dark:text-white">Total Belanja</label>
                    <input type="text" id="total-display" class="w-full p-2 border border-gray-300 rounded bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-bold text-lg" value="Rp 0" readonly>
                </div>
                <div>
                    <label class="font-medium text-sm text-gray-900 dark:text-white">Uang Bayar</label>
                    <input type="number" id="uang_bayar" class="w-full p-2 border border-gray-300 rounded bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 text-lg" placeholder="0">
                </div>
                <div>
                    <label class="font-medium text-sm text-gray-900 dark:text-white">Kembalian</label>
                    <input type="text" id="uang_kembali" class="w-full p-2 border border-gray-300 rounded bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-bold text-lg" readonly>
                </div>
            </div>
            <div class="text-right mt-4">
                <button type="submit" id="bayar-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg disabled:opacity-50" disabled>BAYAR</button>
            </div>
        </form>
    </div>
</div>


<script>
$(document).ready(function(){
    let cart = {};

    function renderCart() {
        const cartBody = $('#cart-items-body');
        cartBody.empty();
        let totalSemua = 0;
        let no = 1;

        if (Object.keys(cart).length === 0) {
            cartBody.html('<tr><td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">Keranjang kosong</td></tr>');
            $('#bayar-btn').prop('disabled', true);
        } else {
            for (const productId in cart) {
                const item = cart[productId];
                item.subtotal = item.price * item.quantity;
                totalSemua += item.subtotal;

                // String HTML ini sekarang memiliki class CSS Flowbite yang benar
                const rowHtml = `
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600" data-id="${productId}">
                        <td class="px-4 py-3">${no++}</td>
                        <td class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">${item.name}</td>
                        <td class="px-4 py-3">
                            <input type="number" class="cart-item-qty w-20 p-1.5 border border-gray-300 rounded text-sm text-center bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="${item.quantity}" min="1" data-id="${productId}">
                        </td>
                        <td class="px-4 py-3 text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                        <td class="px-4 py-3">
                            <button type="button" class="delete-item-btn font-medium text-red-600 dark:text-red-500 hover:underline" data-id="${productId}">Hapus</button>
                        </td>
                    </tr>
                `;
                cartBody.append(rowHtml);
            }
            $('#bayar-btn').prop('disabled', false);
        }
        $('#total-display').val('Rp ' + new Intl.NumberFormat('id-ID').format(totalSemua));
        // Perbarui juga nilai input hidden untuk total
        $('input[name="cart_data"]').closest('form').find('input#total-hidden').remove(); // Hapus jika ada
        $('input[name="cart_data"]').closest('form').append(`<input type="hidden" id="total-hidden" value="${totalSemua}">`);
        calculateChange();
    }

    function calculateChange() {
        let total = 0;
        for (const productId in cart) { total += cart[productId].subtotal; }
        let bayar = parseFloat($('#uang_bayar').val()) || 0;
        let kembali = bayar - total;
        if (bayar > 0 && kembali >= 0) {
            $('#uang_kembali').val('Rp ' + new Intl.NumberFormat('id-ID').format(kembali));
        } else if (bayar > 0 && kembali < 0) {
             $('#uang_kembali').val('Uang Kurang');
        } else {
            $('#uang_kembali').val('');
        }
    }

    $("#inputCariBarang").keydown(function(event){
        if(event.key === 'Enter') {
            event.preventDefault(); 
            $.ajax({
                type: "POST",
                url: "fungsi/edit/edit.php?cari_barang=yes",
                data: 'keyword='+$(this).val(),
                beforeSend: function(){ $("#tunggu").html('<p class="text-green-500">Mencari...</p>'); },
                success: function(html){ $("#tunggu").html(''); $("#hasil_cari").html(html); }
            });
        }
    });

    $(document).on('click', '.add-to-cart-btn', function() {
        const button = $(this);
        const row = button.closest('tr');
        const productId = button.data('produk-id');
        const productName = row.find('.product-name').text();
        const productPrice = parseFloat(row.find('.product-price').data('price'));
        
        if (cart[productId]) {
            cart[productId].quantity++;
        } else {
            cart[productId] = { id: productId, name: productName, price: productPrice, quantity: 1 };
        }
        renderCart();
    });
    
    $(document).on('input', '.cart-item-qty', function() {
        const productId = $(this).data('id');
        const newQuantity = parseInt($(this).val());

        if (newQuantity >= 1) {
            cart[productId].quantity = newQuantity;
        } else {
            // If user enters a number less than 1, reset it to 1
            $(this).val(1);
            cart[productId].quantity = 1;
        }
        // Re-render the cart to update totals
        renderCart();
    });

    $(document).on('click', '.delete-item-btn', function() {
        const productId = $(this).data('id');
        delete cart[productId];
        renderCart();
    });

    $('#reset-keranjang-btn').on('click', function() {
        if (confirm('Yakin ingin reset keranjang?')) {
            cart = {};
            renderCart();
        }
    });

    $('#uang_bayar').on('keyup', calculateChange);

    $('#payment-form').on('submit', function(e) {
        if (Object.keys(cart).length === 0) {
            alert('Keranjang masih kosong!');
            e.preventDefault(); 
            return;
        }
        if (!confirm('Proses transaksi sekarang?')) {
            e.preventDefault();
            return;
        }
        const cartArray = Object.values(cart);
        $('#cart-data-input').val(JSON.stringify(cartArray));
    });
});
</script>