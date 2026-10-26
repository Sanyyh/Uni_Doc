<?php
session_start();
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

include 'includes/db.php';

// Query to get all uploads saved by this user
$sql = "SELECT u.* FROM upload u 
        INNER JOIN saved_item s ON u.id = s.doc_id
        WHERE s.user_id = ?
        ORDER BY u.createdat DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$sql = "
SELECT 
  u.id,
  u.title,
  u.course,
  u.subject,
  u.descrip,
  u.createdat,
  u.url,
  u.thumbnail,
  u.type,
  c.name AS course_name,
  usr.first_name,
  usr.last_name,
  usr.depmt,
  usr.profile
FROM upload u
INNER JOIN saved_item s ON u.id = s.doc_id
LEFT JOIN courses c ON u.course = c.id
LEFT JOIN user usr ON u.userid = usr.id
WHERE s.user_id = ?
ORDER BY u.createdat DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

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
  <title>Saved Items</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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

<body>

  <?php include 'includes/header.php'; ?>

  <div class="flex-1 ">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
      ?>
          <span class="viewCard file-item" data-id="<?= $row['id'] ?>"
            data-title="<?= $row['title'] ?>"
            data-course-id="<?= htmlspecialchars($row['course_name']) ?>"
            data-subject="<?= $row['subject'] ?>"
            data-description="<?= $row['descrip'] ?>"
            data-uploader-name="<?= $row['first_name'] . ' ' . $row['last_name'] ?>"
            data-uploader-dept="<?= $row['depmt'] ?> Department"
            data-uploaded-time="<?= $row['createdat'] ?>"
            data-pdf-url="<?= 'uploads/files/' . $row['url'] ?>"
            data-pdf-cover-url="<?= 'uploads/thumbnail/' . pathinfo($row['thumbnail'], PATHINFO_FILENAME) . '.jpg' ?>">

            <div class="resource-card bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
              <div class="h-40 bg-gray-100 relative">
                <img src="<?= $row['thumbnail'] ?>" alt="Resource Preview"
                  class="w-full h-full object-cover object-top" />
                <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-sm cursor-pointer">
                  <div class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-primary"
                    title="Saved item" style="cursor: default;">
                    <i class="ri-bookmark-fill"></i> <!-- solid bookmark since it's already saved -->
                  </div>
                </div>
              </div>
              <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <h3 class="font-medium text-gray-900"><?= $row['title'] ?></h3>
                    <p class="text-sm text-gray-500"><?= $row['title'] ?></p>
                  </div>
                  <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                    <?php
                    echo match ($row['type']) {
                      0 => 'PDF',
                      1 => 'DOCX',
                      2 => 'PPTX',
                      3 => 'XLSX',
                      4 => 'JPG',
                      5 => 'PNG',
                      default => '',
                    };
                    ?>
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full overflow-hidden">
                      <img src="<?= 'uploads/profile/' . $row['profile'] ?>" alt="User"
                        class="w-full h-full object-cover" />
                    </div>
                    <span class="text-xs text-gray-600"><?= $row['first_name'] . ' ' . $row['last_name'] ?></span>
                  </div>
                  <button class="bg-primary text-white p-1.5 rounded-button hover:bg-primary/90">
                    <i class="ri-download-line"></i>
                  </button>
                </div>
              </div>
            </div>
          </span>

      <?php
        }
      } else {
        echo "<p>You have no saved items.</p>";
      }
      ?>
    </div>
  </div>
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
              title="Click to open full PDF"
              onclick="window.open('<?php echo $pdfWebPath; ?>', '_blank')"
              <small class="mt-2 text-gray-600">Click image to view full PDF</small>
          </div>


          <!-- File Details (right two columns) -->
          <div class="col-span-1 md:col-span-2 space-y-4">
            <p><strong>Course:</strong> <span id="fileCourse">-</span></p>
            <p><strong>Subject:</strong> <span id="fileSubject">-</span><strong></strong> <span id="fileTopic"></span></p>
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

              <form id="commentForm" class="flex gap-2">
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
        <div class="absolute bottom-6 right-6">
          <a
            href="#"
            id="downloadBtn"
            download
            class="bg-primary text-white px-6 py-3 rounded shadow hover:bg-primary/90 whitespace-nowrap">
            Download PDF
          </a>
        </div>
      </div>
    </div>
  </div>
  </div>
  <?php
  include('includes/topper.php');
  ?>
  <script>
    document.querySelectorAll('.viewCard').forEach(el => {
      el.addEventListener('click', () => {
        document.getElementById('viewCard').classList.remove('hidden');
        document.getElementById('fileTitle').textContent = el.dataset.title;
        document.getElementById('fileCourse').textContent = el.dataset.courseId;
        document.getElementById('fileSubject').textContent = el.dataset.subject;
        document.getElementById('fileDescription').textContent = el.dataset.description;
        document.getElementById('uploaderName').textContent = el.dataset.uploaderName;
        document.getElementById('uploaderDept').textContent = el.dataset.uploaderDept;
        document.getElementById('uploadedTime').textContent = el.dataset.uploadedTime;
        document.getElementById('pdfCoverPreview').src = el.dataset.pdfCoverUrl;
        document.getElementById('downloadBtn').href = el.dataset.pdfUrl;
      });
    });

    document.getElementById('closeviewCard').addEventListener('click', () => {
      document.getElementById('viewCard').classList.add('hidden');
    });
  </script>

</body>

</html>