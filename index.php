<?php
session_start();
include 'includes/db.php'; // DB connection

// Redirect based on form buttons
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

// Check if user is logged in
if (!isset($_SESSION['id'])) {
  header('Location: login.php');
  exit();
}

$userId = $_SESSION['id'];
$itemsPerPage = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

$courses = [];
$courseQuery = $conn->query("SELECT id, name FROM courses ORDER BY name ASC");
while ($row = $courseQuery->fetch_assoc()) {
  $courses[$row['id']] = $row['name'];
}
$sort = $_GET['sort'] ?? 'recent';

switch ($sort) {
  case 'liked':
    $orderBy = 'likes DESC';
    break;
  case 'az':
    $orderBy = 'title ASC';
    break;
  case 'recent':
  default:
    $orderBy = 'createdat DESC';
    break;
}

$sql = "SELECT * FROM resources ORDER BY $orderBy";

// Main paginated, sorted query
$sql = "
SELECT
  upload.id,
  upload.title,
  upload.course,
  upload.subject,
  upload.descrip,
  upload.createdat,
  upload.url,
  upload.thumbnail,
  upload.type,
  courses.name AS course_name,
  user.first_name,
  user.last_name,
  user.depmt,
  user.profile,
  dept.name AS user_department
FROM upload
LEFT JOIN courses ON upload.course = courses.id
LEFT JOIN user ON upload.userid = user.id
LEFT JOIN courses AS dept ON user.depmt = dept.id
ORDER BY upload.createdat DESC
LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $itemsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM upload ";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $itemsPerPage);
?>

<?php
$profile = $profile ?? '';
$defaultImg = "uploads/profile/icon.png"; // <-- ensure icon is in same folder
$userImg = "uploads/profile/$profile";
$profileImg = (!empty($profile) && file_exists($userImg)) ? $userImg : $defaultImg;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UniDoc - Collaborative Learning Platform</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <script src="script.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: "#4f46e5",
            secondary: "#818cf8"
          },
          borderRadius: {
            none: "0px",
            sm: "4px",
            DEFAULT: "8px",
            md: "12px",
            lg: "16px",
            xl: "20px",
            "2xl": "24px",
            "3xl": "32px",
            full: "9999px",
            button: "8px",
          },
        },
      },
    };

    // Example JS: Add inside your click listener for .viewCard
    document.addEventListener("DOMContentLoaded", () => {
      document.querySelectorAll('.viewCard').forEach(card => {
        card.addEventListener('click', () => {
          const courseName = card.dataset.courseName || "-";
          const courseId = card.dataset.courseId || "";
          const subject = card.dataset.subject || "-";
          const description = card.dataset.description || "-";
          const uploaderName = card.dataset.uploaderName || "-";
          const uploaderDept = card.dataset.uploaderDept || "-";
          const uploadedTime = card.dataset.uploadedTime || "-";
          const pdfCoverUrl = card.dataset.pdfCoverUrl || "";
          const pdfUrl = card.dataset.pdfUrl || "";

          // Set content
          document.getElementById('fileCourse').textContent = courseName;
          document.getElementById('courseInput').value = courseId;

          document.getElementById('fileSubject').textContent = subject;
          document.getElementById('fileSubjectInput').value = subject;

          document.getElementById('fileDescription').textContent = description;
          document.getElementById('fileDescriptionInput').value = description;

          document.getElementById('uploaderName').textContent = uploaderName;
          document.getElementById('uploaderDept').textContent = uploaderDept;
          document.getElementById('uploadedTime').textContent = uploadedTime;

          document.getElementById('pdfCoverPreview').src = pdfCoverUrl;
          document.getElementById('downloadBtn').href = pdfUrl;

          document.getElementById('pdfPreviewContainer').onclick = () => {
            window.open(pdfUrl, "_blank");

            document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('.viewCard').forEach(card => {
    card.addEventListener('click', () => {
      // Existing logic...
      const fileId = card.dataset.id;

      // ✅ Inject fileId into the comment form's data attribute
      const commentForm = document.getElementById('commentForm');
      commentForm.setAttribute('data-file-id', fileId);

      // Optional: Clear old comments if you're going to load dynamically
      document.getElementById('commentsList').innerHTML = `<p class="text-gray-500 italic">No comments yet.</p>`;
    });
  });
});

          };

          // Show modal
          document.getElementById('viewCard').classList.remove('hidden');
        });
      });
    });
    document.addEventListener('DOMContentLoaded', () => {
      const filters = document.querySelectorAll('#courseFilters .filter-badge');
      const cards = document.querySelectorAll('.file-item');

      filters.forEach(filter => {
        filter.addEventListener('click', () => {
          const selectedCourse = filter.getAttribute('data-filter');

          // Reset styles on all filters
          filters.forEach(f => {
            f.classList.remove('bg-primary', 'text-white');
            f.classList.add('bg-gray-100');
          });

          // Highlight clicked filter
          filter.classList.add('bg-primary', 'text-white');
          filter.classList.remove('bg-gray-100');

          // Show/hide cards based on filter
          cards.forEach(card => {
            const cardCourse = card.getAttribute('data-course-name') || '';

            if (selectedCourse === 'All' || cardCourse === selectedCourse) {
              card.style.display = '';
            } else {
              card.style.display = 'none';
            }
          });
        });
      });
    });
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
  <link rel="stylesheet" href="style.css" />

