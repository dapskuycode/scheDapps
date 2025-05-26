<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "scheDapps"; // ganti sesuai nama database kamu
$port = 3307; // jika kamu ubah port MySQL di XAMPP

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Koneksi GAGAL: " . $conn->connect_error);
} else {
    echo "✅ Koneksi BERHASIL ke database!";
}
?>
