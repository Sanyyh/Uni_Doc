<?php
if (!isset($conn)) {
  include 'db.php'; // adjust path if needed
}

// Load courses only if not already loaded
$courses = [];
$result = $conn->query("SELECT id, name FROM courses ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
  $courses[$row['id']] = $row['name'];
}
?>

<!-- Upload Modal -->
<div
  id="uploadModal"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div
    class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 relative">
    <!-- Close button -->
    <button
      id="closeUploadModal"
      class="absolute top-4 right-4 text-gray-600 hover:text-gray-900"
      title="Close"
      type="button">
      ✕
    </button>

    <h2 class="text-2xl font-semibold mb-6">Upload New Resource</h2>

    <form
      action="upload.php"
      method="post"
      enctype="multipart/form-data"
      id="uploadForm"
      class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
      <label class="flex flex-col">
        <span class="font-medium mb-1">Title <span class="text-red-500">*</span></span>
        <input
          type="text"
          name="title"
          required
          placeholder="Enter a descriptive title"
          class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
      </label>

      <label class="flex flex-col">
        <span class="font-medium mb-1">Description <span class="text-red-500">*</span></span>
        <textarea
          name="description"
          rows="3"
          required
          placeholder="Briefly describe this resource"
          class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
      </label>

      <label class="flex flex-col">
        <span class="font-medium mb-1">Subject <span class="text-red-500">*</span></span>
        <input
          type="text"
          name="subject"
          required
          placeholder="e.g., Computer Science"
          class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
      </label>

      <label class="flex flex-col">
        <span class="font-medium mb-1">Course <span class="text-red-500">*</span></span>
        <select
          name="course"
          required
          class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
          <option value="">-- Select Course --</option>
          <?php foreach ($courses as $id => $name): ?>
            <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($name) ?></option>
          <?php endforeach; ?>
        </select>
      </label>


      <label class="flex flex-col md:col-span-2">
        <span class="font-medium mb-1">Tags</span>
        <input
          type="text"
          name="tags"
          placeholder="Add tags separated by commas"
          class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
      </label>

      <label class="flex items-center md:col-span-2 justify-between border border-gray-200 rounded-lg p-3">
        <div>
          <h4 class="font-medium text-gray-900">Make this resource public</h4>
          <p class="text-xs text-gray-500">Anyone on StudentShare can view and download</p>
        </div>
        <input
          type="checkbox"
          name="is_public"
          checked
          class="w-5 h-5 cursor-pointer" />
      </label>

      <label class="flex flex-col md:col-span-2">
        <span class="font-medium mb-1">Upload File <span class="text-red-500">*</span></span>
        <input
          type="file"
          name="file"
          accept=".pdf,.docx,.pptx,.jpg,.jpeg,.png"
          required
          class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
      </label>

      <!-- Buttons -->
      <div class="md:col-span-2 flex justify-end gap-3 mt-6">
        <button
          id="cancelUpload"
          type="button"
          class="px-5 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">
          Cancel
        </button>
        <button
          type="submit"
          class="bg-primary text-white px-6 py-2 rounded hover:bg-primary/90">
          Upload Resource
        </button>
      </div>
    </form>
  </div>
</div>