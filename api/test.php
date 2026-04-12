<?php
/**
 * Debug Test File for OTP API
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>OTP API Debug Test</h1>";

// Test 1: Check if files exist
echo "<h2>1. File Check</h2>";
$files = [
    '../config/database.php',
    '../includes/functions.php',
    '../includes/auth.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file NOT FOUND<br>";
    }
}

// Test 2: Try to include files
echo "<h2>2. Include Test</h2>";
try {
    echo "Including database.php...<br>";
    require_once '../config/database.php';
    echo "✅ database.php OK<br>";

    echo "Including functions.php...<br>";
    require_once '../includes/functions.php';
    echo "✅ functions.php OK<br>";

    echo "Including auth.php...<br>";
    require_once '../includes/auth.php';
    echo "✅ auth.php OK<br>";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

// Test 3: Check if constants are defined
echo "<h2>3. Constants Check</h2>";
$constants = ['OTP_LENGTH', 'OTP_EXPIRY_MINUTES', 'OTP_DEV_MODE', 'FONNTE_API_URL'];
foreach ($constants as $const) {
    if (defined($const)) {
        echo "✅ $const = " . constant($const) . "<br>";
    } else {
        echo "❌ $const NOT DEFINED<br>";
    }
}

// Test 4: Check if functions exist
echo "<h2>4. Functions Check</h2>";
$functions = ['generateOTP', 'requestOTP', 'verifyOTP', 'resetPasswordWithOTP', 'sendWhatsAppOTP'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() NOT FOUND<br>";
    }
}

// Test 5: Database connection
echo "<h2>5. Database Test</h2>";
try {
    $db = getDB();
    echo "✅ Database connection OK<br>";

    // Check if OTP columns exist
    $result = $db->fetchOne("DESCRIBE users otp_code");
    if ($result) {
        echo "✅ Column otp_code exists<br>";
    } else {
        echo "❌ Column otp_code NOT FOUND<br>";
    }
} catch (Throwable $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Test 6: Test OTP generation
echo "<h2>6. OTP Generation Test</h2>";
try {
    $otp = generateOTP();
    echo "✅ Generated OTP: $otp<br>";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>✅ All Tests Complete</h2>";
