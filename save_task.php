<?php
require 'db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $user_id = intval($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $status = trim($_POST['status'] ?? 'pending');

    if (empty($name) || empty($start_time) || empty($end_time) || $user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Name, start time, end time, and user are required.']); exit;
    }
    if (!in_array($status, ['pending', 'started', 'done'])) { $status = 'pending'; }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE task SET name=?, details=?, start_time=?, end_time=?, status=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssii", $name, $details, $start_time, $end_time, $status, $id, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO task (user_id, name, details, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $name, $details, $start_time, $end_time, $status);
    }

    if ($stmt->execute()) { echo json_encode(['success' => true]); } 
    else { echo json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->error]); }
}
?>
