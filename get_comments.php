<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

$fileId = intval($_GET['file_id'] ?? 0);

if ($fileId === 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
  exit;
}

$sql = "
  SELECT comments.comment, comments.created_at, user.first_name 
  FROM comments 
  JOIN user ON comments.user_id = user.id 
  WHERE comments.file_id = ? 
  ORDER BY comments.created_at ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
  $comments[] = [
    'username' => $row['first_name'],
    'comment' => $row['comment'],
    'created_at' => $row['created_at']
  ];
}

echo json_encode(['success' => true, 'comments' => $comments]);
?>
