<?php
/**
 * OTP API Endpoints
 * EQ - Math - Pendaftaran Kelas Matematika
 */

// Start session first before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Try to include required files
try {
    require_once '../config/database.php';
    require_once '../includes/functions.php';
    require_once '../includes/auth.php';
} catch (Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Failed to load required files: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}

header('Content-Type: application/json');

// Get action
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'request':
            // Request OTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $emailOrPhone = $input['emailOrPhone'] ?? '';

            if (empty($emailOrPhone)) {
                throw new Exception('Email/Phone required', 400);
            }

            $result = requestOTP($emailOrPhone);
            echo json_encode($result);
            break;

        case 'verify':
            // Verify OTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $emailOrPhone = $input['emailOrPhone'] ?? '';
            $otp = $input['otp'] ?? '';

            if (empty($emailOrPhone) || empty($otp)) {
                throw new Exception('Email/Phone and OTP required', 400);
            }

            $result = verifyOTP($emailOrPhone, $otp);
            echo json_encode($result);
            break;

        case 'reset':
            // Reset Password
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $emailOrPhone = $input['emailOrPhone'] ?? '';
            $otp = $input['otp'] ?? '';
            $newPassword = $input['newPassword'] ?? '';

            if (empty($emailOrPhone) || empty($otp) || empty($newPassword)) {
                throw new Exception('All fields required', 400);
            }

            $result = resetPasswordWithOTP($emailOrPhone, $otp, $newPassword);
            echo json_encode($result);
            break;

        default:
            throw new Exception('Invalid action', 400);
    }
} catch (Exception $e) {
    $code = is_int($e->getCode()) ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine(),
        'exception_code' => $e->getCode()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'System error: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
}
