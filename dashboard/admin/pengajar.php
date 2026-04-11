<?php
/**
 * Dashboard Admin - Kelola Pengajar
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('admin');

$db = getDB();

// Handle actions
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tambah'])) {
        $result = $db->insert('master_pengajar', [
            'nama_pengajar' => cleanInput($_POST['nama'])
        ]);

        if ($result) {
            redirectWithMessage('pengajar.php', 'Pengajar berhasil ditambahkan');
        } else {
            redirectWithMessage('pengajar.php', 'Gagal menambahkan pengajar', 'error');
        }
    } elseif (isset($_POST['edit'])) {
        $result = $db->update(
            'master_pengajar',
            [
                'nama_pengajar' => cleanInput($_POST['nama'])
            ],
            'id = ?',
            [$_POST['id']]
        );

        if ($result) {
            redirectWithMessage('pengajar.php', 'Pengajar berhasil diperbarui');
        } else {
            redirectWithMessage('pengajar.php', 'Gagal memperbarui pengajar', 'error');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->delete('master_pengajar', 'id = ?', [$id]);
    redirectWithMessage('pengajar.php', 'Pengajar berhasil dihapus');
}

// Get all pengajar
$pengajar = $db->fetchAll("SELECT * FROM master_pengajar ORDER BY id ASC");

$pageTitle = 'Kelola Pengajar';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Data Pengajar</h1>
            <p class="text-slate-500 mt-1">Kelola data pengajar matematika</p>
        </div>
        <button onclick="openModal('tambahModal')" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium shadow-sm">
            <i class="fas fa-plus mr-2"></i> Tambah Pengajar
        </button>
    </div>
</div>

<?php showMessage(); ?>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Pengajar</p>
                <p class="text-3xl font-bold text-slate-900 mt-2"><?php echo count($pengajar); ?></p>
            </div>
            <div class="w-12 h-12 bg-primary-100 text-primary-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Pengajar Table -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <input type="text" id="searchInput" placeholder="Cari pengajar..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-slate-200 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-600">
                    <i class="fas fa-print mr-2"></i> Cetak
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="pengajarTable">
            <thead>
                <tr class="bg-slate-50">
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">No</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Nama Pengajar</th>
                    <th class="text-left py-4 px-6 font-semibold text-slate-700 text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pengajar)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-chalkboard-teacher text-5xl text-slate-300 mb-4"></i>
                                <p class="text-slate-500 font-medium">Belum ada data pengajar</p>
                                <button onclick="openModal('tambahModal')" class="mt-4 text-primary-600 hover:text-primary-700 font-medium">
                                    <i class="fas fa-plus mr-1"></i> Tambah Pengajar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pengajar as $index => $p): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition pengajar-row" data-nama="<?php echo strtolower($p['nama_pengajar']); ?>">
                            <td class="py-4 px-6 text-slate-600"><?php echo $index + 1; ?></td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold">
                                        <?php echo getInitials($p['nama_pengajar']); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($p['nama_pengajar']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <button onclick='editPengajar(<?php echo json_encode($p); ?>)' class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete('Apakah Anda yakin ingin menghapus pengajar <?php echo htmlspecialchars($p['nama_pengajar']); ?>?').then((result) => { if(result) window.location.href='pengajar.php?delete=<?php echo $p['id']; ?>'; })" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
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

<!-- Modal Tambah Pengajar -->
<div id="tambahModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg fade-in">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Tambah Pengajar Baru</h3>
            <button onclick="closeModal('tambahModal')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="pengajar.php" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Pengajar *</label>
                <input type="text" name="nama" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Contoh: Budi Santoso">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('tambahModal')" class="flex-1 px-6 py-3 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition font-medium">Batal</button>
                <button type="submit" name="tambah" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengajar -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg fade-in">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Edit Pengajar</h3>
            <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="pengajar.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Pengajar *</label>
                <input type="text" name="nama" id="editNama" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
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

    function editPengajar(pengajar) {
        document.getElementById('editId').value = pengajar.id;
        document.getElementById('editNama').value = pengajar.nama_pengajar;
        openModal('editModal');
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('.pengajar-row');

        rows.forEach(row => {
            const nama = row.getAttribute('data-nama');
            if (nama.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

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
