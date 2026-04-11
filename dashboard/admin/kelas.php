<?php
/**
 * Dashboard Admin - Kelola Kelas
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
        $result = $db->insert('master_kelas', [
            'nama_kelas' => cleanInput($_POST['nama_kelas']),
            'jenjang' => cleanInput($_POST['jenjang']),
            'deskripsi' => cleanInput($_POST['deskripsi']),
            'harga' => cleanInput($_POST['harga'])
        ]);

        if ($result) {
            redirectWithMessage('kelas.php', 'Kelas berhasil ditambahkan');
        } else {
            redirectWithMessage('kelas.php', 'Gagal menambahkan kelas', 'error');
        }
    } elseif (isset($_POST['edit'])) {
        $result = $db->update(
            'master_kelas',
            [
                'nama_kelas' => cleanInput($_POST['nama_kelas']),
                'jenjang' => cleanInput($_POST['jenjang']),
                'deskripsi' => cleanInput($_POST['deskripsi']),
                'harga' => cleanInput($_POST['harga'])
            ],
            'id = ?',
            [$_POST['id']]
        );

        if ($result) {
            redirectWithMessage('kelas.php', 'Kelas berhasil diperbarui');
        } else {
            redirectWithMessage('kelas.php', 'Gagal memperbarui kelas', 'error');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->delete('master_kelas', 'id = ?', [$id]);
    redirectWithMessage('kelas.php', 'Kelas berhasil dihapus');
}

// Get all kelas
$kelas = $db->fetchAll("SELECT mk.*,
    (SELECT COUNT(*) FROM jadwal_kelas WHERE kelas_id = mk.id) as jumlah_jadwal,
    (SELECT COUNT(*) FROM transaksi_pembayaran tp JOIN jadwal_kelas jk ON tp.jadwal_id = jk.id WHERE jk.kelas_id = mk.id AND tp.status_pembayaran = 'settlement') as jumlah_siswa
    FROM master_kelas mk ORDER BY mk.jenjang, mk.nama_kelas");

$pageTitle = 'Kelola Kelas';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Data Kelas</h1>
            <p class="text-slate-500 mt-1">Kelola master data kelas matematika</p>
        </div>
        <button onclick="openModal('tambahModal')" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium shadow-sm">
            <i class="fas fa-plus mr-2"></i> Tambah Kelas
        </button>
    </div>
</div>

<?php showMessage(); ?>

<!-- Jenjang Filter -->
<div class="flex flex-wrap gap-3 mb-6">
    <button onclick="filterKelas('all')" class="jenjang-filter active px-5 py-2.5 rounded-xl border-2 border-primary-600 bg-primary-600 text-white font-medium transition hover:bg-primary-700">
        Semua Kelas
    </button>
    <button onclick="filterKelas('SD')" class="jenjang-filter px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 font-medium transition hover:border-primary-600 hover:text-primary-600">
        <i class="fas fa-child mr-2"></i> SD
    </button>
    <button onclick="filterKelas('SMP')" class="jenjang-filter px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 font-medium transition hover:border-primary-600 hover:text-primary-600">
        <i class="fas fa-user-graduate mr-2"></i> SMP
    </button>
    <button onclick="filterKelas('SMA')" class="jenjang-filter px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 font-medium transition hover:border-primary-600 hover:text-primary-600">
        <i class="fas fa-user-tie mr-2"></i> SMA
    </button>
</div>

<!-- Kelas Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="kelasContainer">
    <?php if (empty($kelas)): ?>
        <div class="col-span-full">
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-slate-200">
                <i class="fas fa-book text-5xl text-slate-300 mb-4"></i>
                <p class="text-slate-500 font-medium text-lg">Belum ada data kelas</p>
                <button onclick="openModal('tambahModal')" class="mt-4 inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">
                    <i class="fas fa-plus mr-2"></i> Tambah Kelas
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($kelas as $k): ?>
            <div class="kelas-card bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden card-hover" data-jenjang="<?php echo $k['jenjang']; ?>">
                <?php
                $jenjangColors = [
                    'SD' => 'from-blue-500 to-blue-600',
                    'SMP' => 'from-green-500 to-green-600',
                    'SMA' => 'from-purple-500 to-purple-600'
                ];
                $color = $jenjangColors[$k['jenjang']] ?? 'from-slate-500 to-slate-600';
                ?>
                <div class="bg-gradient-to-r <?php echo $color; ?> p-6 text-white">
                    <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-sm font-medium"><?php echo $k['jenjang']; ?></span>
                    <h3 class="text-xl font-bold mt-3"><?php echo htmlspecialchars($k['nama_kelas']); ?></h3>
                </div>
                <div class="p-6">
                    <p class="text-slate-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($k['deskripsi']); ?></p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-slate-500">
                            <i class="fas fa-users w-6 text-primary-600"></i>
                            <span><?php echo $k['jumlah_siswa']; ?> siswa terdaftar</span>
                        </div>
                        <div class="flex items-center text-sm text-slate-500">
                            <i class="fas fa-calendar w-6 text-primary-600"></i>
                            <span><?php echo $k['jumlah_jadwal']; ?> jadwal tersedia</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div>
                            <p class="text-sm text-slate-500">Harga per bulan</p>
                            <p class="text-xl font-bold text-slate-900"><?php echo formatRupiah($k['harga']); ?></p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick='editKelas(<?php echo json_encode($k); ?>)' class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete('Apakah Anda yakin ingin menghapus kelas <?php echo htmlspecialchars($k['nama_kelas']); ?>?').then((result) => { if(result) window.location.href='kelas.php?delete=<?php echo $k['id']; ?>'; })" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Tambah Kelas -->
<div id="tambahModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg fade-in">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Tambah Kelas Baru</h3>
            <button onclick="closeModal('tambahModal')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="kelas.php" method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Kelas *</label>
                <input type="text" name="nama_kelas" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Contoh: Matematika Dasar">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jenjang *</label>
                    <select name="jenjang" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Pilih Jenjang</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Harga (Rp) *</label>
                    <input type="number" name="harga" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="120000">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Deskripsi *</label>
                <textarea name="deskripsi" required rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Deskripsi singkat tentang kelas..."></textarea>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('tambahModal')" class="flex-1 px-6 py-3 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition font-medium">Batal</button>
                <button type="submit" name="tambah" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg fade-in">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Edit Kelas</h3>
            <button onclick="closeModal('editModal')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form action="kelas.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Kelas *</label>
                <input type="text" name="nama_kelas" id="editNamaKelas" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jenjang *</label>
                    <select name="jenjang" id="editJenjang" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Harga (Rp) *</label>
                    <input type="number" name="harga" id="editHarga" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Deskripsi *</label>
                <textarea name="deskripsi" id="editDeskripsi" required rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
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

    function editKelas(kelas) {
        document.getElementById('editId').value = kelas.id;
        document.getElementById('editNamaKelas').value = kelas.nama_kelas;
        document.getElementById('editJenjang').value = kelas.jenjang;
        document.getElementById('editHarga').value = kelas.harga;
        document.getElementById('editDeskripsi').value = kelas.deskripsi;
        openModal('editModal');
    }

    function filterKelas(jenjang) {
        const cards = document.querySelectorAll('.kelas-card');
        const buttons = document.querySelectorAll('.jenjang-filter');

        // Update button styles
        buttons.forEach(btn => {
            btn.classList.remove('active', 'bg-primary-600', 'text-white', 'border-primary-600');
            btn.classList.add('border-slate-200', 'text-slate-700');
        });

        const activeBtn = event.target;
        activeBtn.classList.add('active', 'bg-primary-600', 'text-white', 'border-primary-600');
        activeBtn.classList.remove('border-slate-200', 'text-slate-700');

        // Filter cards
        cards.forEach(card => {
            if (jenjang === 'all' || card.getAttribute('data-jenjang') === jenjang) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
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
