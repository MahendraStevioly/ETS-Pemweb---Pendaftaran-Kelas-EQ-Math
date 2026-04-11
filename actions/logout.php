<?php
/**
 * Logout Action
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();

// 1. Hapus variabel session
$_SESSION = array();

// 2. Hancurkan Tiket Reguler (Session Cookie)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// 3. Hancurkan Tiket VIP (Remember Me Cookie)
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// 4. Hancurkan Session
session_destroy();

// Redirect ke halaman login
header('Location: ../login.php');
exit();
?>