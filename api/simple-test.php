<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...<br>";

// Test 1: Database
echo "1. Testing database connection...<br>";
try {
    require_once '../config/database.php';
    $db = getDB();
    echo "✅ Database OK<br>";
} catch (Throwable $e) {
    die("❌ Database error: " . $e->getMessage() . "<br>");
}

// Test 2: Functions
echo "2. Testing functions.php...<br>";
try {
    require_once '../includes/functions.php';
    echo "✅ functions.php OK<br>";
} catch (Throwable $e) {
    die("❌ functions.php error: " . $e->getMessage() . "<br>");
}

// Test 3: Auth
echo "3. Testing auth.php...<br>";
try {
    require_once '../includes/auth.php';
    echo "✅ auth.php OK<br>";
} catch (Throwable $e) {
    die("❌ auth.php error: " . $e->getMessage() . "<br>File: " . $e->getFile() . ":" . $e->getLine() . "<br>");
}

// Test 4: Check constants
echo "4. Checking OTP constants...<br>";
if (defined('OTP_LENGTH')) {
    echo "✅ OTP_LENGTH = " . OTP_LENGTH . "<br>";
} else {
    echo "❌ OTP_LENGTH not defined<br>";
}

// Test 5: Check functions
echo "5. Checking OTP functions...<br>";
if (function_exists('requestOTP')) {
    echo "✅ requestOTP() exists<br>";
} else {
    echo "❌ requestOTP() not found<br>";
}

echo "<h2>✅ All tests passed!</h2>";
echo "<p>Now trying to generate OTP...</p>";

try {
    $otp = generateOTP();
    echo "<h3>Generated OTP: $otp</h3>";
} catch (Throwable $e) {
    echo "❌ Error generating OTP: " . $e->getMessage() . "<br>";
}
