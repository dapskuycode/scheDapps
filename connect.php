<?php
$conn = new mysqli("localhost", "root", "", "scheDapps", 3307); // Sesuaikan port!
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>