<?php
session_start();
header('Content-Type: application/json');
include 'includes/db.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['fileId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing file ID']);
    exit;
}

$fileId = intval($data['fileId']);

// Check if user logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
$userid = $_SESSION['id'];

// Check if file exists in upload table
$stmtCheckFile = $conn->prepare("SELECT id FROM upload WHERE id = ?");
$stmtCheckFile->bind_param("i", $fileId);
$stmtCheckFile->execute();
$stmtCheckFile->store_result();
if ($stmtCheckFile->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file ID (upload not found)']);
    $stmtCheckFile->close();
    exit;
}
$stmtCheckFile->close();

// Optional: Check if already saved
$stmtCheck = $conn->prepare("SELECT id FROM saved_item WHERE user_id = ? AND doc_id = ?");
$stmtCheck->bind_param("ii", $userid, $fileId);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Already saved']);
    $stmtCheck->close();
    $conn->close();
    exit;
}
$stmtCheck->close();

// Insert saved item
$stmt = $conn->prepare("INSERT INTO saved_item (user_id, doc_id) VALUES (?, ?)");
$stmt->bind_param("ii", $userid, $fileId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Insert error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
