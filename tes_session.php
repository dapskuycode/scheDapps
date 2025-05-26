<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    echo "Session Aktif!<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . htmlspecialchars($_SESSION['username']);
} else {
    echo "Tidak ada session aktif atau user_id/username tidak tersimpan.";
}
?>
