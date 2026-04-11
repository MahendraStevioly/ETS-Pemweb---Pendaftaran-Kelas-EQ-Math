<?php
/**
 * Dashboard Admin - Pengaturan
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('admin');

$db = getDB();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $result = updateProfile($_SESSION['user_id'], [
        'nama_lengkap' => cleanInput($_POST['nama_lengkap']),
        'email' => cleanInput($_POST['email']),
        'no_wa' => cleanInput($_POST['no_wa'])
    ]);

    if ($result['status']) {
        redirectWithMessage('pengaturan.php', $result['message']);
    } else {
        redirectWithMessage('pengaturan.php', $result['message'], 'error');
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $result = changePassword(
        $_SESSION['user_id'],
        $_POST['current_password'],
        $_POST['new_password']
    );

    if ($result['status']) {
        redirectWithMessage('pengaturan.php', $result['message']);
    } else {
        redirectWithMessage('pengaturan.php', $result['message'], 'error');
    }
}

// Get current admin data
$admin = getCurrentUser();

$pageTitle = 'Pengaturan';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-admin.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Pengaturan</h1>
        <p class="text-slate-500 mt-1">Kelola pengaturan akun dan sistem</p>
    </div>
</div>

<?php showMessage(); ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Profile Settings -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">Profil Admin</h3>
            <p class="text-sm text-slate-500 mt-1">Update informasi profil admin</p>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="update_profile" value="1">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" required
                    value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                <input type="email" name="email" required
                    value="<?php echo htmlspecialchars($admin['email']); ?>"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nomor WhatsApp</label>
                <input type="text" name="no_wa"
                    value="<?php echo htmlspecialchars($admin['no_wa'] ?? ''); ?>"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="081234567890">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Password Settings -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">Keamanan</h3>
            <p class="text-sm text-slate-500 mt-1">Ubah password akun admin</p>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="change_password" value="1">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Password Saat Ini *</label>
                <input type="password" name="current_password" required
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Masukkan password saat ini">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Password Baru *</label>
                <input type="password" name="new_password" required minlength="6"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Masukkan password baru (min. 6 karakter)">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password Baru *</label>
                <input type="password" name="confirm_password" required minlength="6"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Konfirmasi password baru">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium">
                    <i class="fas fa-key mr-2"></i> Ubah Password
                </button>
            </div>
        </form>
    </div>

    <!-- System Info -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">Informasi Sistem</h3>
            <p class="text-sm text-slate-500 mt-1">Informasi tentang aplikasi</p>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">Nama Aplikasi</span>
                <span class="font-semibold text-slate-900">EQ - Math</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">Versi</span>
                <span class="font-semibold text-slate-900">1.0.0</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">PHP Version</span>
                <span class="font-semibold text-slate-900"><?php echo phpversion(); ?></span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="text-slate-600">Database</span>
                <span class="font-semibold text-slate-900">MySQL</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">Aksi Cepat</h3>
            <p class="text-sm text-slate-500 mt-1">Tindakan cepat yang sering digunakan</p>
        </div>
        <div class="p-6 space-y-3">
            <a href="pengajar.php" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <span class="font-medium text-slate-900">Kelola Pengajar</span>
                </div>
                <i class="fas fa-chevron-right text-slate-400"></i>
            </a>
            <a href="kelas.php" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-book"></i>
                    </div>
                    <span class="font-medium text-slate-900">Kelola Kelas</span>
                </div>
                <i class="fas fa-chevron-right text-slate-400"></i>
            </a>
            <a href="jadwal.php" class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="font-medium text-slate-900">Kelola Jadwal</span>
                </div>
                <i class="fas fa-chevron-right text-slate-400"></i>
            </a>
            <a href="../../actions/logout.php" class="flex items-center justify-between p-4 bg-red-50 rounded-xl hover:bg-red-100 transition">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span class="font-medium text-red-600">Keluar</span>
                </div>
                <i class="fas fa-chevron-right text-red-400"></i>
            </a>
        </div>
    </div>
</div>

<script>
    // Password confirmation validation
    document.querySelector('form input[name="confirm_password"]').addEventListener('input', function() {
        const newPassword = document.querySelector('form input[name="new_password"]').value;
        const confirmPassword = this.value;

        if (newPassword !== confirmPassword) {
            this.setCustomValidity('Password tidak cocok');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>
