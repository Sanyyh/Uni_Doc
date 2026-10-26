<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['id'];

include 'includes/db.php';

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update notifications']);
}

$stmt->close();
$conn->close();
?>
