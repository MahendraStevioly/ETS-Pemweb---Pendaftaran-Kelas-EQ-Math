<?php

/**
 * Dashboard Student - Pembayaran
 * EQ - Math - Pendaftaran Kelas Matematika
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// ======================================================
// 1. MINTA TIKET (SNAP TOKEN) KE MIDTRANS
// ======================================================
$snapToken = "";
$serverKey = "Mid-server-N7rQ0VrGUpikExr0FI2pDZpv"; // <-- GANTI DENGAN SERVER KEY MIDTRANS-MU

// Siapkan detail pesanan (Ubah nilainya sesuai variabel di file kamu)
// Catatan: Pastikan $total_bayar bentuknya angka bulat, misal 152500 (tanpa titik/koma)
$order_id = "EQ-" . time();
$total_bayar = 152500; // <--- UBAH INI dengan variabel total bayar kelasmu (contoh: $kelasInfo['harga'] + 2500)

// Data yang dikirim ke Midtrans
$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $total_bayar,
    ],
    'customer_details' => [
        // Ambil dari session jika ada, kalau tidak pakai default
        'first_name' => $_SESSION['user_name'] ?? 'Siswa',
        'email'      => $_SESSION['user_email'] ?? 'siswa@example.com',
    ]
];

// Proses menembak API Midtrans menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://app.sandbox.midtrans.com/snap/v1/transactions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($serverKey . ':') // Perhatikan titik dua di akhir
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

$response = curl_exec($ch);
curl_close($ch);

// Ambil token dari balasan Midtrans
if ($response) {
    $result = json_decode($response);
    if (isset($result->token)) {
        $snapToken = $result->token; // Hore! Tiket berhasil didapatkan
    }
}
// ======================================================
// BATAS AKHIR KODINGAN MIDTRANS
// ======================================================

requireRole('siswa');

$db = getDB();
$userId = $_SESSION['user_id'];

// Get kelas_id from URL
$kelasId = $_GET['kelas_id'] ?? null;

// Get pending payments
$pembayaranPending = $db->fetchAll(
    "SELECT tp.*, mk.nama_kelas, mk.harga, mk.jenjang
    FROM transaksi_pembayaran tp
    JOIN jadwal_kelas jk ON jk.id = tp.jadwal_id
    JOIN master_kelas mk ON mk.id = jk.kelas_id
    WHERE tp.user_id = ? AND tp.status_pembayaran = 'pending'
    ORDER BY tp.tanggal_bayar DESC",
    [$userId]
);

// If kelas_id is provided, show registration payment
$kelasInfo = null;
if ($kelasId) {
    $kelasInfo = $db->fetchOne("SELECT * FROM master_kelas WHERE id = ?", [$kelasId]);
}

$pageTitle = 'Pembayaran';
$showSidebar = true;
$sidebarFile = '../../includes/sidebar-student.php';
$showMobileMenu = true;

include '../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8 fade-in">
    <h1 class="text-3xl font-bold text-slate-900">Pembayaran</h1>
    <p class="text-slate-500 mt-1">Selesaikan pembayaran untuk mengaktifkan kelas</p>
</div>

<?php showMessage(); ?>

<?php if ($kelasInfo): ?>
    <!-- New Registration Payment -->
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-slate-200">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Pendaftaran Kelas Baru</h2>
                <p class="text-slate-500">Selesaikan pembayaran untuk mengaktifkan kelas</p>
            </div>
        </div>

        <div class="border border-slate-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center text-white">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($kelasInfo['nama_kelas']); ?></h3>
                    <p class="text-slate-500"><?php echo htmlspecialchars($kelasInfo['jenjang']); ?> - Paket Bulanan</p>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 mb-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Harga kelas</span>
                        <span class="font-semibold text-slate-900"><?php echo formatRupiah($kelasInfo['harga']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Biaya admin</span>
                        <span class="font-semibold text-slate-900"><?php echo formatRupiah(2500); ?></span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-slate-200">
                        <span class="font-bold text-slate-900">Total Pembayaran</span>
                        <span class="font-bold text-primary-600 text-xl"><?php echo formatRupiah($kelasInfo['harga'] + 2500); ?></span>
                    </div>
                </div>
            </div>

            <h4 class="font-semibold text-slate-900 mb-3">Pilih Jadwal</h4>

            <?php
            // Get available schedules for this class
            $jadwalList = $db->fetchAll(
                "SELECT jk.*, mp.nama_pengajar
            FROM jadwal_kelas jk
            LEFT JOIN master_pengajar mp ON mp.id = jk.pengajar_id
            WHERE jk.kelas_id = ?
            ORDER BY jk.hari, jk.jam_mulai",
                [$kelasId]
            );
            ?>

            <?php if (empty($jadwalList)): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <p class="text-amber-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Belum ada jadwal tersedia untuk kelas ini.
                    </p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                    <?php foreach ($jadwalList as $jadwal): ?>
                        <label class="border-2 border-slate-200 rounded-xl p-4 cursor-pointer hover:border-primary-600 transition has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                            <input type="radio" name="jadwal_id" value="<?php echo $jadwal['id']; ?>" required class="sr-only">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($jadwal['hari']); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($jadwal['jam_mulai']); ?> - <?php echo htmlspecialchars($jadwal['jam_selesai']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-primary-600"><?php echo htmlspecialchars($jadwal['nama_pengajar'] ?? 'TBD'); ?></p>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <form id="form-daftar-baru" action="../../actions/proses_pembayaran.php" method="POST">
                    <input type="hidden" name="kelas_id" value="<?php echo $kelasInfo['id']; ?>">
                    <button type="button" id="btn-daftar-baru" class="w-full bg-primary-600 text-white py-4 rounded-xl hover:bg-primary-700 transition font-semibold text-lg">
                        <i class="fas fa-lock mr-2"></i> Lanjutkan Pembayaran
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Pending Payments -->
<?php if (!empty($pembayaranPending)): ?>
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-slate-200">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Tagihan Belum Dibayar</h2>
                <p class="text-slate-500">Selesaikan pembayaran yang tertunda</p>
            </div>
            <span class="badge badge-warning">
                <i class="fas fa-clock mr-1"></i> <?php echo count($pembayaranPending); ?> tagihan
            </span>
        </div>

        <div class="space-y-4">
            <?php foreach ($pembayaranPending as $tagihan): ?>
                <div class="border border-amber-200 rounded-xl p-5 bg-amber-50">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-receipt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900"><?php echo htmlspecialchars($tagihan['nama_kelas']); ?></h3>
                                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($tagihan['order_id']); ?></p>
                            </div>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-2xl font-bold text-amber-600"><?php echo formatRupiah($tagihan['jumlah_bayar']); ?></p>
                            <p class="text-sm text-slate-500">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo formatDateIndo($tagihan['tanggal_bayar']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4 mt-4 border-t border-amber-200">
                        <p class="text-sm text-amber-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pembayaran akan segera diproses setelah dikonfirmasi
                        </p>
                        <form action="../../actions/proses_pembayaran.php" method="POST">
                            <input type="hidden" name="jadwal_id" value="<?php echo $tagihan['jadwal_id']; ?>">
                            <button type="submit" class="px-6 py-2.5 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition font-medium">
                                <i class="fas fa-credit-card mr-2"></i> Bayar Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Payment Methods Info -->
<div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200">
    <h3 class="text-lg font-bold text-slate-900 mb-4">Metode Pembayaran Tersedia</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="border border-slate-200 rounded-xl p-4 text-center hover:border-primary-600 transition hover:bg-slate-50">
            <i class="fas fa-university text-3xl text-primary-600 mb-2"></i>
            <p class="text-sm font-medium text-slate-700">Transfer Bank</p>
        </div>
        <div class="border border-slate-200 rounded-xl p-4 text-center hover:border-primary-600 transition hover:bg-slate-50">
            <i class="fas fa-wallet text-3xl text-primary-600 mb-2"></i>
            <p class="text-sm font-medium text-slate-700">E-Wallet</p>
        </div>
        <div class="border border-slate-200 rounded-xl p-4 text-center hover:border-primary-600 transition hover:bg-slate-50">
            <i class="fas fa-qrcode text-3xl text-primary-600 mb-2"></i>
            <p class="text-sm font-medium text-slate-700">QRIS</p>
        </div>
        <div class="border border-slate-200 rounded-xl p-4 text-center hover:border-primary-600 transition hover:bg-slate-50">
            <i class="fas fa-store text-3xl text-primary-600 mb-2"></i>
            <p class="text-sm font-medium text-slate-700">Minimarket</p>
        </div>
    </div>
</div>

<script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="Mid-client-5XhHlwtG5PnRxple"></script>

<script type="text/javascript">
    const btnDaftarBaru = document.getElementById('btn-daftar-baru');
    const formDaftarBaru = document.getElementById('form-daftar-baru');

    if (btnDaftarBaru && formDaftarBaru) {
        btnDaftarBaru.addEventListener('click', function() {

            // SATPAM MENGECEK: Apakah jadwal sudah dipilih?
            const jadwalDipilih = document.querySelector('input[name="jadwal_id"]:checked');

            if (!jadwalDipilih) {
                // Jika belum, cegat dan beri peringatan!
                alert("⚠️ Mohon pilih Jadwal Kelas terlebih dahulu!");
                return;
            }

            // JIKA AMAN, Panggil pop-up Midtrans
            window.snap.pay(<?php echo json_encode($snapToken ?? ''); ?>, {
                onSuccess: function(result) {
                    alert("Pembayaran berhasil dikonfirmasi!");
                    // 1. Ambil nilai jadwal yang tadi sudah dipilih
                    const jadwalDipilih = document.querySelector('input[name="jadwal_id"]:checked').value;

                    // 2. Bikin input tersembunyi dan selipkan paksa ke dalam form
                    const inputJadwal = document.createElement('input');
                    inputJadwal.type = 'hidden';
                    inputJadwal.name = 'jadwal_id';
                    inputJadwal.value = jadwalDipilih;
                    formDaftarBaru.appendChild(inputJadwal);
                    // Suruh form lari ke dapur (proses_pembayaran.php)
                    formDaftarBaru.submit();
                },
                onPending: function(result) {
                    alert("Menunggu pembayaran diselesaikan...");
                },
                onError: function(result) {
                    alert("Maaf, pembayaran gagal diproses.");
                },
                onClose: function() {
                    console.log('Siswa menutup pop-up sebelum membayar');
                }
            });
        });
    }
</script>

<?php include '../../includes/footer.php'; ?>