/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // Ini HARUS mencakup semua file PHP di folder admin/ yang berisi HTML/kelas Tailwind
    // Misalnya: admin/index.php (router admin), admin/module/**/*.php (semua file modul PHP)
    //             admin/template/**/*.php (semua file template PHP)
    "./admin/**/*.{html,js,php}",     // Ini sekarang mencakup semua file PHP di folder admin/
    "./admin/template/includes/home.php", // Path baru untuk layout utama
    // index.php di root (web-kasir-new/index.php) yang sekarang menjadi router utama
    "./index.php",
    // Untuk memastikan JS yang menggunakan kelas Flowbite juga dipindai
    "./assets/**/*.js",
    // Ini MUTLAK untuk mengintegrasikan styling Flowbite
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}