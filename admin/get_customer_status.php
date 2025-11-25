<?php

// Mengatur nama session khusus untuk admin
session_name("admin_session");
// Memulai session
session_start();

// Mengecek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, arahkan ke halaman login admin
    header('Location: admin_login.php');
    exit();
}

// Menyertakan file koneksi ke database
include '../koneksi.php';

// ... dan seterusnya (menandakan kode lain sebelumnya diabaikan)

// Mengatur agar output halaman ini berupa JSON
header('Content-Type: application/json');

// Memastikan koneksi ke database tersedia
require_once '../koneksi.php';

// Atur waktu ambang batas (threshold) untuk menentukan siapa yang online atau offline
// Dalam kasus ini, semua yang last_activity lebih dari 10 tahun lalu dianggap offline
$time_threshold = date('Y-m-d H:i:s', strtotime('-10 years'));

// --- QUERY UNTUK CUSTOMER ONLINE ---
// Mengambil semua customer yang aktivitas terakhirnya lebih baru dari waktu threshold
$online_stmt = $conn->prepare("SELECT customer_id, name FROM customer WHERE last_activity > ? AND last_activity IS NOT NULL");
$online_stmt->bind_param("s", $time_threshold);
$online_stmt->execute();
$online_customers = $online_stmt->get_result();

// --- QUERY UNTUK CUSTOMER OFFLINE ---
// Menghitung jumlah customer yang aktivitas terakhirnya lebih lama dari threshold
// atau belum pernah tercatat (NULL)
$offline_stmt = $conn->prepare("SELECT COUNT(*) as total FROM customer WHERE last_activity <= ? OR last_activity IS NULL");
$offline_stmt->bind_param("s", $time_threshold);
$offline_stmt->execute();
$offline_result = $offline_stmt->get_result();
$offline_customers = $offline_result->fetch_assoc();

// --- PROSES PENYUSUNAN DATA UNTUK DIKIRIM DALAM BENTUK JSON ---
// Menyiapkan array untuk menyimpan nama-nama customer yang online
$online_names = [];
while($customer = $online_customers->fetch_assoc()) {
    // Mengamankan output agar tidak ada karakter berbahaya (XSS)
    $online_names[] = htmlspecialchars($customer['name']);
}

// Menyusun respons akhir dalam bentuk array asosiatif
$response = [
    'online_count' => $online_customers->num_rows, // Jumlah customer online
    'offline_count' => $offline_customers['total'], // Jumlah customer offline
    'online_names' => $online_names // Daftar nama-nama customer online
];

// Mengirimkan data dalam format JSON ke frontend
echo json_encode($response);

// Menutup statement dan koneksi ke database
$online_stmt->close();
$offline_stmt->close();
$conn->close();
?>
