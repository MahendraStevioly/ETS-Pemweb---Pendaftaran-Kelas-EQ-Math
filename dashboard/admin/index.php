<?php

/**
 * Dashboard Admin - Home
 * EQ - Math - Pendaftaran Kelas Matematika
 */

// Start session and include required files
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check authentication
requireRole('admin');

// Get statistics
$db = getDB();

// Total siswa
$totalSiswa = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'siswa'")['total'];

// Total pengajar
$totalPengajar = $db->fetchOne("SELECT COUNT(*) as total FROM master_pengajar")['total'];

// Total kelas
$totalKelas = $db->fetchOne("SELECT COUNT(*) as total FROM master_kelas")['total'];

// 1. Pendapatan bulan ini (Kodingan aslimu)
$pendapatanBulanIni = $db->fetchOne(
    "SELECT COALESCE(SUM(tp.jumlah_bayar), 0) as total FROM transaksi_pembayaran tp
    WHERE tp.status_pembayaran = 'settlement'
    AND MONTH(tp.tanggal_bayar) = MONTH(CURRENT_DATE())
    AND YEAR(tp.tanggal_bayar) = YEAR(CURRENT_DATE())"
)['total'];

// 2. Pendapatan bulan lalu (Query tambahan)
$pendapatanBulanLalu = $db->fetchOne(
    "SELECT COALESCE(SUM(tp.jumlah_bayar), 0) as total FROM transaksi_pembayaran tp
    WHERE tp.status_pembayaran = 'settlement'
    AND MONTH(tp.tanggal_bayar) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
    AND YEAR(tp.tanggal_bayar) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)"
)['total'];

