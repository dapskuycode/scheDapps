<?php

session_start();

// 1. Set header Content-Type ke application/json untuk konsistensi respons
header('Content-Type: application/json');

// 2. Sertakan file koneksi database
include '../connect.php'; // Pastikan path ini benar

// 3. Inisialisasi array untuk respons
$response = ['success' => false, 'message' => ''];

// 4. Periksa apakah koneksi database berhasil (asumsi $conn dari connect.php)
if (!$conn || $conn->connect_error) {
    // http_response_code(500); // Internal Server Error
    $response['message'] = 'Koneksi database gagal: ' . ($conn ? $conn->connect_error : 'Unknown error');
    echo json_encode($response);
    exit;
}

// 5. Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // http_response_code(401); // Unauthorized
    $response['message'] = 'Akses ditolak. Silakan login terlebih dahulu.';
    echo json_encode($response);
    exit;
}

// 6. Periksa metode request (opsional tapi direkomendasikan)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // http_response_code(405); // Method Not Allowed
    $response['message'] = 'Metode request tidak valid.';
    echo json_encode($response);
    exit;
}

// 7. Ambil dan validasi input
$userId = $_SESSION['user_id'];
$taskName = $_POST['taskName'] ?? null; // Gunakan null untuk pengecekan lebih mudah
$taskDesc = $_POST['taskDesc'] ?? '';   // Deskripsi boleh kosong
$taskDL = $_POST['taskDL'] ?? null;
$timeDL = $_POST['timeDL'] ?? null;

// Validasi input yang wajib ada
if (empty($taskName) || empty($taskDL) || empty($timeDL)) {
    // http_response_code(400); // Bad Request
    $response['message'] = 'Nama task, tanggal deadline, dan waktu deadline tidak boleh kosong.';
    echo json_encode($response);
    exit;
}

// Validasi format tanggal (contoh sederhana, bisa lebih kompleks)
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $taskDL)) {
    // http_response_code(400);
    $response['message'] = 'Format tanggal deadline tidak valid (YYYY-MM-DD).';
    echo json_encode($response);
    exit;
}

// Validasi format waktu (contoh sederhana)
if (!preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/", $timeDL)) {
    // http_response_code(400);
    $response['message'] = 'Format waktu deadline tidak valid (HH:MM atau HH:MM:SS).';
    echo json_encode($response);
    exit;
}


// 8. Siapkan query SQL
// Ganti 'tasks' dengan nama tabel Anda yang benar jika berbeda.
$sql = "INSERT INTO tasks (user_id, task_name, description, due_date, due_time) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// 9. Periksa apakah prepare statement berhasil
if ($stmt === false) {
    // http_response_code(500); // Internal Server Error
    // Untuk development, Anda bisa log error: error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $response['message'] = 'Gagal mempersiapkan statement database.';
    echo json_encode($response);
    $conn->close(); // Tutup koneksi jika statement gagal di-prepare
    exit;
}

// 10. Bind parameter
// 'issss' sesuai dengan user_id (int), task_name (string), description (string), due_date (string), due_time (string)
$bindResult = $stmt->bind_param("issss", $userId, $taskName, $taskDesc, $taskDL, $timeDL);

if ($bindResult === false) {
    // http_response_code(500);
    // error_log("Bind param failed: (" . $stmt->errno . ") " . $stmt->error);
    $response['message'] = 'Gagal mengikat parameter ke statement.';
    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit;
}

// 11. Eksekusi statement
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Task berhasil ditambahkan.';
    // Anda bisa menambahkan ID task yang baru dibuat ke respons jika diperlukan
    // $response['taskId'] = $stmt->insert_id;
} else {
    // http_response_code(500);
    // error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    $response['message'] = 'Gagal menyimpan data ke database. Error: ' . $stmt->error; // Tampilkan error MySQL untuk debugging
}

// 12. Kirim respons JSON
echo json_encode($response);

// 13. Tutup statement dan koneksi
$stmt->close();
$conn->close();

?>