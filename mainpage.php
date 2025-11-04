<?php

ini_set('display_errors', 1); // Mengaktifkan penampilan error di browser
ini_set('display_startup_errors', 1); // Menampilkan error saat proses startup PHP
error_reporting(E_ALL); // Mengatur agar semua jenis error ditampilkan

include 'koneksi.php';
session_name("customer_session"); 
session_start();

if (isset($_SESSION['customer_id'])) { // Mengecek apakah customer sudah login (session aktif)
    $update_activity_stmt = $conn->prepare("UPDATE customer SET last_activity = NOW() WHERE customer_id = ?"); // Menyiapkan query untuk memperbarui waktu aktivitas terakhir user
    $update_activity_stmt->bind_param("i", $_SESSION['customer_id']); // Mengikat parameter customer_id ke query
    $update_activity_stmt->execute(); // Menjalankan query update
    $update_activity_stmt->close(); // Menutup statement setelah digunakan
}

if (!isset($_SESSION['customer_id'])) { // Jika user belum login (tidak ada session)
    header('Location: login.php'); // Arahkan user ke halaman login
    exit(); // Hentikan eksekusi kode setelah redirect
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Mengecek apakah form dikirim menggunakan metode POST
    $customer_id = $_SESSION['customer_id']; // Mengambil ID customer dari session aktif

    // Ambil nama customer dari database
    $customer_query = "SELECT name FROM customer WHERE customer_id = ?"; // Query untuk mengambil nama customer berdasarkan ID
    $customer_stmt = $conn->prepare($customer_query); // Menyiapkan statement untuk eksekusi yang aman
    if ($customer_stmt === false) { // Jika statement gagal disiapkan
        die("Error preparing customer statement: " . $conn->error); // Hentikan eksekusi dan tampilkan pesan error
    }
    $customer_stmt->bind_param("i", $customer_id); // Mengikat parameter ID customer ke query
    $customer_stmt->execute(); // Menjalankan query
    $customer_result = $customer_stmt->get_result(); // Mengambil hasil eksekusi query

    if ($customer_result->num_rows > 0) { // Jika data customer ditemukan
        $customer_data = $customer_result->fetch_assoc(); // Ambil data hasil query dalam bentuk array asosiatif
        $customer_name = $customer_data['name']; // Simpan nama customer dari hasil query
    } else {
        die("Error: Data customer tidak ditemukan."); // Jika tidak ada data customer, hentikan proses dengan pesan error
    }
    $customer_stmt->close(); // Tutup statement setelah digunakan

    // Ambil data lain dari form
    $session_date = $_POST['session_date']; // Menyimpan tanggal sesi dari form
    $session_time = $_POST['session_time']; // Menyimpan waktu sesi dari form
    $order_details = htmlspecialchars($_POST['order-textarea']); // Mengamankan input agar karakter HTML tidak dijalankan
    $booking_id = uniqid('BK_' . date('Ymd'), true); // Membuat ID booking unik dengan awalan tanggal hari ini
    $query = "INSERT INTO booking_order (booking_id, booking_date, name, customer_id, admin_id, order_details, session_date, session_time, status) VALUES (?, NOW(), ?, ?, NULL, ?, ?, ?, 'pending')"; // Query untuk menyimpan data booking baru ke tabel
    $stmt = $conn->prepare($query); // Menyiapkan statement SQL

    if ($stmt === false) { // Jika statement gagal disiapkan
        die("Error preparing statement: " . $conn->error); // Tampilkan error dan hentikan eksekusi
    }
    
    $stmt->bind_param("isisss", $booking_id, $customer_name, $customer_id, $order_details, $session_date, $session_time); // Mengikat parameter ke query SQL sesuai urutan tipe data

    if ($stmt->execute()) { // Jika eksekusi query berhasil
        // PERUBAHAN: Langsung redirect tanpa notifikasi
        header('Location: mainpage.php'); // Arahkan ke halaman utama setelah booking berhasil
        exit(); // Hentikan eksekusi agar tidak melanjutkan kode di bawah
    } else {
        // Tampilkan error yang sangat jelas
        $error_message = $stmt->error; // Menyimpan pesan error dari MySQL
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>"; // Memanggil library SweetAlert2 untuk menampilkan popup
        echo "<script>
        Swal.fire({
            title: 'Error!', // Judul pesan error
            html: 'Terjadi kesalahan saat menyimpan data.<br><br>Pesan Error: <strong>" . addslashes($error_message) . "</strong>', // Isi pesan error yang ditampilkan ke user
            icon: 'error', // Menampilkan ikon error
            confirmButtonText: 'Coba Lagi' // Tombol untuk menutup popup
        });
        </script>"; // Menutup script JavaScript
    }

    $stmt->close(); // Menutup statement setelah digunakan
    $conn->close(); // Menutup koneksi database
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nahecididi Jewelry Store</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/cart.css">
</head>
<body>

<header>
<a href="mainpage.php" class="Logo">nahecididi jewelry</a>
<nav class="navbar">
<a href="#home">Home</a>
<a href="#about">About</a>
<a href="collection.php">Collection</a>
<a href="#location">Location</a>
<a href="#order">Book</a>
</nav>
<div class="icons">
<i class="fas fa-bars" id="menu-bars"></i>
<a href="logout.php" class="bx bx-log-out" title="Logout"></a>
</div>
</header>

<!-- Swiper -->
<section class="home" id="home">
<div class="swiper home-slider">
<div class="swiper-wrapper wrapper">
<?php
 $query = "SELECT * FROM collection ORDER BY RAND() LIMIT 3"; // Mengambil 3 data acak dari tabel 'collection'
 $result = mysqli_query($conn, $query); // Menjalankan query dan menyimpan hasilnya dalam variabel $result

while ($row = mysqli_fetch_assoc($result)) { // Melakukan perulangan untuk setiap baris hasil query
echo '
<div class="swiper-slide slide"> <!-- Membuat satu slide untuk setiap data produk -->
<div class="content">
<span>Our main jewelry</span> <!-- Teks tetap -->
<h3>' . htmlspecialchars($row['product_name']) . '</h3> <!-- Menampilkan nama produk -->
<p>' . htmlspecialchars($row['description']) . '</p> <!-- Menampilkan deskripsi produk -->
</div>
<div class="image">
<img src="image/' . htmlspecialchars($row['image']) . '"> <!-- Menampilkan gambar produk -->
</div>
</div>';
}
?> <!-- Menutup blok PHP -->

?>
</div>
</div>
</section>

<!-- Popular Jewelry Section -->
<section class="jewelry" id="jewelry">
<h3 class="sub-heading">Our Jewelry</h3>
<h1 class="heading">Popular Jewelry</h1>
<div class="box-container">
<?php
 $query = "SELECT * FROM collection ORDER BY RAND() LIMIT 5"; // Query untuk mengambil 5 data acak dari tabel 'collection'
 $result = mysqli_query($conn, $query); // Menjalankan query ke database dan menyimpan hasilnya ke variabel $result
while ($row = mysqli_fetch_assoc($result)) { // Melakukan perulangan untuk setiap baris hasil query dan menyimpannya dalam array asosiatif $row
?>
<div class="box"> <!-- Membuat elemen box untuk menampilkan satu produk -->
<img src="image/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>"> <!-- Menampilkan gambar produk, diamankan dengan htmlspecialchars untuk mencegah XSS -->
<h3><?php echo htmlspecialchars($row['product_name']); ?></h3> <!-- Menampilkan nama produk -->
<span>Rp. <?php echo htmlspecialchars(number_format($row['price'], 0, ',', '.')); ?></span> <!-- Menampilkan harga produk dengan format rupiah (pemisah ribuan titik, tanpa desimal) -->
</div>
<?php } ?> <!-- Menutup blok PHP setelah perulangan selesai -->

</div>
</section>

<!-- About Section -->
<section class="about" id="about">
<h3 class="sub-heading">About Us</h3>
<h1 class="heading">Why You Should Choose Us?</h1>
<div class="row">
<div class="image">
<img src="image/jewelry_store.jpg" alt="">
</div>
<div class="content">
<h3>Best jewelry in the country</h3>
<p>Jewelry is the embodiment of art in the form of jewelry, blending beauty, elegance, and craftsmanship.</p>
<p>Customer satisfaction is our top priority. We are committed to providing the best service and high-quality products.</p>
<div class="icon-container">
<div class="icon">
<i class="fas fa-dollar-sign"></i>
<span>Easy payment</span>
</div>
<div class="icon">
<i class="fas fa-headset"></i>
<span>10 AM - 10 PM</span>
</div>
</div>
</div>
</div>
</section>

<!-- Location Section -->
<section class="location" id="location">
<h3 class="sub-heading">Our Location</h3>
<h1 class="heading">Come here for our Beautiful Jewelry</h1>
<div class="row">
<div class="image">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3965.246614803817!2d106.82234407603914!3d-6.362120062240419!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ec18c3e8e717%3A0x9f2283e905986b55!2sCEP%20CCIT%20FTUI!5e0!3m2!1sen!2sid!4v1760798690151!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>
<div class="content">
<h3>Where We Can Be Found</h3>
<p>Gedung Engineering Center Lt. 1, FTUI Kampus Baru UI Depok, Jalan Prof. DR. Ir R. Roosseno, Kukusan, Kecamatan Beji, Kota Depok, Jawa Barat 16425</p>
<div class="icon-container">
<div class="icon">
<i class="fas fa-dollar-sign"></i>
<span>Easy payment</span>
</div>
<div class="icon">
<i class="fas fa-headset"></i>
<span>10 AM - 10 PM</span>
</div>
</div>
</div>
</div>
</section>

<!-- Order Section -->
<section class="order" id="order">
<h3 class="sub-heading">Order Details</h3>
<h1 class="heading">Buy your jewelry in our offline store now</h1>
<form action="mainpage.php" method="POST">
<div class="inputbox">
<div class="input"><label>Date</label><input type="date" id="datePicker" name="session_date" required></div>
<div class="input"><label>Time (10 AM - 8 PM)</label><input type="time" id="timePicker" name="session_time" required></div>
</div>
<div class="inputbox">
<div class="input">
<label>Give details in your order</label>
<textarea id="order-textarea" name="order-textarea" placeholder="Enter details for your order here" cols="30" rows="10" required></textarea>
</div>
</div>
<input type="submit" value="Book now" class="btn" id="buynow">
</form>
</section>


<!-- Footer -->
<section class="footer">
<div class="box-container">
<div class="box">
<h3>Location</h3>
<a href="#">Kota Depok</a>
</div>
<div class="box">
<h3>Links</h3>
<a href="#">Home</a>
<a href="#">About</a>
<a href="collection.php">Collection</a>
<a href="#">Location</a>
<a href="#">Book</a>
</div>
<div class="box">
<h3>Contact Info</h3>
<a href="#">+62 812-8717-1663</a>
<a href="#">+62 853-2044-3055</a>
<a href="#">NahecididijewelryTeam@gmail.com</a>
</div>
</div>
<div class="credit">Thank you for coming to Nahecididi Jewelry, <span>By Kelompok 4</span></div>
</section>



<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="js/main.js"></script>
<script src="js/cart.js"></script>
</body>
</html>