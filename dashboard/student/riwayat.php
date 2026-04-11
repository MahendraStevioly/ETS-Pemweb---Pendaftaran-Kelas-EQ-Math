<?php
/**
 * Dashboard Student - Riwayat Transaksi
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('siswa');

$db = getDB();
$userId = $_SESSION['user_id'];

// Get all transaction history
$riwayat = $db->fetchAll(
    "SELECT tp.*, mk.nama_kelas, jk.hari, jk.jam_mulai, jk.jam_selesai
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    WHERE tp.user_id = ?
    ORDER BY tp.tanggal_bayar DESC",
    [$userId]
);

$pageTitle = 'Riwayat Transaksi';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-student.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <h1 class="text-3xl font-bold text-slate-900">Riwayat Transaksi</h1>
    <p class="text-slate-500 mt-1">Lihat semua riwayat transaksi pembayaran</p>
</div>

<?php showMessage(); ?>

<!-- Transaction History -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-900">Semua Transaksi</h3>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-600">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
        </div>
    </div>

    <?php if (empty($riwayat)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-inbox text-5xl text-slate-300 mb-4"></i>
            <p class="text-slate-500 font-medium text-lg">Belum ada transaksi</p>
            <p class="text-slate-400 text-sm mt-2">Transaksi pembayaran akan muncul di sini</p>
            <a href="pilih-kelas.php" class="mt-6 inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">
                <i class="fas fa-book-open mr-2"></i> Pilih Kelas Sekarang
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Order ID</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Kelas</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jadwal</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Tanggal</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jumlah</th>
                        <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $transaksi): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-primary-600"><?php echo htmlspecialchars($transaksi['order_id']); ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-medium text-slate-900"><?php echo htmlspecialchars($transaksi['nama_kelas']); ?></p>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                <?php echo htmlspecialchars($transaksi['hari']); ?>, <?php echo htmlspecialchars($transaksi['jam_mulai']); ?> - <?php echo htmlspecialchars($transaksi['jam_selesai']); ?>
                            </td>
                            <td class="py-4 px-6 text-slate-600 text-sm"><?php echo formatDateIndo($transaksi['tanggal_bayar']); ?></td>
                            <td class="py-4 px-6 font-bold text-slate-900"><?php echo formatRupiah($transaksi['jumlah_bayar']); ?></td>
                            <td class="py-4 px-6">
                                <span class="badge <?php echo getStatusBadge($transaksi['status_pembayaran']); ?>">
                                    <?php echo ucfirst($transaksi['status_pembayaran']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($riwayat); ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-receipt text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Transaksi Sukses</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count(array_filter($riwayat, fn($r) => $r['status_pembayaran'] === 'settlement')); ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Pengeluaran</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">
                    <?php
                    $total = array_sum(array_column(array_filter($riwayat, fn($r) => $r['status_pembayaran'] === 'settlement'), 'jumlah_bayar'));
                    echo formatRupiah($total);
                    ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-wallet text-xl"></i>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
