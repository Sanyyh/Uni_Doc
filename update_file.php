<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['id'], $data['course'], $data['subject'], $data['descrip'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$fileId = intval($data['id']);
$course = intval($data['course']);
$subject = trim($data['subject']);
$description = trim($data['descrip']);

// Security: Check ownership
$stmt = $conn->prepare("SELECT userid FROM upload WHERE id = ?");
$stmt->bind_param("issi", $course, $subject, $description, $fileId);
$stmt->execute();
$stmt->bind_result($ownerId);
$stmt->fetch();
$stmt->close();

if ($ownerId !== $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Update the file
$stmt = $conn->prepare("UPDATE upload SET course = ?, subject = ?, descrip = ? WHERE id = ?");
$stmt->bind_param("sssi", $course, $subject, $description, $fileId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error']);
}

$stmt->close();
$conn->close();
