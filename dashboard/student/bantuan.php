<?php
/**
 * Dashboard Student - Bantuan / Customer Service
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireRole('siswa');

$pageTitle = 'Bantuan';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-student.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <h1 class="text-3xl font-bold text-slate-900">Bantuan</h1>
    <p class="text-slate-500 mt-1">Hubungi customer service untuk bantuan</p>
</div>

<?php showMessage(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Contact Options -->
    <div class="lg:col-span-2 space-y-6">
        <!-- WhatsApp -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 bg-green-100 text-green-600 rounded-xl flex items-center justify-center">
                    <i class="fab fa-whatsapp text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">WhatsApp</h3>
                    <p class="text-slate-500">Respon cepat via WhatsApp</p>
                </div>
            </div>
            <p class="text-slate-600 mb-4">Dapatkan bantuan langsung melalui WhatsApp. Tim kami siap membantu Anda.</p>
            <a href="https://wa.me/629845659?text=Halo%20EQ-Math,%20saya%20butuh%20bantuan" target="_blank" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-medium">
                <i class="fab fa-whatsapp mr-2"></i> Chat via WhatsApp
            </a>
        </div>

        <!-- Email -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-envelope text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Email</h3>
                    <p class="text-slate-500">Kirim email untuk pertanyaan</p>
                </div>
            </div>
            <p class="text-slate-600 mb-4">Kirim pertanyaan atau kendala Anda melalui email.</p>
            <a href="mailto:support@eqmath.com" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                <i class="fas fa-envelope mr-2"></i> Kirim Email
            </a>
        </div>

        <!-- FAQ -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-xl font-bold text-slate-900 mb-4">
                <i class="fas fa-question-circle text-primary-600 mr-2"></i>
                Pertanyaan yang Sering Diajukan
            </h3>

            <div class="space-y-4">
                <div class="border border-slate-200 rounded-xl">
                    <button class="w-full flex items-center justify-between p-4 text-left" onclick="this.nextElementSibling.classList.toggle('hidden')">
                        <span class="font-medium text-slate-900">Bagaimana cara mendaftar kelas?</span>
                        <i class="fas fa-chevron-down text-slate-400"></i>
                    </button>
                    <div class="hidden p-4 pt-0 text-slate-600 text-sm">
                        Pilih menu "Pilih Kelas", pilih kelas yang diinginkan, lalu selesaikan pembayaran. Kelas akan aktif setelah pembayaran berhasil.
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl">
                    <button class="w-full flex items-center justify-between p-4 text-left" onclick="this.nextElementSibling.classList.toggle('hidden')">
                        <span class="font-medium text-slate-900">Metode pembayaran apa yang tersedia?</span>
                        <i class="fas fa-chevron-down text-slate-400"></i>
                    </button>
                    <div class="hidden p-4 pt-0 text-slate-600 text-sm">
                        Kami menerima pembayaran melalui transfer bank (BCA, Mandiri), e-wallet (GoPay, OVO), QRIS, dan pembayaran di minimarket terdekat.
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl">
                    <button class="w-full flex items-center justify-between p-4 text-left" onclick="this.nextElementSibling.classList.toggle('hidden')">
                        <span class="font-medium text-slate-900">Bagaimana jika pembayaran gagal?</span>
                        <i class="fas fa-chevron-down text-slate-400"></i>
                    </button>
                    <div class="hidden p-4 pt-0 text-slate-600 text-sm">
                        Jika pembayaran gagal, silakan coba lagi atau hubungi customer service kami melalui WhatsApp untuk bantuan lebih lanjut.
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl">
                    <button class="w-full flex items-center justify-between p-4 text-left" onclick="this.nextElementSibling.classList.toggle('hidden')">
                        <span class="font-medium text-slate-900">Apakah ada garansi?</span>
                        <i class="fas fa-chevron-down text-slate-400"></i>
                    </button>
                    <div class="hidden p-4 pt-0 text-slate-600 text-sm">
                        Ya, kami memberikan garansi kepuasan belajar. Jika tidak puas, Anda dapat meminta pengembalian dana dalam 7 hari pertama.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Info -->
    <div class="space-y-6">
        <!-- Jam Operasional -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Jam Operasional</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Senin - Jumat</span>
                    <span class="font-medium text-slate-900">08:00 - 20:00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Sabtu</span>
                    <span class="font-medium text-slate-900">09:00 - 17:00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Minggu</span>
                    <span class="font-medium text-slate-900">10:00 - 15:00</span>
                </div>
            </div>
        </div>

        <!-- Kontak Cepat -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Kontak Cepat</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-phone text-primary-600 w-5"></i>
                    <span class="text-slate-600">+62 812-3456-7890</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fas fa-envelope text-primary-600 w-5"></i>
                    <span class="text-slate-600">support@eqmath.com</span>
                </div>
                <div class="flex items-center space-x-3">
                    <i class="fab fa-instagram text-primary-600 w-5"></i>
                    <span class="text-slate-600">@eqmath.indonesia</span>
                </div>
            </div>
        </div>

        <!-- Office Location -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Lokasi Kantor</h3>
            <div class="space-y-3 text-sm">
                <p class="text-slate-600">
                    <i class="fas fa-map-marker-alt text-primary-600 w-5 mr-2"></i>
                    Jl. Pendidikan No. 123<br>
                    Jakarta Selatan, Indonesia<br>
                    12345
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
