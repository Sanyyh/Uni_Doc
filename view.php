<?php include 'header.php'; include 'db.php'; ?>
<main class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4 text-blue-700">View Shared Files</h1>

  <!-- Filter Form -->
  <form method="GET" class="bg-white p-4 rounded shadow mb-6 flex flex-wrap gap-4">
    <input type="text" name="search" placeholder="Search filename..." class="border p-2 rounded w-60" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="type" class="border p-2 rounded">
      <option value="">All Types</option>
      <option value="pdf" <?= ($_GET['type'] ?? '') == 'pdf' ? 'selected' : '' ?>>PDF</option>
      <option value="docx" <?= ($_GET['type'] ?? '') == 'docx' ? 'selected' : '' ?>>DOCX</option>
      <option value="jpg" <?= ($_GET['type'] ?? '') == 'jpg' ? 'selected' : '' ?>>JPG</option>
      <option value="png" <?= ($_GET['type'] ?? '') == 'png' ? 'selected' : '' ?>>PNG</option>
    </select>
    <input type="date" name="date" class="border p-2 rounded" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
  </form>

  <!-- Files List -->
  <div class="bg-white p-4 rounded shadow">
    <?php
    // Build query with filters
    $query = "SELECT * FROM files WHERE 1";
    $params = [];

    if (!empty($_GET['search'])) {
      $query .= " AND filename LIKE ?";
      $params[] = "%" . $_GET['search'] . "%";
    }

    if (!empty($_GET['type'])) {
      $query .= " AND filetype = ?";
      $params[] = $_GET['type'];
    }

    if (!empty($_GET['date'])) {
      $query .= " AND DATE(uploaded_at) = ?";
      $params[] = $_GET['date'];
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($files) {
      foreach ($files as $file) {
        echo "<div class='mb-2 p-2 border-b'>
                <p class='text-sm font-semibold'>{$file['filename']}</p>
                <p class='text-xs text-gray-500'>Type: {$file['filetype']} | Uploaded: {$file['uploaded_at']}</p>
                <a href='uploads/{$file['filename']}' class='text-blue-500 hover:underline' download>Download</a>
              </div>";
      }
    } else {
      echo "<p class='text-gray-500'>No files found.</p>";
    }
    ?>
  </div>
</main>
