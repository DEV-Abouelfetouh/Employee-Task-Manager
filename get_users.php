<?php
require 'db.php';
header('Content-Type: application/json');
$sql = "SELECT u.id, u.name, u.role, u.mobile, COUNT(t.id) as task_count 
        FROM users u LEFT JOIN task t ON u.id = t.user_id 
        GROUP BY u.id ORDER BY u.name ASC";
$result = $conn->query($sql);
$users = [];
while($row = $result->fetch_assoc()) { $users[] = $row; }
echo json_encode(['success' => true, 'users' => $users]);
?>
