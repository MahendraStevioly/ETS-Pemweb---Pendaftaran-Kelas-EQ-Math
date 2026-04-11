<?php
/**
 * Dashboard Admin - Kelola Data Siswa
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('admin');

$db = getDB();

// Get all siswa
$siswa = $db->fetchAll("SELECT * FROM users WHERE role = 'siswa' ORDER BY id ASC");

$pageTitle = 'Data Siswa';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Data Siswa</h1>
            <p class="text-slate-500 mt-1">Kelola data siswa terdaftar</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-600">
            <i class="fas fa-print mr-2"></i> Cetak
        </button>
    </div>
</div>

<?php showMessage(); ?>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Siswa</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($siswa); ?></p>
            </div>
            <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Siswa Aktif</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($siswa); ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Siswa Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-900">Daftar Siswa</h3>
            <div class="relative flex-1 max-w-md">
                <input type="text" id="searchInput" placeholder="Cari siswa..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="siswaTable">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">No</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Nama Lengkap</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Email</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">No. WhatsApp</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Kelas Terdaftar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswa)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-graduate text-5xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500 font-medium">Belum ada siswa terdaftar</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswa as $index => $s): ?>
                        <?php
                        // Get kelas info for this student
                        $kelasSiswa = $db->fetchOne("SELECT mk.nama_kelas FROM transaksi_pembayaran tp
                            JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
                            JOIN master_kelas mk ON mk.id = jk.kelas_id
                            WHERE tp.user_id = ? AND tp.status_pembayaran = 'settlement'
                            ORDER BY tp.tanggal_bayar DESC LIMIT 1", [$s['id']]);
                        ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition siswa-row" data-nama="<?php echo strtolower($s['nama_lengkap']); ?>">
                            <td class="py-4 px-6 text-slate-600"><?php echo $index + 1; ?></td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold">
                                        <?php echo getInitials($s['nama_lengkap']); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($s['nama_lengkap']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-600"><?php echo htmlspecialchars($s['email']); ?></td>
                            <td class="py-4 px-6 text-slate-600"><?php echo htmlspecialchars($s['no_wa'] ?: '-'); ?></td>
                            <td class="py-4 px-6">
                                <?php if ($kelasSiswa): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                        <?php echo htmlspecialchars($kelasSiswa['nama_kelas']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('.siswa-row');

        rows.forEach(row => {
            const nama = row.getAttribute('data-nama');
            if (nama.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>
