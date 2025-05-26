<?php
// weeklyBackEnd/delWeekly.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../connect.php'; // Sesuaikan path jika berbeda

header('Content-Type: application/json');

// 1. Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// 2. Periksa apakah metode request adalah POST dan apakah 'id' ada
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak valid.'
    ]);
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID item tidak disediakan.'
    ]);
    exit;
}

$itemId = $_POST['id'];

// 3. Validasi ID (opsional, tapi baik untuk keamanan)
if (!filter_var($itemId, FILTER_VALIDATE_INT)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID item tidak valid.'
    ]);
    exit;
}

try {
    // 4. Siapkan query SQL untuk menghapus item
    // Pastikan user hanya bisa menghapus item miliknya sendiri
    $sql = "DELETE FROM weekly_schedule WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Gagal mempersiapkan statement SQL: " . $conn->error);
    }
    
    // 5. Bind parameter (ID item dan User ID)
    // 'ii' menandakan dua parameter integer
    $stmt->bind_param("ii", $itemId, $userId);
    
    // 6. Eksekusi statement
    if (!$stmt->execute()) {
        throw new Exception("Gagal mengeksekusi statement: " . $stmt->error);
    }
    
    // 7. Periksa apakah ada baris yang terpengaruh (terhapus)
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Item jadwal berhasil dihapus.'
        ]);
    } else {
        // Tidak ada baris yang terhapus, mungkin karena ID tidak ditemukan atau bukan milik user tersebut
        echo json_encode([
            'success' => false,
            'message' => 'Item jadwal tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.'
        ]);
    }
    
    // 8. Tutup statement
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
    ]);
}

// 9. Tutup koneksi database
if (isset($conn)) {
    $conn->close();
}
?>