<?php
session_name("customer_session");
session_start();

include 'koneksi.php'; // Menghubungkan file ini dengan file koneksi database

// Cek apakah customer sedang login
if (isset($_SESSION['customer_id'])) { // Mengecek apakah ada session customer_id (artinya user sedang login)
    $customer_id = $_SESSION['customer_id']; // Menyimpan ID customer dari session
    
    // Set last_activity ke waktu yang sudah lampau untuk menandai sebagai offline
    $past_time = date('Y-m-d H:i:s', strtotime('-10 years')); // Membuat waktu mundur 10 tahun untuk menandai user sebagai tidak aktif
    $update_stmt = $conn->prepare("UPDATE customer SET last_activity = ? WHERE customer_id = ?"); // Menyiapkan query untuk memperbarui waktu aktivitas terakhir
    $update_stmt->bind_param("si", $past_time, $customer_id); // Mengikat parameter waktu dan ID customer ke query
    $update_stmt->execute(); // Menjalankan query update
    $update_stmt->close(); // Menutup statement setelah selesai
}

// Hancurkan session customer
session_destroy(); // Menghapus seluruh data session sehingga user benar-benar logout

// Alihkan ke halaman login
header('Location: login.php'); // Mengarahkan user kembali ke halaman login setelah logout
exit(); // Menghentikan eksekusi script agar tidak melanjutkan kode di bawahnya
?>
