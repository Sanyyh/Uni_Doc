<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing notification ID']);
    exit;
}

$id = intval($data['id']);

include 'includes/db.php'; // Use your usual DB connection file

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update notification']);
}

$stmt->close();
$conn->close();
?>

