<?php
ini_set('display_errors', 1); // Menampilkan error PHP di layar
ini_set('display_startup_errors', 1); // Menampilkan error saat startup PHP
error_reporting(E_ALL); // Menampilkan semua jenis error (notice, warning, fatal, dll)

session_name("admin_session"); // Menetapkan nama session khusus untuk admin
session_start(); // Memulai session agar bisa menyimpan data login admin

// Jika admin sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['admin_id'])) { // Mengecek apakah session admin_id sudah ada
    header('Location: admin_mainpage.php'); // Jika sudah login, arahkan ke halaman utama admin
    exit(); // Hentikan eksekusi skrip
}

include '../koneksi.php'; // Menghubungkan file koneksi database agar bisa digunakan untuk query

// Proses login saat form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Mengecek apakah form dikirim dengan metode POST
    $email = mysqli_real_escape_string($conn, $_POST['email']); // Mengambil input email dan mengamankannya dari SQL injection
    $password_input = $_POST['password']; // Mengambil input password

    $query = "SELECT * FROM admin WHERE email = '$email' LIMIT 1"; // Mencari data admin berdasarkan email
    $result = mysqli_query($conn, $query); // Menjalankan query ke database

    if (mysqli_num_rows($result) == 1) { // Jika ditemukan satu data admin dengan email tersebut
        $admin = mysqli_fetch_assoc($result); // Mengambil data admin sebagai array asosiatif

        // Cek apakah password yang dimasukkan sesuai dengan database
        if ($password_input === $admin['password']) { // Bandingkan password input dengan password di database (plaintext)
            $_SESSION['admin_name'] = $admin['name']; // Simpan nama admin ke dalam session
            $_SESSION['admin_id'] = $admin['admin_id']; // Simpan ID admin ke dalam session
            header("Location: admin_mainpage.php"); // Arahkan ke halaman utama admin
            exit(); // Hentikan skrip setelah redirect
        } else {
            $error_message = "Password salah!"; // Jika password tidak cocok, tampilkan pesan error
        }
    } else {
        $error_message = "Email tidak ditemukan!"; // Jika email tidak ada di database
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
  <link rel="stylesheet" href="../css/login.css">
</head>
<body>
  <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
    <div class="container">
      <div class="card login-card">
            <div class="card-body">
              <p class="login-card-description">Halo Atmin tersayang, silahkan login</p>

              <!-- Tampilkan pesan error jika ada -->
              <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
              <?php endif; ?>

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

              <footer class="mt-3 text-center">&copy; Nahecididi <?php echo date("Y"); ?></footer>
            </div>
      </div>
    </div>
  </main>
</body>
</html>