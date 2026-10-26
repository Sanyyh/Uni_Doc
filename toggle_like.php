<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit();
}

$userId = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

$uploadId = intval($data['uploadId']);
$like = filter_var($data['like'], FILTER_VALIDATE_BOOLEAN);

if ($like) {
  // Add like
  $stmt = $conn->prepare("INSERT IGNORE INTO likes (upload_id, user_id) VALUES (?, ?)");
  $stmt->bind_param('ii', $uploadId, $userId);
  $stmt->execute();
} else {
  // Remove like
  $stmt = $conn->prepare("DELETE FROM likes WHERE upload_id = ? AND user_id = ?");
  $stmt->bind_param('ii', $uploadId, $userId);
  $stmt->execute();
}

// Get new like count and user liked status
$stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE upload_id = ?");
$stmt->bind_param('i', $uploadId);
$stmt->execute();
$result = $stmt->get_result();
$like_count = $result->fetch_assoc()['like_count'] ?? 0;

$stmt = $conn->prepare("SELECT 1 FROM likes WHERE upload_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('ii', $uploadId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$user_liked = $result->num_rows > 0 ? 1 : 0;

echo json_encode([
  'success' => true,
  'like_count' => $like_count,
  'user_liked' => $user_liked
]);
