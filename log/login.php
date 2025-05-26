<?php

session_start();

include '../connect.php'; // file koneksi database

$username = $_POST['usernameLogin'] ?? '';
$password = $_POST['passwordLogin'] ?? '';

if ($username === '' || $password === '') {
    echo 'Username dan password wajib diisi';
    exit;
}

// Cek di database
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $username;
        echo 'success';
    } else {
        echo 'Password salah';
    }

} else {
    echo 'Username tidak ditemukan';
}
?>
