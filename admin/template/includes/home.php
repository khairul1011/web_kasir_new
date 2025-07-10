<?php
include INCLUDE_PATH . '/admin_header.php';
?>
<?php
// Navbar Admin
include INCLUDE_PATH . '/admin_navbar.php';
?>
<?php
// Sidebar utama
include INCLUDE_PATH . '/admin_sidebar.php';
?>

<?php


if (!isset($view)) {
    require_once __DIR__ . '/../../../config.php';
    require_once __DIR__ . '/../../../fungsi/view/view.php';
    $view = new View($db);
}


$dashboardData = $view->getDashboardData();
$chartData = $view->getChartData(); 

$chartLabels_json = json_encode($chartData['labels']);
$chartRevenue_json = json_encode($chartData['revenue']);

?>
<div class="p-4 sm:ml-64">
    <div class="p-4 dark:border-gray-700 mt-14">
        <?php

        if (!empty($_GET['page'])) {
            $page = $_GET['page'];
            $module_file = MODULE_PATH . '/' . $page . '/index.php';

            if (file_exists($module_file)) {
                include $module_file;
                $title = ucwords(str_replace('-', ' ', $page)) . ' - POS KASIR';
            } else {
                header("HTTP/1.0 404 Not Found");
                include INCLUDE_PATH . '/404.php';
                $title = 'Halaman Tidak Ditemukan - POS KASIR';
            }
        } else {
        ?>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Kasir</h1>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-blue-100 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <p class="text-2xl font-semibold text-blue-800 dark:text-white">
                        <?php echo $dashboardData['produk_terjual_hari_ini']; ?>
                    </p>
                    <p class="text-gray-700 dark:text-gray-400 mt-1">Produk Terjual (Hari ini)</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Rp <?php echo number_format($dashboardData['pendapatan_hari_ini'], 0, ',', '.'); ?>
                    </p>
                    <p class="text-gray-700 dark:text-gray-400 mt-1">Transaksi Hari Ini</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Rp <?php echo number_format($dashboardData['pendapatan_hari_ini'], 0, ',', '.'); ?>
                    </p>
                    <p class="text-gray-700 dark:text-gray-400 mt-1">Pendapatan Hari Ini</p>
                </div>

                <div class="bg-green-100 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <p class="text-2xl font-semibold text-green-800 dark:text-white">
                        Rp <?php echo number_format($dashboardData['pendapatan_bulan_ini'], 0, ',', '.'); ?>
                    </p>
                    <p class="text-gray-700 dark:text-gray-400 mt-1">Pendapatan Bulan Ini</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <a href="/web-kasir-new/index.php?page=jual" class="block bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Transaksi</h2>
                    <p class="text-gray-700 dark:text-gray-300">Mulai pencatatan penjualan</p>
                </a>

                <a href="/web-kasir-new/index.php?page=barang" class="block bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Produk</h2>
                    <p class="text-gray-700 dark:text-gray-300">Kelola stok barang</p>
                </a>

                <a href="index.php?page=user" class="block bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Profil</h2>
                    <p class="text-gray-700 dark:text-gray-300">Lihat Profil</p>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Arus Kas</h2>
                <div id="cash-flow-chart" class="w-full"></div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    if (document.getElementById("cash-flow-chart") && typeof ApexCharts !== 'undefined') {

                        const chartLabels = <?php echo $chartLabels_json; ?>;
                        const chartRevenue = <?php echo $chartRevenue_json; ?>;

                        const chartOptions = {
                            series: [{
                                name: "Pendapatan",
                                data: chartRevenue, 
                                color: "#2563eb",
                            }],
                            chart: {
                                height: "380px",
                                type: "area",
                                fontFamily: "Inter, sans-serif",
                                toolbar: {
                                    show: false
                                },
                            },
                            tooltip: {
                                enabled: true,
                                y: {
                                    formatter: function(value) {
                                        return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                },
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                curve: 'smooth'
                            },
                            xaxis: {
                                categories: chartLabels, 
                                labels: {
                                    style: {
                                        colors: "#6b7280",
                                    }
                                },
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                },
                            },
                            yaxis: {
                                labels: {
                                    style: {
                                        colors: "#6b7280",
                                    },
                                    formatter: function(val) {
                                        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                        if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'K';
                                        return 'Rp ' + val;
                                    }
                                }
                            },
                            grid: {
                                show: true,
                                strokeDashArray: 4,
                                borderColor: '#e5e7eb',
                            },
                            fill: {
                                type: "gradient",
                                gradient: {
                                    opacityFrom: 0.55,
                                    opacityTo: 0,
                                    shade: "#1C64F2",
                                    gradientToColors: ["#1C64F2"],
                                },
                            },
                        };

                        const chart = new ApexCharts(document.getElementById("cash-flow-chart"), chartOptions);
                        chart.render();

                        
                        const observer = new MutationObserver(() => {
                            // ...
                        });
                        observer.observe(document.documentElement, {
                            attributes: true,
                            attributeFilter: ['class']
                        });
                    }
                });
            </script>
        <?php
        }
        ?>
    </div>
</div>

<?php
// Admin Footer
include INCLUDE_PATH . '/admin_footer.php';
?>