</head>

<body class="flex flex-col min-h-screen">
  <?php
  include('includes\header.php');
  ?>
  <!-- Resource Grid -->
  <div class="flex-1 ">
    <div class="mb-4 flex flex-wrap gap-2" id="courseFilters">
      <span
        class="filter-badge px-3 py-1 bg-primary text-white text-sm rounded-full cursor-pointer"
        data-filter="All">All Files</span>

      <?php
      foreach ($courses as $courseId => $courseName) {
        echo '<span class="filter-badge px-3 py-1 bg-gray-100 text-sm rounded-full cursor-pointer" data-filter="' . htmlspecialchars($courseName) . '">' . htmlspecialchars($courseName) . '</span>';
      }
      ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
      ?>
          <!-- Resource Card -->
          <span class="viewCard file-item" data-id="<?= $row['id'] ?>"
            data-title="<?= $row['title']   ?>"
            data-course-id="<?= htmlspecialchars($row['course']) ?>"
            data-course-name="<?= htmlspecialchars($row['course_name']) ?>"
            data-subject="<?= $row['subject']   ?>"
            data-description="<?= $row['descrip']   ?>"
            data-uploader-name="<?= $row['first_name'] . ' ' . $row['last_name']   ?> "
            data-uploader-dept="<?= htmlspecialchars($row['user_department']) ?>"
            data-uploaded-time="<?= $row['createdat']   ?>"
            data-pdf-url="<?= 'uploads/files/' . $row['url'] ?>"
            data-pdf-cover-url="<?= 'uploads/thumbnail/' . pathinfo($row['thumbnail'], PATHINFO_FILENAME) . '.jpg' ?>">
            <div class="resource-card bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
              <div class="h-40 bg-gray-100 relative">
                <img
                  src="<?= "" . $row['thumbnail'] ?>"
                  alt="Resource Preview"
                  class="w-full h-full object-cover object-top" />
                <div
                  class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-sm cursor-pointer">
<div
  class="save-btn w-6 h-6 flex items-center justify-center text-gray-500 hover:text-primary"
  data-file-id="<?= $row['id'] ?>"
  title="Save this item"
  style="cursor: pointer;">
  <i class="ri-bookmark-line"></i>
