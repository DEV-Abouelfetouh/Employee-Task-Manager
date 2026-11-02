<?php
require 'db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || empty($role) || empty($mobile)) {
        echo json_encode(['success' => false, 'message' => 'Name, role, and mobile are required.']); exit;
    }

    if ($id > 0) {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, role=?, mobile=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $role, $mobile, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, role=?, mobile=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $role, $mobile, $id);
        }
    } else {
        $hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : '$2y$10$ZElHxKxfvZLeJJG0VrfLdewUim.LN4rKJIU6qJkHd.gPSPs7YKwoO';
        $stmt = $conn->prepare("INSERT INTO users (name, role, mobile, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $role, $mobile, $hash);
    }

    if ($stmt->execute()) { echo json_encode(['success' => true]); } 
    else { echo json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->error]); }
}
?>
