<?php

/**
 * Sidebar Student
 * EQ - Math - Pendaftaran Kelas Matematika
 */

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['user_role'] !== 'siswa') {
    header('Location: ../index.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

// Get user active class info
$db = getDB();
$activeClass = $db->fetchOne(
    "SELECT mk.*, jk.hari, jk.jam_mulai, jk.jam_selesai FROM jadwal_kelas jk
    JOIN master_kelas mk ON jk.kelas_id = mk.id
    JOIN transaksi_pembayaran tp ON tp.jadwal_id = jk.id
    WHERE tp.user_id = ? AND tp.status_pembayaran = 'settlement'
    ORDER BY tp.tanggal_bayar DESC LIMIT 1",
    [$_SESSION['user_id']]
);
?>

<!-- Logo -->
<div class="p-6 border-b border-primary-600">
    <a href="index.php" class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
            <i class="fas fa-square-root-alt text-primary-700 text-xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-wide">EQ - Math</h1>
            <p class="text-primary-200 text-xs">Panel Siswa</p>
        </div>
    </a>
</div>

<!-- User Info -->
<div class="p-4 border-b border-primary-600">
    <div class="flex items-center space-x-3">
        <div class="w-12 h-12 bg-primary-500 rounded-full flex items-center justify-center text-lg font-bold">
            <?php echo getInitials($_SESSION['user_name']); ?>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold truncate"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            <p class="text-primary-200 text-sm truncate"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            <span class="inline-block mt-1 px-2 py-0.5 bg-green-500 text-white text-xs rounded-full">Aktif</span>
        </div>
    </div>
</div>

<!-- Navigation Menu -->
<nav class="p-4 space-y-1">
    <p class="text-primary-300 text-xs uppercase tracking-wider mb-2 px-3">Menu Utama</p>

    <a href="index.php"
        class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'index.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-home w-6 text-center"></i>
        <span class="ml-3">Beranda</span>
    </a>

    <a href="pilih-kelas.php"
        class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'pilih-kelas.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-book-open w-6 text-center"></i>
        <span class="ml-3">Pilih Kelas</span>
    </a>

    <a href="pembayaran.php"
        class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'pembayaran.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-credit-card w-6 text-center"></i>
        <span class="ml-3">Pembayaran</span>
    </a>

    <a href="riwayat.php"
        class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'riwayat.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-history w-6 text-center"></i>
        <span class="ml-3">Riwayat</span>
    </a>

    <a href="kelas-saya.php"
        class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'kelas-saya.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-graduation-cap w-6 text-center"></i>
        <span class="ml-3">Kelas Saya</span>
    </a>

    <p class="text-primary-300 text-xs uppercase tracking-wider mb-2 mt-6 px-3">Bantuan</p>

    <a href="bantuan.php"
        class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'bantuan.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fab fa-whatsapp w-6 text-center"></i>
        <span class="ml-3">Customer Service</span>
    </a>

    <a href="<?php echo BASE_URL; ?>actions/logout.php"
        class="flex items-center px-3 py-2.5 rounded-lg hover:bg-red-500 transition text-red-200 hover:text-white mt-2">
        <i class="fas fa-sign-out-alt w-6 text-center"></i>
        <span class="ml-3">Keluar</span>
    </a>
</nav>

<!-- Current Class Info -->
<?php if ($activeClass): ?>
    <div class="p-4 border-t border-primary-600 bg-primary-800/50">
        <p class="text-primary-300 text-xs uppercase tracking-wider mb-2">Kelas Aktif</p>
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-reader"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate"><?php echo htmlspecialchars($activeClass['nama_kelas']); ?></p>
                <p class="text-primary-200 text-xs"><?php echo htmlspecialchars($activeClass['hari']); ?>, <?php echo htmlspecialchars($activeClass['jam_mulai']); ?> - <?php echo htmlspecialchars($activeClass['jam_selesai']); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Footer Sidebar -->
<div class="p-4 border-t border-primary-600 mt-auto">
    <p class="text-primary-300 text-xs text-center">
        &copy; <?php echo date('Y'); ?> EQ - Math
    </p>
</div>