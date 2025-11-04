<?php
session_name("customer_session");
session_start();

// Koneksi database
include 'koneksi.php'; // Menghubungkan file ini dengan file koneksi database
if (!$conn) { // Mengecek apakah koneksi ke database berhasil
    die("Koneksi gagal: " . mysqli_connect_error()); // Jika gagal, hentikan proses dan tampilkan pesan error
}

// Saat user menekan tombol daftar
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Mengecek apakah form dikirim menggunakan metode POST
    $name = mysqli_real_escape_string($conn, $_POST['name']); // Mengamankan input nama dari karakter berbahaya
    $email = mysqli_real_escape_string($conn, $_POST['email']); // Mengamankan input email
    $phone = mysqli_real_escape_string($conn, $_POST['phone']); // Mengamankan input nomor telepon
    $address = mysqli_real_escape_string($conn, $_POST['address']); // Mengamankan input alamat
    $password_input = $_POST['password']; // Menyimpan input password dari form

    // Cek apakah email sudah ada
    $check_query = "SELECT * FROM customer WHERE email = '$email' LIMIT 1"; // Membuat query untuk memeriksa apakah email sudah terdaftar
    $check_result = mysqli_query($conn, $check_query); // Menjalankan query ke database

    if (mysqli_num_rows($check_result) > 0) { // Jika ditemukan data dengan email yang sama
        echo "<script>alert('Email sudah digunakan!'); window.history.back();</script>"; // Tampilkan pesan peringatan dan kembali ke halaman sebelumnya
    } else {
        // Simpan data baru
        $query = "INSERT INTO customer (name, email, phone, address, password)
                  VALUES ('$name', '$email', '$phone', '$address', '$password_input')"; // Query untuk menambahkan data pengguna baru ke tabel customer
        if (mysqli_query($conn, $query)) { // Jika proses insert berhasil
            echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='login.php';</script>"; // Tampilkan pesan sukses dan arahkan ke halaman login
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>"; // Jika gagal, tampilkan pesan error dari database
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register - Jewelry Store</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
    <div class="container">
      <div class="card login-card">
        <div class="row no-gutters">
            <div class="card-body">
              <p class="login-card-description">Buat Akun Baru</p>

              <form method="POST" action="">
                <div class="form-group">
                  <label>Nama Lengkap</label>
                  <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Nomor Telepon</label>
                  <input type="text" name="phone" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Alamat</label>
                  <textarea name="address" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                  <label>Password</label>
                  <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Daftar</button>
              </form>

              <p class="text-center mt-3">
                Sudah punya akun? <a href="login.php" class="text-link">Login di sini</a>
              </p>

              <footer class="mt-3 text-center">&copy; Nahecididi <?php echo date("Y"); ?></footer>
            </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
