<?php
// --- AWAL MODE DEBUG ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR MODE DEBUG ---

// 1. Gunakan session yang sama dengan customer
session_name("customer_session");
session_start();

// 2. Cek apakah customer sudah login
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu untuk melakukan checkout.";
    header('Location: ../login.php');
    exit();
}

// 3. Cek apakah keranjang kosong atau aksi tidak valid
if (!isset($_POST['checkout']) || empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Keranjang belanja kosong atau aksi tidak valid.";
    header("Location: ../collection.php");
    exit;
}

include '../koneksi.php'; // Koneksi database

// Proses checkout dimulai
if ($conn) {
    
    // 1. Buat ID unik untuk order ini
    $order_id = uniqid('ORD_' . date('Ymd'), true);
    $customer_id = (int)$_SESSION['customer_id'];
    $all_successful = true; // Flag untuk menandai apakah semua item berhasil diproses

    // 2. Siapkan query dengan Prepared Statements
    // Diasumsikan tabel Anda bernama `order` dengan struktur ini:
    // CREATE TABLE `order` (
    //   `id` int(11) NOT NULL AUTO_INCREMENT,
    //   `order_id` varchar(100) NOT NULL,
    //   `customer_id` int(11) NOT NULL,
    //   `collection_id` int(11) NOT NULL,
    //   `quantity` int(11) NOT NULL,
    //   `total_price` decimal(10,2) NOT NULL,
    //   `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
    //   `status` enum('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    //   PRIMARY KEY (`id`)
    // );
    $query = "INSERT INTO `order` (order_id, customer_id, collection_id, quantity, total_price) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    // 3. Loop setiap item di keranjang untuk disimpan ke database
    foreach ($_SESSION['cart'] as $item) {
        $collection_id = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        $total_price = (float)$item['price'] * $quantity;

        // 4. Bind parameter ke prepared statement
        mysqli_stmt_bind_param($stmt, "siiid", $order_id, $customer_id, $collection_id, $quantity, $total_price);
        
        // 5. Eksekusi query untuk setiap item
        if (!mysqli_stmt_execute($stmt)) {
            $all_successful = false;
            // Simpan error ke session untuk ditampilkan di halaman berikutnya
            $_SESSION['error'] = "Gagal memproses item: " . htmlspecialchars($item['name']) . ". Silakan coba lagi.";
            break; // Hentikan loop jika ada yang gagal
        }
    }
    
    // Tutup statement
    mysqli_stmt_close($stmt);
    $conn->close();

    // 6. Cek hasil akhir dan berikan respons
    if ($all_successful) {
        // Jika semua item berhasil, kosongkan cart
        $_SESSION['cart'] = [];
        $_SESSION['message'] = "Checkout berhasil! Pesanan Anda dengan ID: <strong>" . htmlspecialchars($order_id) . "</strong> telah diterima.";
    } else {
        // Jika ada item yang gagal, pesan error sudah disimpan di session
    }

    // 7. Redirect kembali ke halaman collection
    header("Location: ../collection.php");
    exit;

} else {
    $_SESSION['error'] = "Koneksi database gagal.";
    header("Location: ../collection.php");
    exit;
}
?>