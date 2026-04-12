<?php

/**
 * Authentication Functions
 * EQ - Math - Pendaftaran Kelas Matematika
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// OTP CONFIGURATION
// ============================================
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_MAX_ATTEMPTS', 3);
define('OTP_LOCKOUT_MINUTES', 15);
define('OTP_DEV_MODE', true); // Set false untuk production

// Fonnte API Configuration
define('FONNTE_API_URL', 'https://api.fonnte.com/send');
define('FONNTE_TOKEN', 'YOUR_FONNTE_TOKEN'); // Ganti dengan token Fonnte Anda

// ============================================

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
        'id = :id',
        ['id' => $userId]
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
        'id = :id',
        ['id' => $user['id']]
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
        'id = :id',
        ['id' => $userId]
    );

    return ['status' => true, 'message' => 'Password berhasil diubah'];
}

// ============================================
// OTP HELPER FUNCTIONS
// ============================================

/**
 * Generate random 6-digit OTP
 */
function generateOTP() {
    return str_pad(rand(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
}

/**
 * Check if user is locked from OTP attempts
 */
function isOTPLocked($userId) {
    $db = getDB();
    $user = $db->fetchOne(
        "SELECT otp_locked_until FROM users WHERE id = ?",
        [$userId]
    );

    if (!$user || !$user['otp_locked_until']) {
        return false;
    }

    $lockedUntil = strtotime($user['otp_locked_until']);
    return $lockedUntil > time();
}

/**
 * Get remaining lock time in minutes
 */
function getOTPLockTimeRemaining($userId) {
    $db = getDB();
    $user = $db->fetchOne(
        "SELECT otp_locked_until FROM users WHERE id = ?",
        [$userId]
    );

    if (!$user || !$user['otp_locked_until']) {
        return 0;
    }

    $lockedUntil = strtotime($user['otp_locked_until']);
    $remaining = $lockedUntil - time();

    return $remaining > 0 ? ceil($remaining / 60) : 0;
}

/**
 * Increment OTP attempt counter
 */
function incrementOTPAttempts($userId) {
    $db = getDB();

    // Get current attempts
    $user = $db->fetchOne(
        "SELECT otp_attempts FROM users WHERE id = ?",
        [$userId]
    );

    $newAttempts = ($user['otp_attempts'] ?? 0) + 1;

    // Update attempts
    $db->update(
        'users',
        ['otp_attempts' => $newAttempts],
        'id = :id',
        ['id' => $userId]
    );

    // Lock account if max attempts reached
    if ($newAttempts >= OTP_MAX_ATTEMPTS) {
        $lockUntil = date('Y-m-d H:i:s', strtotime('+' . OTP_LOCKOUT_MINUTES . ' minutes'));
        $db->update(
            'users',
            ['otp_locked_until' => $lockUntil],
            'id = :id',
            ['id' => $userId]
        );
    }

    return $newAttempts;
}

/**
 * Reset OTP attempts after successful verification
 */
function resetOTPAttempts($userId) {
    $db = getDB();
    $db->update(
        'users',
        [
            'otp_attempts' => 0,
            'otp_locked_until' => null
        ],
        'id = :id',
        ['id' => $userId]
    );
}

/**
 * Normalize phone number
 */
function normalizePhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Convert +62 to 0
    if (strpos($phone, '62') === 0 && strlen($phone) > 10) {
        $phone = '0' . substr($phone, 2);
    }

    return $phone;
}

// ============================================
// MAIN OTP FUNCTIONS
// ============================================

/**
 * Request OTP for password reset
 * @param string $emailOrPhone Email or WhatsApp number
 * @return array Status and message
 */
function requestOTP($emailOrPhone) {
    $db = getDB();

    // Determine if input is email or phone
    $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);

    // Find user
    if ($isEmail) {
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$emailOrPhone]
        );
    } else {
        // Normalize phone number (remove +62 or 62, add 0)
        $normalizedPhone = normalizePhoneNumber($emailOrPhone);
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE no_wa = ?",
            [$normalizedPhone]
        );
    }

    if (!$user) {
        return [
            'status' => false,
            'message' => 'Email/nomor WhatsApp tidak ditemukan'
        ];
    }

    // Check if user is locked
    if (isOTPLocked($user['id'])) {
        $remainingTime = getOTPLockTimeRemaining($user['id']);
        return [
            'status' => false,
            'message' => "Akun terkunci sementara. Silakan coba lagi dalam {$remainingTime} menit.",
            'locked' => true,
            'remaining_minutes' => $remainingTime
        ];
    }

    // Generate OTP
    $otpCode = generateOTP();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

    // Save OTP to database
    $db->update(
        'users',
        [
            'otp_code' => $otpCode,
            'otp_expires_at' => $expiresAt,
            'otp_created_at' => date('Y-m-d H:i:s')
        ],
        'id = :id',
        ['id' => $user['id']]
    );

    // Send OTP via WhatsApp
    $sendResult = sendWhatsAppOTP($user['no_wa'], $otpCode, $user['nama_lengkap']);

    // In development mode, return OTP in response
    $response = [
        'status' => true,
        'message' => 'OTP berhasil dikirim ke WhatsApp Anda',
        'expires_in' => OTP_EXPIRY_MINUTES * 60 // in seconds
    ];

    if (OTP_DEV_MODE) {
        $response['dev_otp'] = $otpCode;
        $response['dev_mode'] = true;
    }

    if (!$sendResult['status']) {
        $response['warning'] = 'OTP generated but failed to send: ' . $sendResult['message'];
    }

    return $response;
}

