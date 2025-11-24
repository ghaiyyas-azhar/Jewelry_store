<?php
$host = "mysql";            // nama service mysql di docker-compose
$user = "root";             // user mysql
$pass = "password";         // password sesuai docker-compose
$db   = "jewelry_store";    // nama database kamu

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function db_disconnect($con) {
    mysqli_close($con);
}
?>
