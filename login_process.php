<?php
require 'db.php';
header('Content-Type: application/json');
if (isset($db_error)) { echo json_encode(['success' => false, 'message' => 'DB Error: ' . $db_error]); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = trim($_POST['mobile'] ?? ''); $password = $_POST['password'] ?? '';
    if (empty($mobile) || empty($password)) { echo json_encode(['success' => false, 'message' => 'Fill all fields.']); exit; }

    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE mobile = ?");
    $stmt->bind_param("s", $mobile); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION = ['logged_in' => true, 'user_id' => $row['id'], 'role' => 'admin'];
        echo json_encode(['success' => true, 'role' => 'admin']); exit;
    }

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE mobile = ?");
    $stmt->bind_param("s", $mobile); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION = ['logged_in' => true, 'user_id' => $row['id'], 'role' => 'user'];
        echo json_encode(['success' => true, 'role' => 'user']); exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid mobile or password.']);
}
?>