/**
 * Verify OTP code
 * @param string $emailOrPhone Email or WhatsApp number
 * @param string $otp OTP code to verify
 * @return array Status and message
 */
function verifyOTP($emailOrPhone, $otp) {
    $db = getDB();

    // Determine if input is email or phone
    $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);

    // Find user
    if ($isEmail) {
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE email = ? AND otp_code IS NOT NULL",
            [$emailOrPhone]
        );
    } else {
        $normalizedPhone = normalizePhoneNumber($emailOrPhone);
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE no_wa = ? AND otp_code IS NOT NULL",
            [$normalizedPhone]
        );
    }

    if (!$user) {
        return [
            'status' => false,
            'message' => 'User tidak ditemukan atau OTP tidak diminta'
        ];
    }

    // Check if locked
    if (isOTPLocked($user['id'])) {
        $remainingTime = getOTPLockTimeRemaining($user['id']);
        return [
            'status' => false,
            'message' => "Akun terkunci. Silakan coba lagi dalam {$remainingTime} menit.",
            'locked' => true
        ];
    }

    // Check if OTP expired
    $expiresAt = strtotime($user['otp_expires_at']);
    if ($expiresAt < time()) {
        return [
            'status' => false,
            'message' => 'OTP telah kedaluwarsa. Silakan minta OTP baru.',
            'expired' => true
        ];
    }

    // Verify OTP
    if ($user['otp_code'] !== $otp) {
        $attempts = incrementOTPAttempts($user['id']);
        $remainingAttempts = OTP_MAX_ATTEMPTS - $attempts;

        if ($remainingAttempts > 0) {
            return [
                'status' => false,
                'message' => "OTP salah. Sisa percobaan: {$remainingAttempts}",
                'attempts_remaining' => $remainingAttempts
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Terlalu banyak percobaan salah. Akun terkunci sementara.',
                'locked' => true
            ];
        }
    }

    // OTP is valid - mark for password reset
    // Store temporary verification token in session
    $_SESSION['otp_verified_user'] = $user['id'];
    $_SESSION['otp_verified_time'] = time();

    // Reset attempts
    resetOTPAttempts($user['id']);

    return [
        'status' => true,
        'message' => 'OTP berhasil diverifikasi'
    ];
}

