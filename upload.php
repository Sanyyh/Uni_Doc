<?php
session_start();
include('includes/db.php');

$userid = $_SESSION['id'] ?? null;
if (!$userid) {
    die("User not logged in.");
}
if (isset($_POST['myupload'])) {
    header("Location: myupload.php");
    exit();
}
if (isset($_POST['allresource'])) {
    header("Location: index.php");
    exit();
}
if (isset($_POST['saveditems'])) {
    header("Location: saveditems.php");
    exit();
}
// Get list of courses (id => name)
$courses = [];
$courseResult = $conn->query("SELECT id, name FROM courses ORDER BY name ASC");
while ($row = $courseResult->fetch_assoc()) {
    $courses[$row['id']] = $row['name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $course = $_POST['course'] ?? null;

    // Validate course ID from dropdown
    if (!array_key_exists($course, $courses)) {
        die("Invalid course selected.");
    }

    if (!$title || !$description || !$subject || !$course) {
        die("Please fill all required fields.");
    }

    if (!isset($_FILES['file'])) {
        die("No file uploaded.");
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("File upload error.");
    }

    if ($file['size'] > 50 * 1024 * 1024) {
        die("File too large. Max 50MB allowed.");
    }

    $allowedExtensions = ['pdf', 'docx', 'pptx', 'jpg', 'jpeg', 'png'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedExtensions)) {
        die("Unsupported file format.");
    }

    // Prepare directories
    $uploadDir = __DIR__ . '/uploads/files/';
    $thumbnailDir = __DIR__ . '/uploads/thumbnail/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!is_dir($thumbnailDir)) mkdir($thumbnailDir, 0755, true);

    // Generate filenames
    $newFileName = uniqid('file_', true) . '.' . $fileExt;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Failed to save uploaded file.");
    }

    // Thumbnail logic
    $thumbnailPathForDb = null;
    $thumbnailName = 'thumb_' . pathinfo($newFileName, PATHINFO_FILENAME) . '.jpg';
    $sourcePath = str_replace('\\', '/', realpath($destination));
    $thumbPath = str_replace('\\', '/', $thumbnailDir . $thumbnailName);
    $magickPath = '"C:\\Program Files\\ImageMagick-7.1.1-Q16-HDRI\\magick.exe"';

    if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
        $command = "$magickPath \"$sourcePath\" \"$thumbPath\"";
        exec($command);
    } elseif ($fileExt === 'pdf') {
        $command = "$magickPath -density 150 \"{$sourcePath}[0]\" \"$thumbPath\"";
        exec($command . " 2>&1", $output, $status);
    }

    if (file_exists($thumbPath)) {
        $thumbnailPathForDb = 'uploads/thumbnail/' . $thumbnailName;
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO upload (title, descrip, subject, course, url, thumbnail, userid) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $filePathForDb = $newFileName;
    $stmt->bind_param("sssssss", $title, $description, $subject, $course, $filePathForDb, $thumbnailPathForDb, $userid);

    if ($stmt->execute()) {
        $uploadId = $stmt->insert_id;

        // Notify self or others - adjust logic as needed
        $message = "You uploaded a new file: " . $title;
        $notify = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notify->bind_param("is", $userid, $message);
        $notify->execute();

        header("Location: index.php");
        exit();
    } else {
        echo "Database insert failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
