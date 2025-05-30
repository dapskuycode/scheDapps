<?php 

session_start();

include "../connect.php";

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Jika belum login, kirim respons error dalam format JSON
    echo json_encode([
        'success' => false,
        'message' => 'Akses ditolak. Silakan login terlebih dahulu.',
        'data' => [] // Kirim array data kosong
    ]);
    exit; // Hentikan eksekusi skrip
}

$userId = $_SESSION['user_id'];
$tasks = [];

try {
    $sql = "SELECT id,task_name, description, due_date, due_time 
            FROM tasks 
            WHERE user_id = ? 
            ORDER BY due_date ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Jika prepare gagal, berarti ada masalah dengan query SQL atau koneksi
        throw new Exception("Gagal mempersiapkan statement SQL: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        // Jika eksekusi gagal
        throw new Exception("Gagal mengeksekusi statement: " . $stmt->error);
    }

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $tasks
    ]);
} catch (Exception $e) {
    // Jika terjadi kesalahan, kirim respons error
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ]);
} finally {
    // Tutup statement dan koneksi
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    $conn->close();
}   

?>