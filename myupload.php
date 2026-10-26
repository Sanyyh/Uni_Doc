<?php
session_start();
include 'includes/db.php';

// Redirect buttons
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

// User authentication
if (!isset($_SESSION['id'])) {
  header('Location: login.php');
  exit();
}

$userId = $_SESSION['id'];

// Load course list
$courses = [];
$courseQuery = $conn->query("SELECT id, name FROM courses ORDER BY name ASC");
while ($row = $courseQuery->fetch_assoc()) {
  $courses[$row['id']] = $row['name'];
}

// Pagination logic
$itemsPerPage = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Main query: fetch paginated uploads for this user
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
WHERE upload.userid = ?
ORDER BY upload.createdat DESC
LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $userId, $itemsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) AS total FROM upload WHERE userid = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("i", $userId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $itemsPerPage);

// (Optional) Notification helper
function addNotification($pdo, $user_id, $message) {
  $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
  $stmt->execute([$user_id, $message]);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Uploads</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="script.js"></script>
   <script>
  tailwind.config = {
    theme: {
      extend: {
        colors: { primary: "#4f46e5", secondary: "#818cf8" },
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

document.addEventListener("DOMContentLoaded", () => {
  // View card click logic
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

      document.getElementById('editBtn').dataset.fileId = card.dataset.id;
document.getElementById('saveBtn').dataset.fileId = card.dataset.id; // also for save button


      document.getElementById('pdfPreviewContainer').onclick = () => {
        window.open(pdfUrl, "_blank");
      };

      // Show modal
      document.getElementById('viewCard').classList.remove('hidden');
    });
  });

  // Edit button
  document.getElementById('editBtn').addEventListener('click', () => {
    document.getElementById('fileCourse').classList.add('hidden');
    document.getElementById('courseInput').classList.remove('hidden');

    document.getElementById('fileSubject').classList.add('hidden');
    document.getElementById('fileSubjectInput').classList.remove('hidden');

    document.getElementById('fileDescription').classList.add('hidden');
    document.getElementById('fileDescriptionInput').classList.remove('hidden');

    document.getElementById('editActions').classList.remove('hidden');
  });

  // Cancel button
  document.getElementById('cancelBtn').addEventListener('click', () => {
    document.getElementById('fileCourse').classList.remove('hidden');
    document.getElementById('courseInput').classList.add('hidden');

    document.getElementById('fileSubject').classList.remove('hidden');
    document.getElementById('fileSubjectInput').classList.add('hidden');


    document.getElementById('fileDescription').classList.remove('hidden');
    document.getElementById('fileDescriptionInput').classList.add('hidden');

    document.getElementById('editActions').classList.add('hidden');
  });

  // Close modal button
  document.getElementById('closeviewCard').addEventListener('click', () => {
    document.getElementById('viewCard').classList.add('hidden');
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
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"
    />
    <link rel="stylesheet" href="style.css"/>

</head>

<body class="flex flex-col min-h-screen">
  <?php
  include('includes\header.php');
  ?>
<div class="flex-1 ">
  <div class="mb-4 flex flex-wrap gap-2" id="courseFilters">
  <span
    class="filter-badge px-3 py-1 bg-primary text-white text-sm rounded-full cursor-pointer"
    data-filter="All"
  >All Courses</span>

  <?php
  foreach ($courses as $courseId => $courseName) {
      echo '<span class="filter-badge px-3 py-1 bg-gray-100 text-sm rounded-full cursor-pointer" data-filter="' . htmlspecialchars($courseName) . '">' . htmlspecialchars($courseName) . '</span>';
  }
  ?>
</div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php while ($row = $result->fetch_assoc()): ?>
<span class="viewCard file-item"
  data-id="<?= $row['id'] ?>"
  data-title="<?= $row['title'] ?>"
  data-course-id="<?= htmlspecialchars($row['course']) ?>"
  data-course-name="<?= htmlspecialchars($row['course_name']) ?>"
  data-subject="<?= $row['subject'] ?>"
  data-description="<?= $row['descrip'] ?>"
  data-uploader-name="<?= $row['first_name'] . ' ' . $row['last_name'] ?>"
  data-uploader-dept="<?= htmlspecialchars($row['user_department']) ?>"
  data-uploaded-time="<?= $row['createdat'] ?>"
  data-pdf-url="<?= 'uploads/files/' . $row['url'] ?>"
  data-pdf-cover-url="<?= 'uploads/thumbnail/' . pathinfo($row['thumbnail'], PATHINFO_FILENAME) . '.jpg' ?>">

      <div class="resource-card bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
        <div class="h-40 bg-gray-100 relative">
          <img src="<?= '' . htmlspecialchars($row['thumbnail']) ?>" alt="Resource Preview" class="w-full h-full object-cover object-top" />
          <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-sm cursor-pointer">
            <div class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-primary">
              <i class="ri-bookmark-line"></i>
            </div>
          </div>
        </div>
        <div class="p-4">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h3 class="font-medium text-gray-900"><?= htmlspecialchars($row['title']) ?></h3>
              <p class="text-sm text-gray-500"><?= htmlspecialchars($row['title']) ?></p>
            </div>
            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
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
            <button class="flex items-center gap-1 text-gray-600 hover:text-primary">
              <div class="w-4 h-4 flex items-center justify-center"><i class="ri-thumb-up-line"></i></div>
              <span>0</span>
            </button>
          </div>
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full overflow-hidden">
                <img src="<?= 'uploads/profile/' . htmlspecialchars($row['profile']) ?>" alt="User" class="w-full h-full object-cover" />
              </div>
              <span class="text-xs text-gray-600"><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></span>
            </div>
<div
  class="save-btn w-6 h-6 flex items-center justify-center text-gray-500 hover:text-primary"
  data-file-id="<?= $row['id'] ?>"
  title="Save this item"
  style="cursor: pointer;">
  <i class="ri-bookmark-line"></i>
</div>

          </div>
        </div>
      </div>
    </span>

<?php endwhile; ?>
  </div>
</div>
</div>
</div>

<!-- Pagination -->
<div class="flex justify-center mt-8 space-x-2">
  <nav class="flex items-center gap-1">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">&laquo; Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a 
      href="?page=<?= $i ?>" 
      class="px-3 py-1 <?= $i == $page ? 'bg-primary text-white' : 'bg-gray-200' ?> rounded hover:bg-gray-300">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next &raquo;</a>
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
        <div class="flex gap-2">
          <button id="editBtn" class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-1 rounded">
            Edit
          </button>
          <button id="closeviewCard" class="text-gray-500 hover:text-gray-700">
            <div class="w-6 h-6 flex items-center justify-center">
              <i class="ri-close-line"></i>
            </div>
          </button>
        </div>
      </div>

      <!-- Body: Grid with PDF preview on left, details on right -->
      <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- PDF Cover Preview (left column) -->
        <div class="col-span-1 flex flex-col items-center cursor-pointer" id="pdfPreviewContainer">
          <img
            src=""
            alt="PDF Cover Preview"
            id="pdfCoverPreview"
            class="border rounded-md shadow-sm max-h-64"
            title="Click to open full PDF"
          />
          <small class="mt-2 text-gray-600">Click image to view full file</small>
        </div>

<div class="col-span-1 md:col-span-2 space-y-6 flex flex-col justify-start">
<div class="col-span-1 md:col-span-2 space-y-4 flex flex-col">
  <div class="flex items-center gap-3">
    <p class="font-semibold w-28">Course:</p>
    <div class="flex-1">
      <span id="fileCourse" class="block">-</span>
      <select name="course" id="courseInput" class="hidden border border-gray-300 rounded px-3 py-1 w-full focus:outline-none">
        <?php foreach ($courses as $id => $name): ?>
          <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <p class="font-semibold w-28">Subject:</p>
    <div class="flex-1">
      <span id="fileSubject" class="block">-</span>
      <input type="text" id="fileSubjectInput" class="hidden border border-gray-300 rounded px-3 py-1 w-full" />
    </div>
  </div>

  <div class="flex flex-col gap-1">
    <p class="font-semibold">Description:</p>
    <p id="fileDescription" class="whitespace-pre-wrap text-gray-700"></p>
    <textarea id="fileDescriptionInput" class="hidden border border-gray-300 rounded px-3 py-1 w-full" rows="4"></textarea>
  </div>

  <!-- Action buttons for edit/cancel/save -->
<div id="editActions" class="hidden mt-4 space-x-2">
  <button id="saveBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded">Save</button>
  <button id="cancelBtn" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-1 rounded">Cancel</button>
</div>

  <hr class="my-4" />

  <div class="flex flex-col gap-1">
    <h3 class="font-semibold mb-2">Uploader Details</h3>
    <div class="flex items-center gap-2">
      <p class="font-semibold w-28">Name:</p>
      <p id="uploaderName" class="flex-1">-</p>
    </div>
    <div class="flex items-center gap-2">
      <p class="font-semibold w-28">Department:</p>
      <p id="uploaderDept" class="flex-1">-</p>
    </div>
    <div class="flex items-center gap-2">
      <p class="font-semibold w-28">Uploaded on:</p>
      <p id="uploadedTime" class="flex-1">-</p>
    </div>
  </div>

  <hr class="my-4" />

</div>
</div>


          <!-- Comments Section -->
           <div class="col-span-1 md:col-span-2">
          <div id="commentsSection" class="max-h-40 overflow-y-auto border rounded p-3 bg-gray-50">
            <h3 class="font-semibold mb-2">Comments</h3>
            <div id="commentsList" class="space-y-2 mb-4">
              <!-- Comments inserted here dynamically -->
              <p class="text-gray-500 italic">No comments yet.</p>
            </div>

            <form id="commentForm" class="flex gap-2">
              <input
                type="text"
                id="commentInput"
                placeholder="Write a comment..."
                class="flex-grow px-3 py-2 border rounded focus:outline-none"
                required
              />
              <button type="submit" class="bg-primary text-white px-4 rounded hover:bg-primary/90">
                Send
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Download button fixed bottom-right -->
<div class="absolute bottom-6 right-6">
  <a
    href="#"
    id="downloadBtn"
    download
    class="bg-primary text-white px-6 py-3 rounded shadow hover:bg-primary/90 whitespace-nowrap"
  >
    Download PDF
  </a>
</div>
</div>
    <?php
  include('includes/topper.php');
  ?>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
  const editBtn = document.getElementById('editBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const saveBtn = document.getElementById('saveBtn');

  const fileCourse = document.getElementById('fileCourse');
  const courseInput = document.getElementById('courseInput');

  const fileSubject = document.getElementById('fileSubject');
  const fileSubjectInput = document.getElementById('fileSubjectInput');

  const fileDescription = document.getElementById('fileDescription');
  const fileDescriptionInput = document.getElementById('fileDescriptionInput');

  const editActions = document.getElementById('editActions');

  editBtn.addEventListener('click', () => {
    // Switch from spans to inputs
    fileCourse.classList.add('hidden');
    courseInput.classList.remove('hidden');

    fileSubject.classList.add('hidden');
    fileSubjectInput.classList.remove('hidden');

    fileDescription.classList.add('hidden');
    fileDescriptionInput.classList.remove('hidden');

    editActions.classList.remove('hidden');
    editBtn.classList.add('hidden');

    // Initialize input values from spans
    courseInput.value = [...courseInput.options].find(opt => opt.text === fileCourse.textContent)?.value || '';
    fileSubjectInput.value = fileSubject.textContent;
    fileDescriptionInput.value = fileDescription.textContent;
  });

  cancelBtn.addEventListener('click', () => {
    // Hide inputs, show spans back
    fileCourse.classList.remove('hidden');
    courseInput.classList.add('hidden');

    fileSubject.classList.remove('hidden');
    fileSubjectInput.classList.add('hidden');

    fileDescription.classList.remove('hidden');
    fileDescriptionInput.classList.add('hidden');

    editActions.classList.add('hidden');
    editBtn.classList.remove('hidden');
  });

  saveBtn.addEventListener('click', () => {
    // Here you can add AJAX to save to backend if needed
    // For now just update the spans and exit edit mode

    // Update spans with input values
    const selectedCourseName = courseInput.options[courseInput.selectedIndex].text;
    fileCourse.textContent = selectedCourseName;

    fileSubject.textContent = fileSubjectInput.value;
    fileDescription.textContent = fileDescriptionInput.value;

    // Switch back to view mode
    fileCourse.classList.remove('hidden');
    courseInput.classList.add('hidden');

    fileSubject.classList.remove('hidden');
    fileSubjectInput.classList.add('hidden');

    fileDescription.classList.remove('hidden');
    fileDescriptionInput.classList.add('hidden');

    editActions.classList.add('hidden');
    editBtn.classList.remove('hidden');

    // TODO: send updated data to server with fetch/ajax if desired
  });
});

  </script>
  <script>
document.getElementById('saveBtn').addEventListener('click', function () {
  const fileId = document.getElementById('editBtn').dataset.fileId; // file ID must be stored in a data attribute
  const course = document.getElementById('courseInput').value;
  const subject = document.getElementById('fileSubjectInput').value.trim();
  const description = document.getElementById('fileDescriptionInput').value.trim();

  fetch('update_resource.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: fileId,
      course: course,
      subject: subject,
      descrip: description
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Updated successfully!");
      location.reload(); // Optional: refresh to reflect changes
    } else {
      alert("Failed to save changes.");
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert("Something went wrong while saving.");
  });
});
</script>

  <script>
document.getElementById('saveBtn').addEventListener('click', function () {
  const fileId = document.getElementById('editBtn').dataset.fileId; // assuming fileId is stored here
  const course = document.getElementById('courseInput').value;
  const subject = document.getElementById('fileSubjectInput').value;
  const description = document.getElementById('fileDescriptionInput').value;

  fetch('update_file.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      fileId: fileId,
      course: course,
      subject: subject,
      descrip: description
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Changes saved!');

      // Update UI
      document.getElementById('fileCourse').textContent = data.updated.course_name;
      document.getElementById('fileSubject').textContent = subject;
      document.getElementById('fileDescription').textContent = description;

      // Hide inputs again
      toggleEditMode(false);
    } else {
      alert('Save failed: ' + data.message);
    }
  })
  .catch(err => {
    alert('Error saving: ' + err.message);
  });
});

function toggleEditMode(isEditing) {
  document.getElementById('fileCourse').classList.toggle('hidden', isEditing);
  document.getElementById('courseInput').classList.toggle('hidden', !isEditing);
  document.getElementById('fileSubject').classList.toggle('hidden', isEditing);
  document.getElementById('fileSubjectInput').classList.toggle('hidden', !isEditing);
  document.getElementById('fileDescription').classList.toggle('hidden', isEditing);
  document.getElementById('fileDescriptionInput').classList.toggle('hidden', !isEditing);
  document.getElementById('editActions').classList.toggle('hidden', !isEditing);
}

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

</body>
</html>