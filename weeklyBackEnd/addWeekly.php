<?php 

session_start();
include '../connect.php'; 

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menambahkan jadwal.']); 
    exit;
}
$userId = $_SESSION['user_id'];
$day = $_POST['day'] ?? '';
$timeStart = $_POST['timeStart'] ?? '';
$timeEnd = $_POST['timeEnd'] ?? '';
$activity = $_POST['act'] ?? '';
$location = $_POST['loc'] ?? '';

$stmt = $conn->prepare("INSERT INTO weekly_schedule (user_id, day_of_week, activity, time_start, time_end, location) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $userId, $day,  $activity, $timeStart, $timeEnd, $location);

if($stmt->execute()) {
    echo 'success';
} else {
    echo 'Gagal menyimpan data';
    exit;
}

$stmt->close();
$conn->close();

?>