<?php
// log/logout.php

// 1. Mulai atau lanjutkan session yang ada untuk bisa dimanipulasi
session_start();

// 2. Hapus semua variabel session yang telah dibuat
// Cara paling pasti adalah dengan mengosongkan array $_SESSION
$_SESSION = array();

// 3. Jika ingin menghancurkan session sepenuhnya, hapus juga cookie session.
// Catatan: Ini akan menghancurkan session, bukan hanya data session!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Set waktu ke masa lalu
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Akhirnya, hancurkan session.
session_destroy();

// 5. Kirim respons JSON ke JavaScript (jika JavaScript Anda mengharapkannya)
// Ini berguna agar JavaScript tahu bahwa logout di server berhasil.
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logout berhasil.']);
exit; // Pastikan tidak ada output lain setelah ini

?>