<?php
require 'db.php'; header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['logged_in']) && $_SESSION['role'] === 'user') {
    $task_id = intval($_POST['task_id'] ?? 0); $status = $_POST['status'] ?? ''; $user_id = $_SESSION['user_id'];
    if (in_array($status, ['pending', 'started', 'done']) && $task_id > 0) {
        $stmt = $conn->prepare("UPDATE task SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $task_id, $user_id);
        echo json_encode(['success' => $stmt->execute()]);
    } else { echo json_encode(['success' => false]); }
} else { echo json_encode(['success' => false, 'message' => 'Unauthorized']); }
?>
