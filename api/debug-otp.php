<?php
/**
 * Simple OTP Debug Endpoint
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to catch any errors
ob_start();

echo "<h2>OTP Debug Test</h2>";

try {
    // Step 1: Start session
    echo "<p>1. Starting session...</p>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p>✅ Session started</p>";

    // Step 2: Include files
    echo "<p>2. Including required files...</p>";

    $basePath = dirname(__DIR__);
    echo "<p>Base path: $basePath</p>";

    require_once $basePath . '/config/database.php';
    echo "<p>✅ database.php loaded</p>";

    require_once $basePath . '/includes/functions.php';
    echo "<p>✅ functions.php loaded</p>";

    require_once $basePath . '/includes/auth.php';
    echo "<p>✅ auth.php loaded</p>";

    // Step 3: Check constants
    echo "<h3>3. Checking constants...</h3>";
    $constants = ['OTP_LENGTH', 'OTP_EXPIRY_MINUTES', 'OTP_MAX_ATTEMPTS', 'OTP_DEV_MODE'];
    foreach ($constants as $const) {
        if (defined($const)) {
            echo "<p>✅ $const = " . constant($const) . "</p>";
        } else {
            echo "<p>❌ $const NOT DEFINED</p>";
        }
    }

    // Step 4: Check functions
    echo "<h3>4. Checking functions...</h3>";
    $functions = ['getDB', 'generateOTP', 'requestOTP', 'verifyOTP'];
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "<p>✅ $func() exists</p>";
        } else {
            echo "<p>❌ $func() NOT FOUND</p>";
        }
    }

    // Step 5: Test database
    echo "<h3>5. Testing database...</h3>";
    $db = getDB();
    echo "<p>✅ Database connected</p>";

    // Check if OTP columns exist
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'otp%'");
    echo "<p>OTP columns found: " . count($columns) . "</p>";
    foreach ($columns as $col) {
        echo "<p>- {$col['Field']} ({$col['Type']})</p>";
    }

    // Step 6: Test OTP generation
    echo "<h3>6. Testing OTP generation...</h3>";
    $otp = generateOTP();
    echo "<p>✅ Generated OTP: <strong>$otp</strong></p>";

    // Step 7: Test with sample data
    echo "<h3>7. Testing requestOTP()...</h3>";
    $testEmail = 'test@test.com';
    echo "<p>Testing with: $testEmail</p>";

    $result = requestOTP($testEmail);
    echo "<pre>" . print_r($result, true) . "</pre>";

    echo "<h2>✅ All tests completed!</h2>";

} catch (Throwable $e) {
    echo "<h2>❌ Error Occurred:</h2>";
    echo "<p><strong>" . get_class($e) . ": " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Get any buffered output
$output = ob_get_clean();

echo $output;