</div>
                </div>
              </div>
              <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h3 class="font-medium text-gray-900">
                      <?= $row['title'] ?>
                    </h3>
                    <p class="text-sm text-gray-500">
                      <?= $row['title'] ?>
                    </p>
                  </div>
                  <span
                    class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                    <?php
                    $ext = strtolower(pathinfo($row['url'], PATHINFO_EXTENSION));
                    $typeLabel = match ($ext) {
                      'pdf'         => 'PDF',
                      'doc', 'docx' => 'DOCX',
                      'ppt', 'pptx' => 'PPTX',
                      'xls', 'xlsx' => 'XLSX',
                      'jpg', 'jpeg' => 'JPG',
                      'png'         => 'PNG',
                      default       => strtoupper($ext)
                    };
                    echo $typeLabel;
                    ?>

                  </span>
                </div>
                <div class="flex items-center gap-2 text-sm mb-3">
                  <button
                    class="flex items-center gap-1 text-gray-600 hover:text-primary">
                    <div class="w-4 h-4 flex items-center justify-center">
                      <i class="ri-thumb-up-line"></i>
                    </div>
                    <span>0</span>
                  </button>
                </div>
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full overflow-hidden">
                      <img
                        src="<?= "uploads/profile/" . $row['profile'] ?>"
                        alt="User"
                        class="w-full h-full object-cover" />
                    </div>
                    <span class="text-xs text-gray-600"><?= $row['first_name'] . " " . $row['last_name'] ?></span>
                  </div>
                  <button
                    class="bg-primary text-white p-1.5 rounded-button whitespace-nowrap hover:bg-primary/90">
                    <div class="w-4 h-4 flex items-center justify-center">
                      <i class="ri-download-line"></i>
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </span>
      <?php
        }
      } else {
        echo "<p>You have no no files. :(</p>";
      }
      ?>
    </div>
  </div>
  </div>
  </div>

  <!-- Pagination -->
  <div class="mt-8 flex justify-center">
    <nav class="flex items-center gap-1">

      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="w-9 h-9 flex items-center justify-center rounded border border-gray-300 text-gray-500 hover:bg-gray-50">
          <i class="ri-arrow-left-s-line"></i>
        </a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="w-9 h-9 flex items-center justify-center rounded <?= $i == $page ? 'bg-primary text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="w-9 h-9 flex items-center justify-center rounded border border-gray-300 text-gray-500 hover:bg-gray-50">
          <i class="ri-arrow-right-s-line"></i>
        </a>
      <?php endif; ?>

    </nav>
  </div>
  </div>
  <div class="w-full mt-8">
    <?php include('includes/footer.php'); ?>
  </div>

  <!-- Preview popup -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto"
    id="viewCard">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg w-full max-w-3xl shadow-xl my-8 relative">

        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b">
          <h2 class="text-xl font-semibold text-gray-900" id="fileTitle">File Title</h2>
          <button id="closeviewCard" class="text-gray-500 hover:text-gray-700">
            <div class="w-6 h-6 flex items-center justify-center">
              <i class="ri-close-line"></i>
            </div>
          </button>
        </div>

        <!-- Body: Grid with PDF preview on left, details on right -->
        <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- PDF Cover Preview (left column) -->
          <div class="col-span-1 flex flex-col items-center cursor-pointer" id="pdfPreviewContainer">
            <img
              src="<?php
                    if (file_exists($previewImage)) {
                      $webPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $previewImage);
                      echo $webPath;
                    } else {
                      echo '/path/to/pdf_icon.png'; // fallback PDF icon path relative to web root
                    }
                    ?>"
              alt="PDF Cover Preview"
              id="pdfCoverPreview"
              class="border rounded-md shadow-sm max-h-64"
              title="Click to open full file"
              onclick="window.open('<?php echo $pdfWebPath; ?>', '_blank')">
            <small class="mt-2 text-gray-600">Click image to view full PDF</small>
          </div>


          <!-- File Details (right two columns) -->
          <div class="col-span-1 md:col-span-2 space-y-4">
            <p><strong>Course:</strong> <span id="fileCourse">-</span></p>
            <p><strong>Subject:</strong> <span id="fileSubject">-</span><strong></strong><span id="fileTopic"></span></p>
            <p><strong>Description:</strong></p>
            <p id="fileDescription" class="whitespace-pre-wrap text-gray-700"></p>

            <hr class="my-4" />

            <div>
              <h3 class="font-semibold mb-2">Uploader Details</h3>
              <p><strong>Name:</strong> <span id="uploaderName">-</span></p>
              <p><strong>Department:</strong> <span id="uploaderDept">-</span></p>
              <p><strong>Uploaded on:</strong> <span id="uploadedTime">-</span></p>
            </div>

            <hr class="my-4" />

            <!-- Comments Section -->
            <div id="commentsSection" class="max-h-40 overflow-y-auto border rounded p-3 bg-gray-50">
              <h3 class="font-semibold mb-2">Comments</h3>
              <div id="commentsList" class="space-y-2 mb-4">
                <!-- Comments inserted here dynamically -->
                <p class="text-gray-500 italic">No comments yet.</p>
              </div>