/**
 * Reset password with OTP verification
 * @param string $emailOrPhone Email or WhatsApp number
 * @param string $otp OTP code
 * @param string $newPassword New password
 * @return array Status and message
 */
function resetPasswordWithOTP($emailOrPhone, $otp, $newPassword) {
    // First verify OTP
    $verifyResult = verifyOTP($emailOrPhone, $otp);

    if (!$verifyResult['status']) {
        return $verifyResult;
    }

    // Check session verification
    if (!isset($_SESSION['otp_verified_user']) || !isset($_SESSION['otp_verified_time'])) {
        return [
            'status' => false,
            'message' => 'Sesi verifikasi kadaluwarsa. Silakan ulangi dari awal.'
        ];
    }

    // Check if session expired (10 minutes)
    if ((time() - $_SESSION['otp_verified_time']) > 600) {
        unset($_SESSION['otp_verified_user']);
        unset($_SESSION['otp_verified_time']);
        return [
            'status' => false,
            'message' => 'Sesi verifikasi kadaluwarsa. Silakan ulangi dari awal.'
        ];
    }

    $db = getDB();

    // Get user ID from session
    $userId = $_SESSION['otp_verified_user'];

    // Validate password strength
    if (strlen($newPassword) < 6) {
        return [
            'status' => false,
            'message' => 'Password minimal 6 karakter'
        ];
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password and clear OTP
    $db->update(
        'users',
        [
            'password' => $hashedPassword,
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_created_at' => null
        ],
        'id = :id',
        ['id' => $userId]
    );

    // Clear session
    unset($_SESSION['otp_verified_user']);
    unset($_SESSION['otp_verified_time']);

    return [
        'status' => true,
        'message' => 'Password berhasil direset. Silakan login dengan password baru.'
    ];
}

/**
 * Send OTP via WhatsApp using Fonnte API
 * @param string $phone WhatsApp number
 * @param string $otp OTP code
 * @param string $userName User's name
 * @return array Status and message
 */
function sendWhatsAppOTP($phone, $otp, $userName = '') {
    // Normalize phone number for Fonnte (62 format)
    $normalizedPhone = normalizePhoneNumber($phone);
    if (strpos($normalizedPhone, '0') === 0) {
        $normalizedPhone = '62' . substr($normalizedPhone, 1);
    }

    // Create message
    $greeting = $userName ? "Halo {$userName}," : "Halo,";
    $message = "{$greeting}\n\n" .
               "Kode OTP untuk reset password EQ Math Anda adalah:\n\n" .
               "*{$otp}*\n\n" .
               "Kode ini berlaku selama 5 menit. JANGAN BERIKAN kode ini kepada siapapun, termasuk pihak EQ Math.\n\n" .
               "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n" .
               "Terima kasih,\n" .
               "EQ Math Team";

    // Prepare data for Fonnte API
    $data = [
        'target' => $normalizedPhone,
        'message' => $message,
        'countryCode' => '62'
    ];

    // In development mode, log instead of sending
    if (OTP_DEV_MODE) {
        error_log("[DEV MODE] WhatsApp OTP to {$normalizedPhone}: {$otp}");
        return [
            'status' => true,
            'message' => 'OTP logged in development mode',
            'dev_mode' => true
        ];
    }

    // Send to Fonnte API
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => FONNTE_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . FONNTE_TOKEN,
            'Content-Type: application/x-www-form-urlencoded'
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return [
            'status' => false,
            'message' => 'cURL Error: ' . $err
        ];
    }

    $result = json_decode($response, true);

    if (isset($result['status']) && $result['status']) {
        return [
            'status' => true,
            'message' => 'OTP berhasil dikirim',
            'fonnte_response' => $result
        ];
    } else {
        return [
            'status' => false,
            'message' => 'Gagal mengirim OTP: ' . ($result['reason'] ?? 'Unknown error'),
            'fonnte_response' => $result
        ];
    }
}
