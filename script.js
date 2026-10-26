 document.addEventListener("DOMContentLoaded", function () {
        const uploadBtn = document.querySelector("button.bg-primary");
        const uploadModal = document.getElementById("uploadModal");
        const closeUploadModal = document.getElementById("closeUploadModal");
        const cancelUpload = document.getElementById("cancelUpload");
        uploadBtn.addEventListener("click", function () {
          uploadModal.classList.remove("hidden");
          document.body.style.overflow = "hidden";
        });
        closeUploadModal.addEventListener("click", function () {
          uploadModal.classList.add("hidden");
          document.body.style.overflow = "auto";
        });
        cancelUpload.addEventListener("click", function () {
          uploadModal.classList.add("hidden");
          document.body.style.overflow = "auto";
        });
        // Close modal when clicking outside
        uploadModal.addEventListener("click", function (e) {
          if (e.target === uploadModal) {
            uploadModal.classList.add("hidden");
            document.body.style.overflow = "auto";
          }
        });
      });

document.addEventListener("DOMContentLoaded", function () {
  const viewCard = document.getElementById("viewCard");
  const closeviewCard = document.getElementById("closeviewCard");

  // Open preview popup and fill with data from clicked file-item
document.querySelectorAll(".file-item").forEach(item => {
  item.addEventListener("click", () => {
    const fileData = {
      fileId: item.dataset.id,
      title: item.dataset.title,
      course: item.dataset.course,
      subject: item.dataset.subject,
      description: item.dataset.description,
      uploaderName: item.dataset.uploaderName,
      uploaderDept: item.dataset.uploaderDept,
      uploadedTime: item.dataset.uploadedTime,
      pdfUrl: item.dataset.pdfUrl,
      pdfCoverUrl: item.dataset.pdfCoverUrl,
    };
    window.currentFileId = fileData.fileId;

    // Show modal and disable body scroll
    viewCard.classList.remove("hidden");
    document.body.style.overflow = "hidden";

    // Fill popup fields
    document.getElementById("fileTitle").textContent = fileData.title || "-";
    document.getElementById("fileCourse").textContent = fileData.course || "-";
    document.getElementById("fileSubject").textContent = fileData.subject || "-";
    document.getElementById("fileDescription").textContent = fileData.description || "-";

    document.getElementById("uploaderName").textContent = fileData.uploaderName || "-";
    document.getElementById("uploaderDept").textContent = fileData.uploaderDept || "-";
    document.getElementById("uploadedTime").textContent = fileData.uploadedTime || "-";

    // PDF preview image and click handler
    const pdfCoverPreview = document.getElementById("pdfCoverPreview");
    pdfCoverPreview.src = fileData.pdfCoverUrl || "default-cover.jpg";
    pdfCoverPreview.onclick = () => {
      window.open(fileData.pdfUrl, "_blank");
    };

    // Download button
    const downloadBtn = document.getElementById("downloadBtn");
    downloadBtn.href = fileData.pdfUrl || "#";
    downloadBtn.setAttribute("download", (fileData.title || "file") + ".pdf");
  });
});

  // Close modal handlers
  closeviewCard.addEventListener("click", closeModal);
  viewCard.addEventListener("click", function (e) {
    if (e.target === viewCard) closeModal();
  });

  function closeModal() {
    viewCard.classList.add("hidden");
    document.body.style.overflow = "auto";
  }



    document.addEventListener("DOMContentLoaded", function () {
    const notifBtn = document.getElementById("notificationButton");
    const notifDropdown = document.getElementById("notificationDropdown");

    document.addEventListener("click", function (e) {
      if (notifBtn.contains(e.target)) {
        notifDropdown.classList.toggle("hidden");
      } else if (!notifDropdown.contains(e.target)) {
        notifDropdown.classList.add("hidden");
      }
    });
  });
    const toggle = document.getElementById('darkmode-toggle');

  if (toggle) {
    toggle.addEventListener('change', function () {
      document.body.classList.toggle('dark', this.checked);
      localStorage.setItem('darkMode', this.checked);
    });

    window.addEventListener('DOMContentLoaded', () => {
      const isDark = localStorage.getItem('darkMode') === 'true';
      if (isDark) {
        document.body.classList.add('dark');
        toggle.checked = true;
      }
    });
  }

function fetchNotifications() {
  fetch('fetch_notifications.php')
    .then(res => res.json())
    .then(data => {
      console.log('Notification data:', data);
      // Update notification count and list here
    })
    .catch(error => console.error('Fetch error:', error));
}

fetchNotifications();
// Poll every 10 seconds (adjust as needed)
setInterval(fetchNotifications, 10000);


      document.addEventListener("DOMContentLoaded", function () {
        // Tab switching
        const tabButtons = document.querySelectorAll(".tab-button");
        tabButtons.forEach((button) => {
          button.addEventListener("click", function () {
            tabButtons.forEach((btn) => {
              btn.classList.remove("active");
              btn.classList.remove("text-primary");
              btn.classList.add("text-gray-600");
            });
            this.classList.add("active");
            this.classList.add("text-primary");
            this.classList.remove("text-gray-600");
          });
        });
        // Bookmark functionality
        const bookmarkButtons = document.querySelectorAll(
          ".resource-card .absolute .w-6"
        );
        bookmarkButtons.forEach((button) => {
          button.addEventListener("click", function () {
            const icon = this.querySelector("i");
            if (icon.classList.contains("ri-bookmark-line")) {
              icon.classList.remove("ri-bookmark-line");
              icon.classList.add("ri-bookmark-fill");
              this.classList.add("text-primary");
              this.classList.remove("text-gray-500");
            } else {
              icon.classList.remove("ri-bookmark-fill");
              icon.classList.add("ri-bookmark-line");
              this.classList.remove("text-primary");
              this.classList.add("text-gray-500");
            }
          });
        });
        // Custom checkbox and radio functionality
        const customCheckboxes = document.querySelectorAll(
          ".custom-checkbox input"
        );
        customCheckboxes.forEach((checkbox) => {
          checkbox.addEventListener("change", function () {
            const checkmark = this.nextElementSibling;
            if (this.type === "radio" && this.checked) {
              // For radio buttons, uncheck all others in the same group
              const name = this.getAttribute("name");
              document
                .querySelectorAll(`input[name="${name}"]`)
                .forEach((radio) => {
                  const otherCheckmark = radio.nextElementSibling;
                  if (radio !== this) {
                    otherCheckmark.style.backgroundColor = "#fff";
                    otherCheckmark.style.borderColor = "#d1d5db";
                  }
                });
              // Style the checked radio
              checkmark.style.backgroundColor = "#4f46e5";
              checkmark.style.borderColor = "#4f46e5";
            }
          });
        });
      });


  document.addEventListener("DOMContentLoaded", () => {
  // Elements
  const editBtn = document.getElementById('editBtn');
  const saveBtn = document.getElementById('saveBtn');
  const cancelBtn = document.getElementById('cancelBtn');

  const courseDisplay = document.getElementById('fileCourse');
  const courseInput = document.getElementById('fileCourseInput');

  const subjectDisplay = document.getElementById('fileSubject');
  const subjectInput = document.getElementById('fileSubjectInput');

  const descriptionDisplay = document.getElementById('fileDescriptionDisplay'); // make sure this ID matches your HTML!
  const descriptionInput = document.getElementById('fileDescriptionInput');

  const editActions = document.getElementById('editActions');

  // Helper toggle function
  function toggleEdit(isEditing) {
    courseDisplay.style.display = isEditing ? 'none' : 'inline';
    courseInput.style.display = isEditing ? 'inline-block' : 'none';

    subjectDisplay.style.display = isEditing ? 'none' : 'inline';
    subjectInput.style.display = isEditing ? 'inline-block' : 'none';

    descriptionDisplay.style.display = isEditing ? 'none' : 'block';
    descriptionInput.style.display = isEditing ? 'block' : 'none';

    editActions.style.display = isEditing ? 'flex' : 'none';
    editBtn.style.display = isEditing ? 'none' : 'inline-block';
  }

  // Edit click
  editBtn.addEventListener('click', () => {
    courseInput.value = courseDisplay.textContent.trim();
    subjectInput.value = subjectDisplay.textContent.trim();
    descriptionInput.value = descriptionDisplay.textContent.trim();
    toggleEdit(true);
  });

  // Cancel click
  cancelBtn.addEventListener('click', () => {
    toggleEdit(false);
  });

  // Save click
  saveBtn.addEventListener('click', () => {
    const updatedData = {
      id: window.currentFileId,
      course: courseInput.value.trim(),
      subject: subjectInput.value.trim(),
      description: descriptionInput.value.trim(),
    };

    fetch('update_file.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updatedData),
    })
      .then(res => res.json())
      .then(response => {
        if (response.success) {
          courseDisplay.textContent = updatedData.course;
          subjectDisplay.textContent = updatedData.subject;
          descriptionDisplay.textContent = updatedData.description;
          toggleEdit(false);
          alert('Details updated successfully!');
        } else {
          alert('Failed to update details: ' + (response.message || 'Unknown error'));
        }
      })
      .catch(() => alert('Error updating details'));
  });
});


document.addEventListener("DOMContentLoaded", function () {
  // Filter by department badges
  document.querySelectorAll(".filter-badge").forEach(badge => {
    badge.addEventListener("click", () => {
      const dept = badge.textContent.trim();
      document.querySelectorAll(".filter-badge").forEach(b => b.classList.remove("bg-primary", "text-white"));
      badge.classList.add("bg-primary", "text-white");

      document.querySelectorAll(".file-card").forEach(card => {
        const cardDept = card.dataset.department;
        if (dept === "All Departments" || cardDept === dept) {
          card.style.display = "block";
        } else {
          card.style.display = "none";
        }
      });
    });
  });

  // Filter by file type checkboxes
  document.querySelectorAll('input[type="checkbox"]').forEach(box => {
    box.addEventListener("change", () => {
      filterFiles();
    });
  });

  function filterFiles() {
    const types = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
      .map(cb => cb.nextElementSibling?.nextElementSibling?.textContent?.trim().toLowerCase());

    document.querySelectorAll(".file-card").forEach(card => {
      const type = card.dataset.type?.toLowerCase();
      if (types.includes(type)) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
  }
});
document.addEventListener('DOMContentLoaded', () => {
  const filters = document.querySelectorAll('#departmentFilters .filter-badge');
  const cards = document.querySelectorAll('.file-item');

  filters.forEach(filter => {
    filter.addEventListener('click', () => {
      const selectedDept = filter.getAttribute('data-filter');

      // Reset active style
      filters.forEach(f => {
        f.classList.remove('bg-primary', 'text-white');
        f.classList.add('bg-gray-100');
      });

      // Highlight clicked filter
      filter.classList.add('bg-primary', 'text-white');
      filter.classList.remove('bg-gray-100');

      // Filter cards
      cards.forEach(card => {
        const cardDept = card.getAttribute('data-department') || '';

        if (selectedDept === 'All Departments' || cardDept === selectedDept) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
});
});

