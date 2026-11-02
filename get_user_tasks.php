<?php
require 'db.php';
header('Content-Type: application/json');
$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid user']); exit; }

$stmt = $conn->prepare("SELECT id, name, details, start_time, end_time, status FROM task WHERE user_id = ? ORDER BY start_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = [];
while($row = $result->fetch_assoc()) { $tasks[] = $row; }
echo json_encode(['success' => true, 'tasks' => $tasks]);
?>
