<?php

session_start();

include '../connect.php'; // koneksi ke database

$username = $_POST['usernameSignUp'] ?? '';
$password = $_POST['passwordSignUp'] ?? '';

if ($username === '' || $password === '') {
    echo 'Username dan password wajib diisi';
    exit;
}

// Cek apakah username sudah ada
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo 'Username sudah digunakan';
    exit;
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashedPassword);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Gagal menyimpan data';
}

$stmt->close();
$conn->close();

?>
