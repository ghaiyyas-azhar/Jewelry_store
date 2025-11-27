<?php
session_name("customer_session");
session_start();
include 'koneksi.php';

$message = ""; // Variabel untuk menyimpan pesan hasil proses (error atau sukses)

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Mengecek apakah form dikirim menggunakan metode POST
    $email = mysqli_real_escape_string($conn, $_POST['email']); // Mengamankan input email dari karakter berbahaya (SQL injection)
    $new_password = mysqli_real_escape_string($conn, $_POST['password']); // Mengamankan input password baru
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']); // Mengamankan input konfirmasi password

    // Pastikan password baru dan konfirmasi sama
    if ($new_password !== $confirm_password) { // Jika password baru dan konfirmasi tidak cocok
        $message = "Password baru dan verifikasi tidak cocok!"; // Simpan pesan error
    } else {
        // Cek apakah email ada di database
        $result = mysqli_query($conn, "SELECT * FROM customer WHERE email='$email'"); // Mencari data user berdasarkan email

        if (mysqli_num_rows($result) > 0) { // Jika email ditemukan di database
            // Update password baru
            $update = mysqli_query($conn, "UPDATE customer SET password='$new_password' WHERE email='$email'"); // Mengubah password user sesuai email

            if ($update) { // Jika proses update berhasil
                $message = "Password berhasil diperbarui! Silakan login kembali."; // Pesan sukses jika password berhasil diganti
            } else {
                $message = "Terjadi kesalahan saat memperbarui password."; // Pesan error jika query update gagal
            }
        } else {
            $message = "Email tidak ditemukan dalam sistem."; // Pesan error jika email tidak ada di database
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
  <div class="container">
    <div class="card login-card mx-auto">
      <div class="card-body">
        <p class="login-card-description">Reset Password</p>
        <p class="description-subtext">
          Masukkan email dan buat password baru Anda.
        </p>

        <?php if (!empty($message)): ?>
          <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" required>
          </div>

          <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru" required>
          </div>

          <div class="form-group">
            <label for="confirm_password">Verifikasi Password Baru</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
          </div>

          <button type="submit" class="btn btn-primary btn-block">Perbarui Password</button>
        </form>

        <p class="text-center mt-3">
          <a href="login.php" class="text-link">Kembali ke Login</a>
        </p>

        <footer class="mt-3 text-center">
          &copy; Nahecididi <?php echo date("Y"); ?>
        </footer>
      </div>
    </div>
  </div>
</main>

</body>
</html>
