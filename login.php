<?php
session_name("customer_session"); // Mengatur nama sesi agar berbeda dengan sesi lain
session_start(); // Memulai sesi untuk melacak status login pengguna

include 'koneksi.php'; // Menghubungkan file ini dengan file koneksi ke database

// Cegah caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // Mencegah browser menyimpan cache halaman
header("Cache-Control: post-check=0, pre-check=0", false); // Pengaturan tambahan untuk mencegah cache
header("Pragma: no-cache"); // Menonaktifkan cache pada browser lama

if (isset($_SESSION['username'])) { // Mengecek apakah pengguna sudah login
    header("Location: mainpage.php"); // Jika sudah login, arahkan ke halaman utama
    exit(); // Hentikan eksekusi skrip setelah pengalihan
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Mengecek apakah form dikirim dengan metode POST
    $email = mysqli_real_escape_string($conn, $_POST['email']); // Mengamankan input email dari karakter berbahaya (SQL injection)
    $password_input = $_POST['password']; // Menyimpan input password dari form

    $query = "SELECT * FROM customer WHERE email = '$email' LIMIT 1"; // Membuat query untuk mencari data pengguna berdasarkan email
    $result = mysqli_query($conn, $query); // Menjalankan query ke database

    if (mysqli_num_rows($result) == 1) { // Jika ada satu data ditemukan (email terdaftar)
        $user = mysqli_fetch_assoc($result); // Mengambil data pengguna dari hasil query
        if ($password_input === $user['password']) { // Mengecek apakah password yang dimasukkan sama dengan yang ada di database
            $_SESSION['username'] = $user['name']; // Menyimpan nama pengguna ke dalam sesi
            $_SESSION['customer_id'] = $user['customer_id']; // Menyimpan ID pengguna ke dalam sesi
            header("Location: mainpage.php"); // Mengarahkan pengguna ke halaman utama setelah login berhasil
            exit(); // Menghentikan eksekusi skrip
        } else {
            echo "<script>alert('Password salah!');</script>"; // Menampilkan peringatan jika password salah
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!');</script>"; // Menampilkan peringatan jika email tidak terdaftar
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Jewelry Store</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
    <div class="container">
      <div class="card login-card">
            <div class="card-body">
              <p class="login-card-description">Silahkan masuk ke akun Anda</p>

              <form method="POST" action="">
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                  <label>Password</label>
                  <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
              </form>

              <p class="text-center mt-3">
                Lupa Password? <a href="update_password.php" style="color: #ff9f0d;"> Ganti sekarang</a></p>

                Belum punya akun? <a href="register.php" class="text-link"> Daftar Sekarang</a>
              </p>

              <footer class="mt-3 text-center">&copy; Nahecididi <?php echo date("Y"); ?></footer>
            </div>
      </div>
    </div>
  </main>
</body>
</html>
