<?php
// Minimal test endpoint
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Include files
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Set JSON header
header('Content-Type: application/json');

// Test response
try {
    // Generate OTP
    $otp = generateOTP();

    // Return success
    echo json_encode([
        'status' => true,
        'message' => 'Test successful',
        'otp' => $otp,
        'otp_length' => defined('OTP_LENGTH') ? OTP_LENGTH : 'not defined',
        'dev_mode' => defined('OTP_DEV_MODE') ? OTP_DEV_MODE : 'not defined'
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
