<?php
// 1. Panggil file library Midtrans yang dibutuhkan secara manual
require_once __DIR__ . '/Midtrans/Config.php';
require_once __DIR__ . '/Midtrans/Snap.php';
require_once __DIR__ . '/Midtrans/ApiRequestor.php';
require_once __DIR__ . '/Midtrans/Sanitizer.php';

// 2. Masukkan Kunci Akses akun Sandbox kamu di sini
\Midtrans\Config::$serverKey = 'Mid-server-N7rQ0VrGUpikExr0FI2pDZpv';
\Midtrans\Config::$clientKey = 'Mid-client-5XhHlwtG5PnRxple';

// 3. Pengaturan Lingkungan (Wajib false untuk Sandbox)
\Midtrans\Config::$isProduction = false; 
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>