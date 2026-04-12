<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Check Last PHP Error</h2>";
echo "<p>Error log location: " . ini_get('error_log') . "</p>";

// Try to read error log
$logFile = ini_get('error_log');
if (file_exists($logFile)) {
    $errors = file_get_contents($logFile);
    $lastErrors = explode("\n", $errors);
    $lastErrors = array_slice($lastErrors, -10); // Last 10 errors
    echo "<h3>Last 10 Errors:</h3>";
    echo "<pre>" . print_r($lastErrors, true) . "</pre>";
} else {
    echo "<p>Log file not found</p>";
}

// Direct test of API
echo "<h2>Direct API Test</h2>";
echo "<p>Testing: api/otp.php?action=request</p>";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p>Session started</p>";

// Include files
try {
    echo "<p>Including database.php...</p>";
    require_once '../config/database.php';
    echo "<p>✅ database.php OK</p>";

    echo "<p>Including functions.php...</p>";
    require_once '../includes/functions.php';
    echo "<p>✅ functions.php OK</p>";

    echo "<p>Including auth.php...</p>";
    require_once '../includes/auth.php';
    echo "<p>✅ auth.php OK</p>";

    echo "<h3>All includes successful!</h3>";

    // Test requestOTP function
    echo "<p>Testing requestOTP()...</p>";
    $result = requestOTP('test@example.com');
    echo "<pre>" . print_r($result, true) . "</pre>";

} catch (Throwable $e) {
    echo "<h3>Error:</h3>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
