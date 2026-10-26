<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit;
}

$userId = $_SESSION['id'];
$comment = trim($_POST['comment'] ?? '');
$fileId = intval($_POST['file_id'] ?? 0);

if ($comment === '' || $fileId === 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid input']);
  exit;
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO comments (file_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $fileId, $userId, $comment);

if ($stmt->execute()) {
  // Optional: fetch user name
  $userQuery = $conn->prepare("SELECT first_name FROM user WHERE id = ?");
  $userQuery->bind_param("i", $userId);
  $userQuery->execute();
  $userResult = $userQuery->get_result()->fetch_assoc();

  echo json_encode([
    'success' => true,
    'username' => $userResult['first_name'] ?? 'You',
  ]);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
