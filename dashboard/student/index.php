<?php
/**
 * Dashboard Student - Home
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('siswa');

$db = getDB();

$userId = $_SESSION['user_id'];

// Get user active classes
$kelasAktif = $db->fetchAll(
    "SELECT mk.*, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.id as jadwal_id, mp.nama_pengajar
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    LEFT JOIN master_pengajar mp ON mp.id = jk.pengajar_id
    WHERE tp.user_id = ? AND tp.status_pembayaran = 'settlement'
    ORDER BY tp.tanggal_bayar DESC",
    [$userId]
);

// Get next class
$kelasBerikutnya = $db->fetchOne(
    "SELECT mk.*, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.id as jadwal_id, mp.nama_pengajar
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    LEFT JOIN master_pengajar mp ON mp.id = jk.pengajar_id
    WHERE tp.user_id = ? AND tp.status_pembayaran = 'settlement'
    ORDER BY tp.tanggal_bayar ASC
    LIMIT 1",
    [$userId]
);

// Get pending payments
$pembayaranPending = $db->fetchAll(
    "SELECT tp.*, mk.nama_kelas, mk.harga
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    WHERE tp.user_id = ? AND tp.status_pembayaran = 'pending'
    ORDER BY tp.tanggal_bayar DESC",
    [$userId]
);

// Get transaction history
$riwayatTransaksi = $db->fetchAll(
    "SELECT tp.*, mk.nama_kelas
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    WHERE tp.user_id = ?
    ORDER BY tp.tanggal_bayar DESC
    LIMIT 5",
    [$userId]
);

// Get available classes
$kelasTersedia = $db->fetchAll("SELECT * FROM master_kelas ORDER BY jenjang, nama_kelas");

$pageTitle = 'Dashboard Siswa';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-student.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="mb-8 fade-in">
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
            <i class="fas fa-square-root-alt text-[200px] transform translate-x-10 -translate-y-10"></i>
        </div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold mb-2">Selamat Datang, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
            <p class="text-primary-100 mb-6">Siap untuk belajar matematika hari ini? Mari tingkatkan kemampuanmu!</p>
            <?php if (!empty($kelasAktif)): ?>
                <a href="kelas-saya.php" class="inline-flex items-center px-6 py-3 bg-white text-primary-600 rounded-xl hover:bg-primary-50 transition font-semibold shadow-lg">
                    <i class="fas fa-book-reader mr-2"></i> Lanjutkan Belajar
                </a>
            <?php else: ?>
                <a href="pilih-kelas.php" class="inline-flex items-center px-6 py-3 bg-white text-primary-600 rounded-xl hover:bg-primary-50 transition font-semibold shadow-lg">
                    <i class="fas fa-book-open mr-2"></i> Pilih Kelas Sekarang
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php showMessage(); ?>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Kelas Aktif</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($kelasAktif); ?></p>
                <p class="text-sm text-slate-500 mt-2">
                    <?php echo count($kelasAktif) > 0 ? 'Sedang berjalan' : 'Belum ada kelas'; ?>
                </p>
            </div>
            <div class="w-14 h-14 bg-primary-100 text-primary-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-book-reader text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Tagihan Pending</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($pembayaranPending); ?></p>
                <p class="text-sm text-amber-600 mt-2">
                    <?php echo count($pembayaranPending) > 0 ? 'Perlu dibayar' : 'Tidak ada tagihan'; ?>
                </p>
            </div>
            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-clock text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 card-hover border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Pembayaran</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">
                    <?php
                    $total = $db->fetchOne("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM transaksi_pembayaran WHERE user_id = ? AND status_pembayaran = 'settlement'", [$userId])['total'];
                    echo $total > 0 ? formatRupiah($total) : 'Rp 0';
                    ?>
                </p>
                <p class="text-sm text-green-600 mt-2">
                    <i class="fas fa-check-circle mr-1"></i> Lunas
                </p>
            </div>
            <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<?php if ($kelasBerikutnya): ?>
<!-- Next Class -->
<div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-slate-200">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-slate-900">Kelas Berikutnya</h2>
        <a href="kelas-saya.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="border border-slate-200 rounded-xl p-5 hover:shadow-md transition">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($kelasBerikutnya['nama_kelas']); ?></h3>
                    <p class="text-slate-500"><?php echo htmlspecialchars($kelasBerikutnya['jenjang']); ?> - Pengajar: <?php echo htmlspecialchars($kelasBerikutnya['nama_pengajar'] ?? 'TBD'); ?></p>
                </div>
            </div>
            <div class="text-left md:text-right">
                <p class="font-bold text-primary-600"><?php echo htmlspecialchars($kelasBerikutnya['hari']); ?>, <?php echo htmlspecialchars($kelasBerikutnya['jam_mulai']); ?> - <?php echo htmlspecialchars($kelasBerikutnya['jam_selesai']); ?></p>
                <p class="text-sm text-slate-500"><?php echo formatTime($kelasBerikutnya['jam_mulai']); ?></p>
            </div>
        </div>
        <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100">
            <div class="flex items-center text-sm text-slate-500">
                <i class="fas fa-video mr-2"></i>
                <span>Online via Zoom</span>
            </div>
            <button class="bg-primary-600 text-white px-6 py-2.5 rounded-xl hover:bg-primary-700 transition font-medium text-sm">
                <i class="fas fa-sign-in-alt mr-2"></i> Join Kelas
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Pending Payments -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Tagihan Belum Dibayar</h2>
                <?php if (count($pembayaranPending) > 0): ?>
                    <a href="pembayaran.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($pembayaranPending)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                    <p class="text-slate-500 font-medium">Tidak ada tagihan pending</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pembayaranPending as $tagihan): ?>
                        <div class="flex items-center justify-between p-4 bg-amber-50 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($tagihan['nama_kelas']); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($tagihan['order_id']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-amber-600"><?php echo formatRupiah($tagihan['jumlah_bayar']); ?></p>
                                <a href="pembayaran.php" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Bayar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Available Classes -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Kelas Tersedia</h2>
                <a href="pilih-kelas.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($kelasTersedia)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-book text-4xl text-slate-300 mb-3"></i>
                    <p class="text-slate-500">Belum ada kelas tersedia</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($kelasTersedia, 0, 3) as $kelas): ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition cursor-pointer" onclick="window.location.href='pilih-kelas.php'">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center text-sm font-bold">
                                    <?php echo $kelas['jenjang']; ?>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo formatRupiah($kelas['harga']); ?>/bulan</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-slate-400"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200">
    <div class="p-6 border-b border-slate-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Transaksi Terakhir</h2>
            <a href="riwayat.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Invoice</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Kelas</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Tanggal</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jumlah</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayatTransaksi)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Belum ada transaksi</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatTransaksi as $transaksi): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-primary-600"><?php echo htmlspecialchars($transaksi['order_id']); ?></span>
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

<?php include '../../includes/footer.php'; ?>
