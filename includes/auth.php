<?php

/**
 * Authentication Functions
 * EQ - Math - Pendaftaran Kelas Matematika
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// --- FITUR AUTO-LOGOUT ---
$timeout_duration = 300; // 300 detik = 5 menit otomatis terlempar

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Jika tidak centang "Remember Me"
    if (!isset($_COOKIE['remember_token'])) {
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];

            // Jika sudah melebihi 5 menit sejak klik terakhir
            if ($elapsed_time > $timeout_duration) {
                session_unset();
                session_destroy();
                // Tendang ke halaman login
                header("Location: " . BASE_URL . "login.php?error=Waktu habis. Silakan login kembali.");
                exit();
            }
        }
        // Catat waktu klik terakhir
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}
// -------------------------


// Login user
function login($email, $password, $remember = false)
{
    $db = getDB();

    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ?",
        [$email]
    );

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nama_lengkap'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // =====================================
        // TAMBAHKAN BAGIAN INI UNTUK REMEMBER ME
        // =====================================
        if ($remember === true) {
            // Bikin token acak untuk tiket VIP
            $token = bin2hex(random_bytes(16));

            // Simpan tiket di laptop (berlaku 30 hari)
            setcookie('remember_token', $token, time() + (86400 * 30), "/");
            setcookie('remember_user', $user['id'], time() + (86400 * 30), "/");
        }
        // =====================================

        return true;
    }

    return false;
}

// Logout user
function logout()
{
    // Clear remember token
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    // Clear session
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    return true;
}

// Check remember me token (disabled - feature not in current database schema)
function checkRememberMe()
{
    // ==========================================
    // --- FITUR AUTO-LOGIN (MEMBACA TIKET VIP) ---
    // ==========================================
    // Jika Session kosong (karena browser habis ditutup), tapi Cookie Remember Me ada
    if (!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_user'])) {
        $db = getDB();
        $userId = $_COOKIE['remember_user'];

        // Cari data user di database berdasarkan ID di dalam Cookie
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

        if ($user) {
            // Bangkitkan kembali Session-nya!
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Reset waktu aktif agar hitungan 5 menit mulai dari awal lagi
            $_SESSION['LAST_ACTIVITY'] = time();

            return true;
        }
    }
    return false;
}

// Register new user
function register($nama_lengkap, $email, $password, $no_wa = null)
{
    $db = getDB();

    // Check if email already exists
    $existing = $db->fetchOne(
        "SELECT id FROM users WHERE email = ?",
        [$email]
    );

    if ($existing) {
        return ['status' => false, 'message' => 'Email sudah terdaftar'];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $userId = $db->insert('users', [
        'nama_lengkap' => $nama_lengkap,
        'email' => $email,
        'password' => $hashedPassword,
        'no_wa' => $no_wa,
        'role' => 'siswa'
    ]);

    if ($userId) {
        return ['status' => true, 'message' => 'Registrasi berhasil', 'user_id' => $userId];
    }

    return ['status' => false, 'message' => 'Registrasi gagal'];
}

// Get current user
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDB();
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
}

// Update user profile
function updateProfile($userId, $data)
{
    $db = getDB();

    // If password is being updated, hash it
    if (isset($data['password']) && !empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
        unset($data['password']);
    }

    $result = $db->update(
        'users',
        $data,
        'id = ?',
        [$userId]
    );

    if ($result) {
        // Update session
        if (isset($data['nama_lengkap'])) {
            $_SESSION['user_name'] = $data['nama_lengkap'];
        }
        if (isset($data['email'])) {
            $_SESSION['user_email'] = $data['email'];
        }

        return ['status' => true, 'message' => 'Profil berhasil diperbarui'];
    }

    return ['status' => false, 'message' => 'Gagal memperbarui profil'];
}

// Reset password
function resetPassword($email)
{
    $db = getDB();

    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ?",
        [$email]
    );

    if (!$user) {
        return ['status' => false, 'message' => 'Email tidak ditemukan'];
    }

    // Generate new password
    $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $db->update(
        'users',
        ['password' => $hashedPassword],
        'id = ?',
        [$user['id']]
    );

    // In production, send email with new password
    // For now, just return the new password
    return ['status' => true, 'message' => 'Password berhasil direset', 'new_password' => $newPassword];
}

// Change password
function changePassword($userId, $currentPassword, $newPassword)
{
    $db = getDB();

    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ?",
        [$userId]
    );

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['status' => false, 'message' => 'Password saat ini salah'];
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $db->update(
        'users',
        ['password' => $hashedPassword],
        'id = ?',
        [$userId]
    );

    return ['status' => true, 'message' => 'Password berhasil diubah'];
}