// 3. Hitung persentase kenaikan
$persentase = 0;
if ($pendapatanBulanLalu > 0) {
    $persentase = (($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100;
} elseif ($pendapatanBulanIni > 0) {
    // Jika bulan lalu 0 tapi bulan ini ada pemasukan, kita anggap kenaikan 100%
    $persentase = 100;
}

// Menghitung siswa baru di bulan dan tahun berjalan
$querySiswaBaru = "SELECT COUNT(*) as baru FROM users WHERE role = 'siswa' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$hasilSiswaBaru = $db->fetchOne($querySiswaBaru);
$siswaBaru = $hasilSiswaBaru ? $hasilSiswaBaru['baru'] : 0;

// Pendaftaran terbaru
$pendaftaranTerbaru = $db->fetchAll(
    "SELECT u.*, mk.nama_kelas, mk.harga
    FROM users u
    JOIN transaksi_pembayaran tp ON tp.user_id = u.id
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    WHERE u.role = 'siswa'
    ORDER BY tp.tanggal_bayar DESC
    LIMIT 5"
);

// Transaksi terbaru
$transaksiTerbaru = $db->fetchAll(
    "SELECT tp.*, u.nama_lengkap as nama_siswa, mk.nama_kelas
    FROM transaksi_pembayaran tp
    JOIN users u ON u.id = tp.user_id
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    ORDER BY tp.tanggal_bayar DESC
    LIMIT 5"
);

// Kelas populer
$kelasPopuler = $db->fetchAll(
    "SELECT mk.*, COUNT(tp.id) as jumlah_pendaftar,
    (SELECT COUNT(*) FROM jadwal_kelas WHERE kelas_id = mk.id) as jumlah_jadwal
    FROM master_kelas mk
    LEFT JOIN jadwal_kelas jk ON jk.kelas_id = mk.id
    LEFT JOIN transaksi_pembayaran tp ON tp.jadwal_id = jk.id AND tp.status_pembayaran = 'settlement'
    GROUP BY mk.id
    ORDER BY jumlah_pendaftar DESC
    LIMIT 5"
);

$pageTitle = 'Dashboard Admin';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Dashboard Admin</h1>
            <p class="text-slate-500 mt-1">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="pengajar.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                <i class="fas fa-plus mr-2"></i> Tambah Data
            </a>
        </div>
    </div>
</div>

<?php showMessage(); ?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Siswa -->
    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Siswa</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo number_format($totalSiswa); ?></p>
                <p class="text-sm text-green-600 mt-2">
                    <i class="fas fa-arrow-up mr-1"></i>
                    +<?php echo $siswaBaru; ?> bulan ini
                </p>
            </div>
            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-users text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Pengajar -->
    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Pengajar</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo number_format($totalPengajar); ?></p>
                <?php if ($totalPengajar > 0): ?>
                    <p class="text-sm text-green-600 mt-2">
                        <i class="fas fa-check-circle mr-1"></i>
                        Semua aktif
                    </p>
                <?php else: ?>
                    <p class="text-sm text-slate-400 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Belum ada pengajar
                    </p>
                <?php endif; ?>
            </div>
            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Kelas -->
    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Kelas</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo number_format($totalKelas); ?></p>
                <p class="text-sm text-slate-500 mt-2">
                    <i class="fas fa-book mr-1"></i>
                    <?php echo $db->fetchOne("SELECT COUNT(DISTINCT jenjang) as total FROM master_kelas")['total']; ?> jenjang
                </p>
            </div>
            <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-book-open text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Pendapatan Bulan Ini -->
    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Pendapatan Bulan Ini</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo formatRupiah($pendapatanBulanIni); ?></p>
                <?php if ($pendapatanBulanIni > 0): ?>
                    <p class="text-sm <?php echo $persentase >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-2 font-medium">
                        <i class="fas <?php echo $persentase >= 0 ? 'fa-chart-line' : 'fa-chart-bar'; ?> mr-1"></i>
                        <?php echo ($persentase >= 0 ? '+' : '') . round($persentase, 1); ?>% dari bulan lalu
                    </p>
                <?php else: ?>
                    <p class="text-sm text-slate-400 mt-2">
                        <i class="fas fa-minus-circle mr-1"></i>
                        Belum ada pemasukan
                    </p>
                <?php endif; ?>
            </div>
            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Pendaftaran Terbaru -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Pendaftaran Terbaru</h2>
                <a href="siswa.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($pendaftaranTerbaru)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-4xl text-slate-300 mb-3"></i>
                    <p class="text-slate-500">Belum ada pendaftaran</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pendaftaranTerbaru as $pendaftaran): ?>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                                    <?php echo getInitials($pendaftaran['nama_lengkap']); ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($pendaftaran['nama_kelas']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-green-600"><?php echo formatRupiah($pendaftaran['harga']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo formatDateIndo($pendaftaran['tanggal_bayar']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kelas Populer -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Kelas Populer</h2>
                <a href="kelas.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($kelasPopuler)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-book text-4xl text-slate-300 mb-3"></i>
                    <p class="text-slate-500">Belum ada kelas</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($kelasPopuler as $index => $kelas): ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm font-bold">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 text-sm"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($kelas['jenjang']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-blue-600"><?php echo $kelas['jumlah_pendaftar']; ?> siswa</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Transaksi Terbaru -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-8">
    <div class="p-6 border-b border-slate-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Transaksi Terbaru</h2>
            <a href="pembayaran.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Invoice</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Siswa</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Kelas</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Tanggal</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jumlah</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaksiTerbaru)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Belum ada transaksi</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaksiTerbaru as $transaksi): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-blue-600"><?php echo htmlspecialchars($transaksi['order_id']); ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-medium text-slate-900"><?php echo htmlspecialchars($transaksi['nama_siswa']); ?></span>
                            </td>
                            <td class="py-4 px-6 text-slate-600"><?php echo htmlspecialchars($transaksi['nama_kelas']); ?></td>
                            <td class="py-4 px-6 text-slate-600 text-sm"><?php echo formatDateIndo($transaksi['tanggal_bayar']); ?></td>
                            <td class="py-4 px-6 font-bold text-slate-900"><?php echo formatRupiah($transaksi['jumlah_bayar']); ?></td>
                            <td class="py-4 px-6">
                                <span class="badge <?php echo getStatusBadge($transaksi['status_pembayaran']); ?>">
                                    <?php echo ucfirst($transaksi['status_pembayaran']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <a href="pengajar.php?action=tambah" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm">Tambah Pengajar</p>
                <p class="text-xl font-bold mt-1">Kelola Guru</p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
        </div>
    </a>

    <a href="kelas.php?action=tambah" class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm">Tambah Kelas</p>
                <p class="text-xl font-bold mt-1">Buat Kelas Baru</p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-book text-xl"></i>
            </div>
        </div>
    </a>

    <a href="jadwal.php?action=tambah" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm">Atur Jadwal</p>
                <p class="text-xl font-bold mt-1">Kelola Jadwal</p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-alt text-xl"></i>
            </div>
        </div>
    </a>

    <a href="siswa.php" class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-6 text-white card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-amber-100 text-sm">Data Siswa</p>
                <p class="text-xl font-bold mt-1">Lihat Semua</p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
    </a>
</div>

<?php include '../../includes/footer.php'; ?>