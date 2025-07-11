<?php
$user_id = $_SESSION['user_id'];
$user_data = $view->user_edit($user_id);
?>

<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Profil Pengguna Aplikasi</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md text-center flex flex-col">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Foto Pengguna</h2>

        <?php
        $default_foto = BASE_URL . '/assets/img/profil/default.png';
        $user_foto = $user_data['foto'] ?? null;
        $foto_path = $default_foto;

        if ($user_foto && file_exists(APP_ROOT_PATH . '/assets/img/profil/' . $user_foto)) {
            $foto_path = BASE_URL . '/assets/img/profil/' . $user_foto;
        }
        ?>
        <img id="image-preview" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover bg-gray-200 dark:bg-gray-700" src="<?php echo $foto_path; ?>" alt="Foto Profil">

        <form action="fungsi/edit/edit.php?foto" method="POST" enctype="multipart/form-data" class="mt-auto">
            <input type="hidden" name="id" value="<?php echo $user_id; ?>">
            <label for="foto-upload" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 mt-4">
                Pilih File
            </label>
            <input id="foto-upload" name="foto" type="file" class="hidden" />
            <p id="file-name" class="text-sm text-gray-500 dark:text-gray-400 mt-2">Belum ada file dipilih</p>
            <button type="submit" class="mt-4 w-full text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Ganti Foto
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 grid grid-cols-1 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 border-b pb-3 dark:border-gray-700">Kelola Pengguna</h2>
            <form method="POST" action="fungsi/edit/edit.php?profil">
                <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama</label>
                        <input type="text" id="nama" name="nama" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?php echo htmlspecialchars($user_data['nama'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="nohp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Telepon</label>
                        <input type="text" id="nohp" name="nohp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" value="<?php echo htmlspecialchars($user_data['nohp'] ?? ''); ?>">
                    </div>
                    <div class="md:col-span-2 mb-4">
                        <label for="alamat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 border-b pb-3 dark:border-gray-700">Ganti Password</h2>
            <form method="POST" action="fungsi/edit/edit.php?pass">
                <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                        <input type="text" id="username" name="user" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" readonly>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password Baru</label>
                        <input type="password" id="password" name="pass" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Ubah Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('foto-upload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('file-name').textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>