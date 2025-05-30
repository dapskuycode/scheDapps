<?php

session_start();

include "../connect.php"; // TITIK KOMA DITAMBAHKAN DI SINI
header('Content-Type: application/json'); // Pastikan ini dipanggil sebelum output apapun

$userId = $_SESSION['user_id'] ?? null;
$response = []; // Siapkan array untuk respons JSON

try {
    // 1. Periksa apakah pengguna sudah login
    if (!$userId) {
        http_response_code(401); // Unauthorized
        $response = [
            'success' => false,
            'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
        ];
        echo json_encode($response);
        exit;
    }

    // 2. Periksa apakah metode request adalah POST dan apakah 'id' ada
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || empty(trim($_POST['id']))) {
        http_response_code(400); // Bad Request
        $response = [
            'success' => false,
            'message' => 'Metode request tidak valid atau ID item tidak disediakan.'
        ];
        echo json_encode($response);
        exit;
    }

    $itemId = trim($_POST['id']); // Ambil ID item

    // 3. Validasi ID (opsional, tapi baik untuk keamanan)
    if (!filter_var($itemId, FILTER_VALIDATE_INT)) {
        http_response_code(400); // Bad Request
        $response = [
            'success' => false,
            'message' => 'ID item tidak valid.'
        ];
        echo json_encode($response);
        exit;
    }

    // 4. Siapkan query SQL untuk menghapus item
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Catat error server untuk debugging, jangan tampilkan detail ke user di produksi
        error_log("MySQL Prepare Error: " . $conn->error);
        throw new Exception("Terjadi kesalahan saat mempersiapkan penghapusan data.");
    }
    
    $stmt->bind_param("ii", $itemId, $userId);

    if (!$stmt->execute()) {
        // Catat error server untuk debugging
        error_log("MySQL Execute Error: " . $stmt->error);
        throw new Exception("Terjadi kesalahan saat mengeksekusi penghapusan data.");
    }

    // Periksa apakah ada baris yang benar-benar terhapus
    if ($stmt->affected_rows > 0) {
        http_response_code(200); // OK
        $response = [
            'success' => true,
            'message' => 'Tugas berhasil dihapus.'
        ];
    } else {
        http_response_code(404); // Not Found (atau bisa juga 200 dengan pesan berbeda)
        $response = [
            'success' => false, // Atau true jika "tidak ada yang dihapus" bukan error fatal
            'message' => 'Tugas tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.'
        ];
    }
    
    $stmt->close(); // Selalu tutup statement

} catch (Exception $e) {
    // Pastikan http_response_code diset sebelum echo json_encode jika belum diset
    if (http_response_code() === 200) { // Jika belum ada kode error yang diset
        http_response_code(500); // Internal Server Error
    }
    $response = [
        'success' => false,
        'message' => $e->getMessage() // Pesan dari Exception
        // Di lingkungan produksi, Anda mungkin ingin pesan yang lebih generik:
        // 'message' => 'Terjadi kesalahan internal pada server. Silakan coba lagi nanti.'
    ];
} finally {
    // Selalu pastikan respons JSON dikirim
    if (!headers_sent()) { // Cek apakah header sudah terkirim
         header('Content-Type: application/json'); // Set lagi jika belum
    }
    echo json_encode($response); // Kirim respons

    // Tutup koneksi database jika ada dan belum ditutup
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit; // Pastikan skrip berhenti setelah mengirim respons
}

?>