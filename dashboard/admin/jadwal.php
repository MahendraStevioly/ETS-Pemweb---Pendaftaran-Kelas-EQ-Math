<?php
/**
 * Dashboard Admin - Kelola Jadwal Kelas
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('admin');

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tambah'])) {
        $result = $db->insert('jadwal_kelas', [
            'kelas_id' => cleanInput($_POST['kelas_id']),
            'pengajar_id' => cleanInput($_POST['pengajar_id']),
            'hari' => cleanInput($_POST['hari']),
            'jam_mulai' => cleanInput($_POST['jam_mulai']),
            'jam_selesai' => cleanInput($_POST['jam_selesai']),
            'status' => 'upcoming'
        ]);

        if ($result) {
            redirectWithMessage('jadwal.php', 'Jadwal berhasil ditambahkan');
        } else {
            redirectWithMessage('jadwal.php', 'Gagal menambahkan jadwal', 'error');
        }
    } elseif (isset($_POST['edit'])) {
        $result = $db->update(
            'jadwal_kelas',
            [
                'kelas_id' => cleanInput($_POST['kelas_id']),
                'pengajar_id' => cleanInput($_POST['pengajar_id']),
                'hari' => cleanInput($_POST['hari']),
                'jam_mulai' => cleanInput($_POST['jam_mulai']),
                'jam_selesai' => cleanInput($_POST['jam_selesai']),
                'status' => cleanInput($_POST['status'])
            ],
            'id = ?',
            [$_POST['id']]
        );

        if ($result) {
            redirectWithMessage('jadwal.php', 'Jadwal berhasil diperbarui');
        } else {
            redirectWithMessage('jadwal.php', 'Gagal memperbarui jadwal', 'error');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->delete('jadwal_kelas', 'id = ?', [$id]);
    redirectWithMessage('jadwal.php', 'Jadwal berhasil dihapus');
}

// Get all jadwal with related data
$jadwal = $db->fetchAll("SELECT jk.*, mk.nama_kelas, mp.nama_pengajar
    FROM jadwal_kelas jk
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    JOIN master_pengajar mp ON mp.id = jk.pengajar_id
    ORDER BY jk.hari, jk.jam_mulai");

// Get data for dropdowns
$kelas = $db->fetchAll("SELECT * FROM master_kelas ORDER BY jenjang, nama_kelas");
$pengajar = $db->fetchAll("SELECT * FROM master_pengajar ORDER BY nama_pengajar");

$pageTitle = 'Kelola Jadwal Kelas';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Jadwal Kelas</h1>
            <p class="text-slate-500 mt-1">Kelola jadwal kelas matematika</p>
        </div>
        <button onclick="openModal('tambahModal')" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium shadow-sm">
            <i class="fas fa-plus mr-2"></i> Tambah Jadwal
        </button>
    </div>
</div>

<?php showMessage(); ?>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Jadwal</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($jadwal); ?></p>
            </div>
            <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-alt text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Jadwal Aktif</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count(array_filter($jadwal, fn($j) => $j['status'] === 'active')); ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Akan Datang</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count(array_filter($jadwal, fn($j) => $j['status'] === 'upcoming')); ?></p>
            </div>
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-900">Daftar Jadwal Kelas</h3>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-600">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">No</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Kelas</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Pengajar</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Hari</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Jam</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Status</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jadwal)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-alt text-5xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500 font-medium">Belum ada jadwal</p>
                                <button onclick="openModal('tambahModal')" class="mt-4 text-primary-600 hover:text-primary-700 font-medium">
                                    <i class="fas fa-plus mr-1"></i> Tambah Jadwal
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jadwal as $index => $j): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="py-4 px-6 text-slate-600"><?php echo $index + 1; ?></td>
                            <td class="py-4 px-6">
                                <span class="font-medium text-slate-900"><?php echo htmlspecialchars($j['nama_kelas']); ?></span>
                            </td>
                            <td class="py-4 px-6 text-slate-600"><?php echo htmlspecialchars($j['nama_pengajar']); ?></td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-700">
                                    <?php echo htmlspecialchars($j['hari']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                <?php echo htmlspecialchars($j['jam_mulai']); ?> - <?php echo htmlspecialchars($j['jam_selesai']); ?>
                            </td>
                            <td class="py-4 px-6">
                                <span class="badge <?php echo getStatusBadge($j['status']); ?>">
                                    <?php echo ucfirst($j['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button onclick='editJadwal(<?php echo json_encode($j); ?>)' class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete('Apakah Anda yakin ingin menghapus jadwal ini?').then((result) => { if(result) window.location.href='jadwal.php?delete=<?php echo $j['id']; ?>'; })" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Jadwal -->
<div id="tambahModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg fade-in">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Tambah Jadwal Baru</h3>
            <button onclick="closeModal('tambahModal')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="jadwal.php" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas *</label>
                <select name="kelas_id" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?> (<?php echo $k['jenjang']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Pengajar *</label>
                <select name="pengajar_id" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Pengajar</option>
                    <?php foreach ($pengajar as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_pengajar']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Hari *</label>
                <select name="hari" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Hari</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jam Mulai *</label>
                    <input type="time" name="jam_mulai" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jam Selesai *</label>
                    <input type="time" name="jam_selesai" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('tambahModal')" class="flex-1 px-6 py-3 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition font-medium">Batal</button>
                <button type="submit" name="tambah" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Jadwal -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg fade-in">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Edit Jadwal</h3>
            <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="jadwal.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Kelas *</label>
                <select name="kelas_id" id="editKelasId" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?> (<?php echo $k['jenjang']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Pengajar *</label>
                <select name="pengajar_id" id="editPengajarId" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php foreach ($pengajar as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_pengajar']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Hari *</label>
                <select name="hari" id="editHari" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jam Mulai *</label>
                    <input type="time" name="jam_mulai" id="editJamMulai" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jam Selesai *</label>
                    <input type="time" name="jam_selesai" id="editJamSelesai" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select name="status" id="editStatus" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="upcoming">Akan Datang</option>
                    <option value="active">Aktif</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('editModal')" class="flex-1 px-6 py-3 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition font-medium">Batal</button>
                <button type="submit" name="edit" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function editJadwal(jadwal) {
        document.getElementById('editId').value = jadwal.id;
        document.getElementById('editKelasId').value = jadwal.kelas_id;
        document.getElementById('editPengajarId').value = jadwal.pengajar_id;
        document.getElementById('editHari').value = jadwal.hari;
        document.getElementById('editJamMulai').value = jadwal.jam_mulai;
        document.getElementById('editJamSelesai').value = jadwal.jam_selesai;
        document.getElementById('editStatus').value = jadwal.status;
        openModal('editModal');
    }

    // Close modal on outside click
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>
