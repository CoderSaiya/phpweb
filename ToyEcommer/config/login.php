<?php
session_start();
require_once("./connect.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Username or password cannot be empty']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit();
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password_hash'])) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $row['role'];

        if ($row['role'] === 'admin') {
            echo json_encode([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'redirect' => 'admin/admin.php',
                'role' => 'admin'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'redirect' => 'index.php',
                'role' => 'user'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Mật khẩu không hợp lệ']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy tài khoản']);
}

$stmt->close();
$conn->close();