<form id="commentForm" class="flex gap-2" data-file-id="">
  <input
    type="text"
    id="commentInput"
    placeholder="Write a comment..."
    class="flex-grow px-3 py-2 border rounded focus:outline-none"
    required />
  <button type="submit" class="bg-primary text-white px-4 rounded hover:bg-primary/90">
    Send
  </button>
</form>


            </div>
          </div>
        </div>

        <!-- Download button fixed bottom-right -->
        <div class="absolute bottom-6 left-6">
          <a
            href="#"
            id="downloadBtn"
            download
            class="bg-primary text-white px-6 py-3 rounded shadow hover:bg-primary/90 whitespace-nowrap">
            Download File
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php
  include('includes/topper.php');
  ?>
  <script>
document.querySelectorAll('.save-btn').forEach(el => {
  el.addEventListener('click', () => {
    const fileId = el.getAttribute('data-file-id');
    console.log('Clicked save for fileId:', fileId); // Debug

    fetch('save_item.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ fileId })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Item saved!');
      } else {
        alert(data.message || 'Failed to save');
      }
    })
    .catch(error => {
      console.error('Fetch error:', error);
      alert('Error occurred while saving.');
    });
  });
});

  </script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
  const commentForm = document.getElementById('commentForm');

  commentForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const commentInput = document.getElementById('commentInput');
    const comment = commentInput.value.trim();
    const fileId = this.getAttribute('data-file-id');

    if (!comment) {
      alert('Please enter a comment');
      return;
    }
    if (!fileId) {
      alert('No file selected');
      return;
    }

    const formData = new FormData();
    formData.append('comment', comment);
    formData.append('file_id', fileId);

    fetch('add_comment.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Comment added!');
        commentInput.value = '';
        // Optionally, add the new comment to the comments list here dynamically
        const commentsList = document.getElementById('commentsList');
        const newComment = document.createElement('p');
        newComment.textContent = `${data.username}: ${comment}`;
        commentsList.appendChild(newComment);
      } else {
        alert(data.message || 'Failed to add comment');
      }
    })
    .catch(() => alert('Error occurred while adding comment'));
  });

  // Also, make sure when you open the modal, you set data-file-id dynamically:
  document.querySelectorAll('.viewCard').forEach(card => {
    card.addEventListener('click', () => {
      const fileId = card.dataset.id;
      commentForm.setAttribute('data-file-id', fileId);

      // Clear old comments or load comments here as needed
      const commentsList = document.getElementById('commentsList');
      commentsList.innerHTML = `<p class="text-gray-500 italic">No comments yet.</p>`;
    });
  });
});

function loadComments(fileId) {
  fetch(`get_comments.php?file_id=${fileId}`)
    .then(res => res.json())
    .then(data => {
      const commentsList = document.getElementById('commentsList');
      commentsList.innerHTML = ''; // Clear previous

      if (data.success && data.comments.length > 0) {
        data.comments.forEach(comment => {
          const commentItem = document.createElement('p');
          commentItem.innerHTML = `<strong>${comment.username}</strong>: ${comment.comment}`;
          commentsList.appendChild(commentItem);
        });
      } else {
        commentsList.innerHTML = '<p class="text-gray-500 italic">No comments yet.</p>';
      }
    })
    .catch(err => {
      console.error('Failed to load comments:', err);
    });
}

</script>


</body>

</html>