<?php
/**
 * Forgot Password Page with OTP Verification
 * EQ - Math - Pendaftaran Kelas Matematika
 */

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is already logged in
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: dashboard/admin/index.php');
    } else {
        header('Location: dashboard/student/index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - EQ Math</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        .step-indicator.active {
            background-color: #2563eb;
            color: white;
        }
        .step-indicator.completed {
            background-color: #10b981;
            color: white;
        }
        .connector.active {
            background-color: #2563eb;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-blue-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center space-x-3">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-square-root-alt text-3xl text-white"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-3xl font-bold text-slate-900">EQ - Math</h1>
                    <p class="text-slate-500">Platform Pendaftaran Kelas Matematika</p>
                </div>
            </a>
        </div>

        <!-- Forgot Password Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center space-x-2">
                    <div id="step1Indicator" class="step-indicator active w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">1</div>
                    <div class="w-12 h-1 bg-slate-200 rounded" id="connector1"></div>
                    <div id="step2Indicator" class="step-indicator w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm">2</div>
                    <div class="w-12 h-1 bg-slate-200 rounded" id="connector2"></div>
                    <div id="step3Indicator" class="step-indicator w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm">3</div>
                </div>
            </div>

            <!-- Error/Success Messages -->
            <div id="errorMsg" class="hidden mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700" id="errorText"></span>
            </div>

            <div id="successMsg" class="hidden mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="text-green-700" id="successText"></span>
            </div>

            <!-- Dev Mode OTP Display -->
            <div id="devModeOTP" class="hidden mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                <p class="text-yellow-800 font-semibold mb-1"><i class="fas fa-bug mr-2"></i>Development Mode</p>
                <p class="text-yellow-700">OTP Anda: <strong id="devOTPCode" class="text-xl tracking-wider"></strong></p>
                <p class="text-yellow-600 text-sm mt-1">Berlaku selama 5 menit</p>
            </div>

            <!-- STEP 1: Request OTP -->
            <div id="step1" class="step-content">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-mobile-alt text-2xl text-blue-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Lupa Password?</h2>
                    <p class="text-slate-500 mt-2">Masukkan email atau nomor WhatsApp Anda</p>
                </div>

                <form id="requestOTPForm" class="space-y-5">
                    <div>
                        <label for="emailOrPhone" class="block text-sm font-medium text-slate-700 mb-2">Email atau WhatsApp</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="text" id="emailOrPhone" required
                                class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="nama@email.com atau 08xxxxxxxxxx">
                        </div>
                    </div>

                    <button type="submit" id="requestOTPBtn" class="w-full bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition font-semibold shadow-lg shadow-blue-500/30">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim OTP
                    </button>
                    <button type="submit" id="requestOTPBtnLoading" class="hidden w-full bg-blue-400 text-white py-3 rounded-xl font-semibold shadow-lg cursor-not-allowed">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...
                    </button>
                </form>
            </div>

            <!-- STEP 2: Verify OTP -->
            <div id="step2" class="step-content hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl text-blue-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Masukkan OTP</h2>
                    <p class="text-slate-500 mt-2">Kode 6 digit telah dikirim ke WhatsApp Anda</p>
                </div>

                <form id="verifyOTPForm" class="space-y-5">
                    <div>
                        <label for="otpInput" class="block text-sm font-medium text-slate-700 mb-2">Kode OTP</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="text" id="otpInput" required maxlength="6" pattern="[0-9]{6}"
                                class="w-full pl-11 pr-4 py-3 text-center text-2xl tracking-widest border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition font-mono"
                                placeholder="------">
                        </div>
                        <p class="mt-2 text-sm text-slate-500">
                            Belum menerima?
                            <button type="button" id="resendOTP" class="text-blue-600 hover:text-blue-700 font-semibold">
                                Kirim ulang
                            </button>
                            <span id="resendCountdown" class="text-slate-400 hidden"></span>
                        </p>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" id="backToStep1" class="flex-1 bg-slate-100 text-slate-700 py-3 rounded-xl hover:bg-slate-200 transition font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </button>
                        <button type="submit" id="verifyOTPBtn" class="flex-1 bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition font-semibold shadow-lg shadow-blue-500/30">
                            Verifikasi
                        </button>
                    </div>
                </form>
            </div>

            <!-- STEP 3: Reset Password -->
            <div id="step3" class="step-content hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-2xl text-green-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Password Baru</h2>
                    <p class="text-slate-500 mt-2">Masukkan password baru Anda</p>
                </div>

                <form id="resetPasswordForm" class="space-y-5">
                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="newPassword" required minlength="6"
                                class="w-full pl-11 pr-12 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="Minimal 6 karakter">
                            <button type="button" onclick="togglePassword('newPassword', 'eyeIcon1')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="flex items-center space-x-2 text-xs">
                                <div id="lengthCheck" class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center">
                                    <i class="fas fa-check text-slate-300 text-xs"></i>
                                </div>
                                <span class="text-slate-500">Minimal 6 karakter</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="confirmPassword" required
                                class="w-full pl-11 pr-12 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="Ulangi password baru">
                            <button type="button" onclick="togglePassword('confirmPassword', 'eyeIcon2')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <i class="fas fa-eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" id="backToStep2" class="flex-1 bg-slate-100 text-slate-700 py-3 rounded-xl hover:bg-slate-200 transition font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </button>
                        <button type="submit" id="resetPasswordBtn" class="flex-1 bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition font-semibold shadow-lg shadow-blue-500/30">
                            <i class="fas fa-check mr-2"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Success State -->
            <div id="successState" class="hidden text-center py-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-4xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Password Berhasil Direset!</h2>
                <p class="text-slate-500 mb-6">Silakan login dengan password baru Anda</p>
                <a href="login.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition font-semibold shadow-lg shadow-blue-500/30">
                    <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                </a>
            </div>

            <!-- Divider -->
            <div class="my-6 flex items-center" id="divider">
                <div class="flex-1 border-t border-slate-200"></div>
                <span class="px-4 text-sm text-slate-400">atau</span>
                <div class="flex-1 border-t border-slate-200"></div>
            </div>

            <!-- Login Link -->
            <div class="text-center" id="loginLink">
                <p class="text-slate-600">Sudah ingat password?
                    <a href="login.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Masuk
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="index.php" class="inline-flex items-center text-slate-500 hover:text-slate-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        // Global state
        let currentEmailOrPhone = '';
        let currentOTP = '';
        let resendTimer = null;
        let resendCount = 0;

        // DOM Elements
        const steps = {
            step1: document.getElementById('step1'),
            step2: document.getElementById('step2'),
            step3: document.getElementById('step3')
        };
        const indicators = {
            step1: document.getElementById('step1Indicator'),
            step2: document.getElementById('step2Indicator'),
            step3: document.getElementById('step3Indicator')
        };
        const connectors = {
            connector1: document.getElementById('connector1'),
            connector2: document.getElementById('connector2')
        };

        // Show/Hide steps
        function showStep(stepNum) {
            // Hide all steps
            Object.values(steps).forEach(step => step.classList.add('hidden'));

            // Show requested step
            steps[`step${stepNum}`].classList.remove('hidden');

            // Update indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = indicators[`step${i}`];
                indicator.classList.remove('active', 'completed');
                if (i < stepNum) {
                    indicator.classList.add('completed');
                } else if (i === stepNum) {
                    indicator.classList.add('active');
                }
            }

            // Update connectors
            if (stepNum >= 2) {
                connectors.connector1.classList.add('active');
            } else {
                connectors.connector1.classList.remove('active');
            }

            if (stepNum >= 3) {
                connectors.connector2.classList.add('active');
            } else {
                connectors.connector2.classList.remove('active');
            }

            // Hide divider and login link on step 3
            if (stepNum === 3) {
                document.getElementById('divider').classList.add('hidden');
                document.getElementById('loginLink').classList.add('hidden');
            } else {
                document.getElementById('divider').classList.remove('hidden');
                document.getElementById('loginLink').classList.remove('hidden');
            }
        }

        // Show error/success messages
        function showError(message) {
            const errorMsg = document.getElementById('errorMsg');
            const errorText = document.getElementById('errorText');
            const successMsg = document.getElementById('successMsg');

            successMsg.classList.add('hidden');
            errorText.textContent = message;
            errorMsg.classList.remove('hidden');
        }

        function showSuccess(message) {
            const errorMsg = document.getElementById('errorMsg');
            const successMsg = document.getElementById('successMsg');
            const successText = document.getElementById('successText');

            errorMsg.classList.add('hidden');
            successText.textContent = message;
            successMsg.classList.remove('hidden');
        }

        function hideMessages() {
            document.getElementById('errorMsg').classList.add('hidden');
            document.getElementById('successMsg').classList.add('hidden');
        }

        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Request OTP
        document.getElementById('requestOTPForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideMessages();

            const emailOrPhone = document.getElementById('emailOrPhone').value;

            if (!emailOrPhone) {
                showError('Email atau nomor WhatsApp wajib diisi');
                return;
            }

            // Show loading
            document.getElementById('requestOTPBtn').classList.add('hidden');
            document.getElementById('requestOTPBtnLoading').classList.remove('hidden');

            try {
                const response = await fetch('api/otp.php?action=request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ emailOrPhone })
                });

                const result = await response.json();

                if (result.status) {
                    currentEmailOrPhone = emailOrPhone;
                    showSuccess(result.message);

                    // Show dev mode OTP if available
                    if (result.dev_mode && result.dev_otp) {
                        document.getElementById('devModeOTP').classList.remove('hidden');
                        document.getElementById('devOTPCode').textContent = result.dev_otp;
                    }

                    // Move to step 2
                    setTimeout(() => {
                        showStep(2);
                        startResendCountdown();
                    }, 1000);
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Terjadi kesalahan. Silakan coba lagi.');
                console.error('Error:', error);
            } finally {
                document.getElementById('requestOTPBtn').classList.remove('hidden');
                document.getElementById('requestOTPBtnLoading').classList.add('hidden');
            }
        });

        // Verify OTP
        document.getElementById('verifyOTPForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideMessages();

            const otp = document.getElementById('otpInput').value;

            if (!otp || otp.length !== 6) {
                showError('Masukkan 6 digit OTP');
                return;
            }

            try {
                const response = await fetch('api/otp.php?action=verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        emailOrPhone: currentEmailOrPhone,
                        otp: otp
                    })
                });

                const result = await response.json();

                if (result.status) {
                    currentOTP = otp;
                    showSuccess(result.message);

                    // Move to step 3
                    setTimeout(() => {
                        showStep(3);
                    }, 1000);
                } else {
                    showError(result.message);

                    // If expired, go back to step 1
                    if (result.expired) {
                        setTimeout(() => {
                            showStep(1);
                            document.getElementById('devModeOTP').classList.add('hidden');
                        }, 2000);
                    }
                }
            } catch (error) {
                showError('Terjadi kesalahan. Silakan coba lagi.');
                console.error('Error:', error);
            }
        });

        // Reset Password
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideMessages();

            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword.length < 6) {
                showError('Password minimal 6 karakter');
                return;
            }

            if (newPassword !== confirmPassword) {
                showError('Password tidak sama');
                return;
            }

            try {
                const response = await fetch('api/otp.php?action=reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        emailOrPhone: currentEmailOrPhone,
                        otp: currentOTP,
                        newPassword: newPassword
                    })
                });

                const result = await response.json();

                if (result.status) {
                    // Hide all steps and show success
                    Object.values(steps).forEach(step => step.classList.add('hidden'));
                    document.getElementById('successState').classList.remove('hidden');
                    document.getElementById('divider').classList.add('hidden');
                    document.getElementById('loginLink').classList.add('hidden');
                    document.getElementById('devModeOTP').classList.add('hidden');
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Terjadi kesalahan. Silakan coba lagi.');
                console.error('Error:', error);
            }
        });

        // Resend OTP
        document.getElementById('resendOTP').addEventListener('click', async function() {
            if (resendTimer) return;

            hideMessages();
            resendCount++;

            if (resendCount > 3) {
                showError('Terlalu banyak permintaan. Silakan tunggu beberapa saat.');
                return;
            }

            try {
                const response = await fetch('api/otp.php?action=request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ emailOrPhone: currentEmailOrPhone })
                });

                const result = await response.json();

                if (result.status) {
                    showSuccess('OTP berhasil dikirim ulang');

                    if (result.dev_mode && result.dev_otp) {
                        document.getElementById('devModeOTP').classList.remove('hidden');
                        document.getElementById('devOTPCode').textContent = result.dev_otp;
                    }

                    startResendCountdown();
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Terjadi kesalahan. Silakan coba lagi.');
                console.error('Error:', error);
            }
        });

        // Resend countdown
        function startResendCountdown() {
            let seconds = 60;
            const resendBtn = document.getElementById('resendOTP');
            const countdown = document.getElementById('resendCountdown');

            resendBtn.classList.add('hidden');
            countdown.classList.remove('hidden');

            resendTimer = setInterval(() => {
                seconds--;
                countdown.textContent = `(${seconds}s)`;

                if (seconds <= 0) {
                    clearInterval(resendTimer);
                    resendTimer = null;
                    resendBtn.classList.remove('hidden');
                    countdown.classList.add('hidden');
                }
            }, 1000);
        }

        // Back buttons
        document.getElementById('backToStep1').addEventListener('click', function() {
            showStep(1);
            document.getElementById('devModeOTP').classList.add('hidden');
        });

        document.getElementById('backToStep2').addEventListener('click', function() {
            showStep(2);
        });

        // Password strength indicator
        document.getElementById('newPassword').addEventListener('input', function() {
            const length = this.value.length;
            const check = document.getElementById('lengthCheck');

            if (length >= 6) {
                check.classList.remove('border-slate-300');
                check.classList.add('border-green-500', 'bg-green-500');
                check.querySelector('i').classList.remove('text-slate-300');
                check.querySelector('i').classList.add('text-white');
            } else {
                check.classList.remove('border-green-500', 'bg-green-500');
                check.classList.add('border-slate-300');
                check.querySelector('i').classList.remove('text-white');
                check.querySelector('i').classList.add('text-slate-300');
            }
        });

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            hideMessages();
        }, 5000);
    </script>
</body>

</html>
