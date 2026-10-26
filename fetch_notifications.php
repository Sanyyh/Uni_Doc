<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

// Make sure user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'count' => 0,
        'notifications' => []
    ]);
    exit;
}

$userId = $_SESSION['id'];

// Fetch unread notifications for this user
$sql = "SELECT id, message, created_at FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Send back JSON response
echo json_encode([
    'count' => count($notifications),
    'notifications' => $notifications
]);
?>
