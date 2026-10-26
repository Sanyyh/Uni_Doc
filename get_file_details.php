

$fileId = intval($_GET['id']);
$conn = new mysqli(...);

$sql = "SELECT f.title, f.course, f.subject, f.description, f.pdf_path, f.pdf_cover_path,
        u.name AS uploader_name, u.department, f.uploaded_at
        FROM files f
        JOIN users u ON f.uploader_id = u.id
        WHERE f.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $fileId);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($file);