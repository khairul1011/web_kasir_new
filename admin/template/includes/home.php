<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'POS Kasir Admin'; ?></title>
    <link href="/web-kasir-new/assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">

    <?php
    // Admin Navbar akan mengandung tombol toggle sidebar
    // Path relatif dari home.php ke admin_navbar.php
    include __DIR__ . '/admin_navbar.php';
    ?>

    <?php
    // Sidebar utama
    // Path relatif dari home.php ke admin_sidebar.php
    include __DIR__ . '/admin_sidebar.php';
    ?>

    <div class="p-4 sm:ml-64">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14 bg-white dark:bg-gray-900">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Kasir</h1>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg shadow-md">
                    <p class="text-xl font-semibold text-blue-800 dark:text-blue-300">Rp24.575</p>
                    <p class="text-gray-700 dark:text-gray-300">Saldo Awal</p>
                    <span class="text-green-600 text-sm font-medium">+65%</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">Rp5.786</p>
                    <p class="text-gray-700 dark:text-gray-300">Transaksi Hari Ini</p>
                    <span class="text-green-600 text-sm font-medium">+3.47%</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">Rp57.575</p>
                    <p class="text-gray-700 dark:text-gray-300">Pengeluaran Hari Ini</p>
                    <span class="text-red-600 text-sm font-medium">-2.8%</span>
                </div>

                <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-md">
                    <p class="text-xl font-semibold text-green-800 dark:text-green-300">Rp24.575</p>
                    <p class="text-gray-700 dark:text-gray-300">Pendapatan Bersih Hari Ini</p>
                    <span class="text-green-600 text-sm font-medium">+65%</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <a href="/web-kasir-new/admin/module/jual" class="block bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Transaksi</h2>
                    <p class="text-gray-700 dark:text-gray-300">Mulai pencatatan penjualan</p>
                </a>

                <a href="/web-kasir-new/admin/module/barang" class="block bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Produk</h2>
                    <p class="text-gray-700 dark:text-gray-300">Kelola stok barang</p>
                </a>

                <a href="#" class="block bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pelanggan</h2>
                    <p class="text-gray-700 dark:text-gray-300">Lihat data pelanggan</p>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Arus Kas</h2>
                <div id="cash-flow-chart" class="w-full"></div>
            </div>

            <script>
                // Inisialisasi ApexCharts
                const getChartOptions = () => {
                    return {
                        series: [
                            {
                                name: "Pengeluaran",
                                data: [120, 140, 160, 150, 180, 170, 190], // Contoh data
                                color: "#ef4444", // Merah
                            },
                            {
                                name: "Pendapatan",
                                data: [100, 120, 140, 160, 180, 200, 220], // Contoh data
                                color: "#3b82f6", // Biru
                            },
                        ],
                        chart: {
                            height: "380px",
                            maxWidth: "100%",
                            type: "line",
                            fontFamily: "Inter, sans-serif",
                            dropShadow: {
                                enabled: false,
                            },
                            toolbar: {
                                show: false,
                            },
                        },
                        tooltip: {
                            enabled: true,
                            x: {
                                show: false,
                            },
                            y: {
                                formatter: function (val) {
                                    return "Rp" + val;
                                }
                            },
                        },
                        dataLabels: {
                            enabled: false,
                        },
                        stroke: {
                            curve: 'smooth'
                        },
                        grid: {
                            show: true,
                            strokeDashArray: 4,
                            padding: {
                                left: 2,
                                right: 2,
                                top: -20
                            },
                        },
                        legend: {
                            show: true,
                            position: "top",
                            horizontalAlign: "left",
                            fontFamily: "Inter, sans-serif",
                            markers: {
                                radius: 99,
                            },
                        },
                        xaxis: {
                            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'], // Label kategori
                            labels: {
                                show: true,
                                style: {
                                    fontFamily: "Inter, sans-serif",
                                    cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                                }
                            },
                            axisBorder: {
                                show: false,
                            },
                            axisTicks: {
                                show: false,
                            },
                        },
                        yaxis: {
                            labels: {
                                show: true,
                                formatter: function (val) {
                                    return "Rp" + val;
                                },
                                style: {
                                    fontFamily: "Inter, sans-serif",
                                    cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                                }
                            }
                        },
                    }
                }

                if (document.getElementById("cash-flow-chart") && typeof ApexCharts !== 'undefined') {
                    const chart = new ApexCharts(document.getElementById("cash-flow-chart"), getChartOptions());
                    chart.render();

                    // Mengatasi perubahan mode gelap/terang secara dinamis jika Anda menggunakan Flowbite
                    const toggleThemeBtn = document.querySelector('[data-drawer-toggle="logo-sidebar"]'); // Atau tombol toggle mode gelap Anda
                    if (toggleThemeBtn) {
                        const htmlElement = document.documentElement;
                        const observer = new MutationObserver(() => {
                            if (htmlElement.classList.contains('dark')) {
                                chart.updateOptions({
                                    xaxis: {
                                        labels: {
                                            style: {
                                                colors: '#9ca3af',
                                            }
                                        }
                                    },
                                    yaxis: {
                                        labels: {
                                            style: {
                                                colors: '#9ca3af',
                                            }
                                        }
                                    },
                                    grid: {
                                        stroke: '#374151',
                                    },
                                    tooltip: {
                                        theme: 'dark'
                                    }
                                });
                            } else {
                                chart.updateOptions({
                                    xaxis: {
                                        labels: {
                                            style: {
                                                colors: '#6b7280',
                                            }
                                        }
                                    },
                                    yaxis: {
                                        labels: {
                                            style: {
                                                colors: '#6b7280',
                                            }
                                        }
                                    },
                                    grid: {
                                        stroke: '#e5e7eb',
                                    },
                                    tooltip: {
                                        theme: 'light'
                                    }
                                });
                            }
                        });
                        observer.observe(htmlElement, { attributes: true, attributeFilter: ['class'] });
                    }
                } else {
                    console.warn("Element 'cash-flow-chart' not found or ApexCharts not loaded.");
                }
            </script>
            </div>
    </div>

    <?php
    // Admin Footer
    // Path relatif dari home.php ke admin_footer.php
    include __DIR__ . '/admin_footer.php';
    ?>

    <script src="/web-kasir-new/node_modules/flowbite/dist/flowbite.min.js"></script>
    <script src="/web-kasir-new/assets/js/main.js"></script>
</body>
</html>