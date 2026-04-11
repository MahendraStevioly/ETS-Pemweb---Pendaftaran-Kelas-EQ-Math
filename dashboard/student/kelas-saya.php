<?php
/**
 * Dashboard Student - Kelas Saya
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
$kelasSaya = $db->fetchAll(
    "SELECT tp.*, mk.*, jk.hari, jk.jam_mulai, jk.jam_selesai, jk.id as jadwal_id, mp.nama_pengajar
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    LEFT JOIN master_pengajar mp ON mp.id = jk.pengajar_id
    WHERE tp.user_id = ? AND tp.status_pembayaran = 'settlement'
    ORDER BY tp.tanggal_bayar DESC",
    [$userId]
);

$pageTitle = 'Kelas Saya';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-student.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <h1 class="text-3xl font-bold text-slate-900">Kelas Saya</h1>
    <p class="text-slate-500 mt-1">Kelas yang sedang Anda ikuti</p>
</div>

<?php showMessage(); ?>

<?php if (empty($kelasSaya)): ?>
    <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-slate-200">
        <i class="fas fa-book-open text-5xl text-slate-300 mb-4"></i>
        <p class="text-slate-500 font-medium text-lg">Belum ada kelas</p>
        <p class="text-slate-400 text-sm mt-2">Anda belum terdaftar di kelas manapun</p>
        <a href="pilih-kelas.php" class="mt-6 inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">
            <i class="fas fa-book-open mr-2"></i> Pilih Kelas Sekarang
        </a>
    </div>
<?php else: ?>
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Kelas Aktif</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($kelasSaya); ?></p>
                </div>
                <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book-reader text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Pembayaran</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">
                        <?php
                        $total = array_sum(array_column($kelasSaya, 'harga'));
                        echo formatRupiah($total);
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Jenjang</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">
                        <?php
                        $jenjangList = array_unique(array_column($kelasSaya, 'jenjang'));
                        echo implode(', ', $jenjangList);
                        ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($kelasSaya as $kelas): ?>
            <?php
            $jenjangColors = [
                'SD' => 'from-blue-500 to-blue-600',
                'SMP' => 'from-green-500 to-green-600',
                'SMA' => 'from-purple-500 to-purple-600'
            ];
            $color = $jenjangColors[$kelas['jenjang']] ?? 'from-slate-500 to-slate-600';
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden card-hover">
                <div class="bg-gradient-to-r <?php echo $color; ?> p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-sm font-medium"><?php echo $kelas['jenjang']; ?></span>
                            <h3 class="text-xl font-bold mt-3"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></h3>
                        </div>
                        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-calculator text-3xl"></i>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-slate-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($kelas['deskripsi']); ?></p>

                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-slate-500">
                            <i class="fas fa-chalkboard-teacher w-6 text-primary-600"></i>
                            <span><?php echo htmlspecialchars($kelas['nama_pengajar'] ?? 'TBD'); ?></span>
                        </div>
                        <div class="flex items-center text-sm text-slate-500">
                            <i class="fas fa-calendar w-6 text-primary-600"></i>
                            <span><?php echo htmlspecialchars($kelas['hari']); ?></span>
                        </div>
                        <div class="flex items-center text-sm text-slate-500">
                            <i class="fas fa-clock w-6 text-primary-600"></i>
                            <span><?php echo htmlspecialchars($kelas['jam_mulai']); ?> - <?php echo htmlspecialchars($kelas['jam_selesai']); ?></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div>
                            <p class="text-sm text-slate-500">Status</p>
                            <span class="badge badge-success mt-1">
                                <i class="fas fa-check-circle mr-1"></i> Aktif
                            </span>
                        </div>
                        <button class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium text-sm">
                            <i class="fas fa-sign-in-alt mr-2"></i> Join Kelas
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
