<?php
require_once 'config/database.php';
$db = getDB();

$password_baru = "admin123";
$hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

// Update langsung lewat PHP (cara paling akurat agar tidak ada spasi nyasar)
$sql = "UPDATE users SET password = ? WHERE email = ?";
$db->query($sql, [$hash_baru, 'admin@eqmath.com']);

echo "<h1>Password Telah Direset! ✅</h1>";
echo "Silakan login ke web dengan:<br>";
echo "Email: <b>admin@eqmath.com</b><br>";
echo "Password: <b>admin123</b>";
?>