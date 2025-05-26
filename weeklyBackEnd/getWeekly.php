<?php
// weeklyBackEnd/getWeekly.php

// Selalu aktifkan error reporting selama development untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Mulai session di paling atas
session_start();

// 2. Sertakan file koneksi database
// Pastikan path ini benar: dari weeklyBackEnd/ ke root folder lalu ke connect.php
include '../connect.php';

// 3. Set header Content-Type ke application/json karena kita akan mengirim data JSON
header('Content-Type: application/json');

// 4. Periksa apakah pengguna sudah login dengan mengecek session user_id
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Jika belum login, kirim respons error dalam format JSON
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Silakan login terlebih dahulu.',
        'data' => [] // Kirim array data kosong
    ]);
    exit; // Hentikan eksekusi skrip
}

// 5. Ambil user_id dari session
$userId = $_SESSION['user_id'];

// 6. Siapkan array untuk menampung data jadwal
$schedules = [];

try {
    // 7. Siapkan query SQL untuk mengambil jadwal berdasarkan user_id
    // Pastikan nama tabel (misal 'weekly_schedule') dan nama kolomnya benar
    // ('id', 'day', 'time_start', 'time_end', 'activity', 'location', 'user_id')
    $sql = "SELECT id, day_of_week, time_start, time_end, activity, location 
            FROM weekly_schedule  -- GANTI NAMA TABEL JIKA BERBEDA
            WHERE user_id = ? 
            ORDER BY 
                FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), 
                time_start ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Jika prepare gagal, berarti ada masalah dengan query SQL atau koneksi
        throw new Exception("Gagal mempersiapkan statement SQL: " . $conn->error);
    }
    
    // 8. Bind parameter user_id ke placeholder '?'
    // 'i' menandakan tipe data integer untuk $userId
    $stmt->bind_param("i", $userId);
    
    // 9. Eksekusi statement
    if (!$stmt->execute()) {
        // Jika eksekusi gagal
        throw new Exception("Gagal mengeksekusi statement: " . $stmt->error);
    }
    
    // 10. Dapatkan hasilnya
    $result = $stmt->get_result();

    // 11. Ambil semua baris data dan masukkan ke array $schedules
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }

    // 12. Kirim respons sukses beserta data jadwal dalam format JSON
    echo json_encode([
        'success' => true,
        'data' => $schedules
    ]);

    // 13. Tutup statement
    $stmt->close();

} catch (Exception $e) {
    // Tangani jika ada error selama proses try
    http_response_code(500); // Set status code server error jika terjadi exception
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
        'data' => []
    ]);
}

// 14. Tutup koneksi database
if (isset($conn)) { // Pastikan $conn ada sebelum ditutup
    $conn->close();
}

?>