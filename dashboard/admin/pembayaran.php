<?php
/**
 * Dashboard Admin - Kelola Pembayaran
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('admin');

$db = getDB();

// Handle update status
if (isset($_POST['update_status'])) {
    $result = $db->update(
        'transaksi_pembayaran',
        ['status_pembayaran' => cleanInput($_POST['status'])],
        'id = ?',
        [$_POST['id']]
    );

    if ($result) {
        redirectWithMessage('pembayaran.php', 'Status pembayaran berhasil diperbarui');
    } else {
        redirectWithMessage('pembayaran.php', 'Gagal memperbarui status', 'error');
    }
}

// Get all transaksi with related data
$transaksi = $db->fetchAll("SELECT tp.*, u.nama_lengkap, u.email, mk.nama_kelas, jk.hari, jk.jam_mulai, jk.jam_selesai
    FROM transaksi_pembayaran tp
    JOIN users u ON u.id = tp.user_id
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    ORDER BY tp.tanggal_bayar DESC");

// Calculate statistics
$totalPendapatan = $db->fetchOne("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM transaksi_pembayaran WHERE status_pembayaran = 'settlement'")['total'];
$pendingCount = count(array_filter($transaksi, fn($t) => $t['status_pembayaran'] === 'pending'));
$settlementCount = count(array_filter($transaksi, fn($t) => $t['status_pembayaran'] === 'settlement'));

$pageTitle = 'Kelola Pembayaran';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Data Pembayaran</h1>
            <p class="text-slate-500 mt-1">Kelola transaksi pembayaran siswa</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-600">
            <i class="fas fa-print mr-2"></i> Cetak Laporan
        </button>
    </div>
</div>

<?php showMessage(); ?>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo formatRupiah($totalPendapatan); ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-wallet text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Pembayaran Sukses</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo $settlementCount; ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Menunggu Konfirmasi</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo $pendingCount; ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Transaksi Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <h3 class="text-lg font-semibold text-slate-900">Daftar Transaksi</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Order ID</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Siswa</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Kelas</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jadwal</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Tanggal</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jumlah</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Status</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaksi)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-receipt text-5xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500 font-medium">Belum ada transaksi</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaksi as $t): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-primary-600"><?php echo htmlspecialchars($t['order_id']); ?></span>
                            </td>
                            <td class="py-4 px-6">
                                <div>
                                    <p class="font-medium text-slate-900"><?php echo htmlspecialchars($t['nama_lengkap']); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($t['email']); ?></p>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-600"><?php echo htmlspecialchars($t['nama_kelas']); ?></td>
                            <td class="py-4 px-6 text-slate-600">
                                <?php echo htmlspecialchars($t['hari']); ?>, <?php echo htmlspecialchars($t['jam_mulai']); ?> - <?php echo htmlspecialchars($t['jam_selesai']); ?>
                            </td>
                            <td class="py-4 px-6 text-slate-600 text-sm"><?php echo formatDateIndo($t['tanggal_bayar']); ?></td>
                            <td class="py-4 px-6 font-bold text-slate-900"><?php echo formatRupiah($t['jumlah_bayar']); ?></td>
                            <td class="py-4 px-6">
                                <span class="badge <?php echo getStatusBadge($t['status_pembayaran']); ?>">
                                    <?php echo ucfirst($t['status_pembayaran']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <?php if ($t['status_pembayaran'] === 'pending'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="px-3 py-1 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                            <option value="pending" <?php echo $t['status_pembayaran'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="settlement" <?php echo $t['status_pembayaran'] === 'settlement' ? 'selected' : ''; ?>>Settlement</option>
                                            <option value="cancel" <?php echo $t['status_pembayaran'] === 'cancel' ? 'selected' : ''; ?>>Cancel</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                <?php else: ?>
                                    <span class="text-sm text-slate-400"><?php echo ucfirst($t['status_pembayaran']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
