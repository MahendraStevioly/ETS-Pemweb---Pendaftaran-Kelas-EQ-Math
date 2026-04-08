<?php
$host     = "localhost";
$username = "root"; // Default XAMPP
$password = "";     // Default XAMPP biasanya kosong
$database = "eq_math_db";

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Mengecek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
