<?php
/**
 * Helper Functions
 * EQ - Math - Pendaftaran Kelas Matematika
 */

// Format tanggal Indonesia
function formatDateIndo($date) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $timestamp = strtotime($date);
    $tanggal = date('d', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);

    return $tanggal . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
}

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format waktu
function formatTime($time) {
    $parts = explode(':', $time);
    $hour = intval($parts[0]);
    $minute = $parts[1];

    $period = $hour >= 12 ? 'PM' : 'AM';
    $hour = $hour % 12;
    if ($hour == 0) $hour = 12;

    return sprintf('%02d:%02d %s', $hour, $minute, $period);
}

// Bersihkan input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate invoice number
function generateInvoice() {
    return 'INV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Upload file
function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
    $filename = $file['name'];
    $filetmp = $file['tmp_name'];
    $filesize = $file['size'];
    $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Cek tipe file
    if (!in_array($fileext, $allowedTypes)) {
        return ['status' => false, 'message' => 'Tipe file tidak diizinkan'];
    }

    // Cek ukuran file (max 2MB)
    if ($filesize > 2097152) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar (max 2MB)'];
    }

    // Generate nama file unik
    $newFilename = uniqid() . '.' . $fileext;
    $filepath = $destination . $newFilename;

    if (move_uploaded_file($filetmp, $filepath)) {
        return ['status' => true, 'filename' => $newFilename, 'filepath' => $filepath];
    } else {
        return ['status' => false, 'message' => 'Gagal mengupload file'];
    }
}

// Redirect dengan pesan
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}

// Tampilkan pesan
function showMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'success';
        $message = $_SESSION['message'];
        $alertClass = $type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
        $icon = $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        echo "<div class='border-l-4 p-4 mb-4 rounded-lg $alertClass flex items-center'>
            <i class='fas $icon mr-3'></i>
            <span>$message</span>
        </div>";

        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Pagination
function paginate($totalRecords, $perPage, $currentPage) {
    $totalPages = ceil($totalRecords / $perPage);
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total_pages' => $totalPages,
        'offset' => $offset,
        'current_page' => $currentPage,
        'per_page' => $perPage
    ];
}

// Generate pagination links
function paginationLinks($totalPages, $currentPage, $baseUrl) {
    $links = '';

    // Previous button
    if ($currentPage > 1) {
        $prev = $currentPage - 1;
        $links .= "<a href='$baseUrl?page=$prev' class='px-3 py-2 border rounded-lg hover:bg-gray-50'><i class='fas fa-chevron-left'></i></a>";
    }

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-50';
        $links .= "<a href='$baseUrl?page=$i' class='px-3 py-2 border rounded-lg $active'>$i</a>";
    }

    // Next button
    if ($currentPage < $totalPages) {
        $next = $currentPage + 1;
        $links .= "<a href='$baseUrl?page=$next' class='px-3 py-2 border rounded-lg hover:bg-gray-50'><i class='fas fa-chevron-right'></i></a>";
    }

    return $links;
}

// Get initial name
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
        if (strlen($initials) >= 2) break;
    }
    return $initials;
}

// Status badge color
function getStatusBadge($status) {
    $badges = [
        'aktif' => 'bg-green-100 text-green-700',
        'pending' => 'bg-yellow-100 text-yellow-700',
        'selesai' => 'bg-blue-100 text-blue-700',
        'batal' => 'bg-red-100 text-red-700',
        'berhasil' => 'bg-green-100 text-green-700',
        'gagal' => 'bg-red-100 text-red-700',
    ];

    return $badges[strtolower($status)] ?? 'bg-gray-100 text-gray-700';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirectWithMessage(BASE_URL . 'login.php', 'Silakan login terlebih dahulu', 'error');
    }
}

// Require role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirectWithMessage('../index.php', 'Anda tidak memiliki akses ke halaman ini', 'error');
    }
}
?>
