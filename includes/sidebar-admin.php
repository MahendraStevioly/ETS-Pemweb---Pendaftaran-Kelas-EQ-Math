<?php
/**
 * Sidebar Admin
 * EQ - Math - Pendaftaran Kelas Matematika
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Logo -->
<div class="p-6 border-b border-primary-600">
    <a href="index.php" class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
            <i class="fas fa-square-root-alt text-primary-700 text-xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-wide">EQ - Math</h1>
            <p class="text-primary-200 text-xs">Panel Admin</p>
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
        </div>
    </div>
</div>

<!-- Navigation Menu -->
<nav class="p-4 space-y-1">
    <p class="text-primary-300 text-xs uppercase tracking-wider mb-2 px-3">Menu Utama</p>

    <a href="index.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'index.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-chart-pie w-6 text-center"></i>
        <span class="ml-3">Ringkasan</span>
    </a>

    <a href="pengajar.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'pengajar.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-chalkboard-teacher w-6 text-center"></i>
        <span class="ml-3">Data Pengajar</span>
    </a>

    <a href="kelas.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'kelas.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-book w-6 text-center"></i>
        <span class="ml-3">Data Kelas</span>
    </a>

    <a href="jadwal.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'jadwal.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-calendar-alt w-6 text-center"></i>
        <span class="ml-3">Jadwal Kelas</span>
    </a>

    <a href="pembayaran.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'pembayaran.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-credit-card w-6 text-center"></i>
        <span class="ml-3">Pembayaran</span>
    </a>

    <a href="siswa.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'siswa.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-user-graduate w-6 text-center"></i>
        <span class="ml-3">Data Siswa</span>
    </a>

    <p class="text-primary-300 text-xs uppercase tracking-wider mb-2 mt-6 px-3">Akun</p>

    <a href="pengaturan.php"
       class="nav-item flex items-center px-3 py-2.5 rounded-lg hover:bg-primary-600 transition <?php echo $currentPage == 'pengaturan.php' ? 'bg-primary-800 border-l-4 border-amber-400' : ''; ?>">
        <i class="fas fa-cog w-6 text-center"></i>
        <span class="ml-3">Pengaturan</span>
    </a>

    <a href="<?php echo BASE_URL; ?>actions/logout.php"
       class="flex items-center px-3 py-2.5 rounded-lg hover:bg-red-500 transition text-red-200 hover:text-white mt-2">
        <i class="fas fa-sign-out-alt w-6 text-center"></i>
        <span class="ml-3">Keluar</span>
    </a>
</nav>

<!-- Footer Sidebar -->
<div class="p-4 border-t border-primary-600 mt-auto">
    <p class="text-primary-300 text-xs text-center">
        &copy; <?php echo date('Y'); ?> EQ - Math
    </p>
</div>
