<!-- Header/Navigation -->
<header class="bg-white shadow-sm sticky top-0 z-50">
  <div
    class="container mx-auto px-4 py-3 flex items-center justify-between">
    <!-- Logo -->
    <div class="flex items-center">
      <a href="index.php" class="text-2xl font-['Segoe UI', Tahoma, sans-serif] text-primary">UniDoc</a>
    </div>
    <!-- Search Bar -->
    <div class="hidden md:flex items-center flex-1 max-w-2xl mx-8">
      <div class="relative w-full">
        <input
          type="text"
          placeholder="Search for resources, subjects, courses..."
          class="w-full py-2 pl-10 pr-4 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm" />
        <div
          class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 flex items-center justify-center text-gray-400">
          <i class="ri-search-line"></i>
        </div>
        <button
          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-500 hover:text-primary">
          <div class="w-5 h-5 flex items-center justify-center">
            <i class="ri-filter-3-line"></i>
          </div>
        </button>
      </div>
    </div>
    <!-- Right Navigation -->
    <div class="flex items-center space-x-4">
      <button
        class="bg-primary text-white px-4 py-2 rounded-button whitespace-nowrap flex items-center gap-2 hover:bg-primary/90">
        <div class="w-5 h-5 flex items-center justify-center">
          <i class="ri-upload-2-line"></i>
        </div>
        <span class="hidden md:inline">Upload</span>
      </button>
      <!-- Notification Bell -->
      <!-- <div class="relative"> -->
      <!-- Bell Icon Button -->
      <!-- <div
    id="notificationButton"
    class="w-8 h-8 flex items-center justify-center cursor-pointer relative"
  >
    <i class="ri-notification-3-line text-gray-600"></i>
    <span
      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
      >3</span
    >
  </div> -->

      <div style="position: relative; cursor: pointer;" id="notificationBell">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 24c1.104 0 2-.895 2-2H10c0 1.105.896 2 2 2zm6.364-6c.136 0 .136-1.924 0-2l-1.588-1.583C16.805 14.144 16 12.518 16 10.5V8a4 4 0 10-8 0v2.5c0 2.018-.806 3.644-1.776 4.917L4 16c-.136.076-.136 2 0 2h14.364z" />
        </svg>
        <span id="notificationCount" style="position:absolute; top:-6px; right:-6px; background:red; color:#fff; font-size:12px; padding:2px 6px; border-radius:50%; display:none;">0</span>
      </div>

      <div id="notificationDropdown" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; width:300px; max-height:400px; overflow-y:auto; box-shadow: 0 2px 8px rgba(0,0,0,0.2); z-index:100;">
        <ul id="notificationList" style="list-style:none; margin:0; padding:10px;"></ul>
      </div>

      <script>
        const bell = document.getElementById('notificationBell');
        const countSpan = document.getElementById('notificationCount');
        const dropdown = document.getElementById('notificationDropdown');
        const list = document.getElementById('notificationList');


        let lastCount = 0;

        function fetchNotifications() {
          fetch('fetch_notifications.php')
            .then(res => res.json())
            .then(data => {
              if (data.count > 0) {
                countSpan.textContent = data.count;
                countSpan.style.display = 'inline-block';

                if (data.count > lastCount) {
                  // New notifications arrived, animate badge
                  countSpan.classList.add('pulse-animation');
                  setTimeout(() => countSpan.classList.remove('pulse-animation'), 600);
                }
              } else {
                countSpan.style.display = 'none';
              }

              lastCount = data.count;

              list.innerHTML = '';
              data.notifications.forEach(n => {
                const li = document.createElement('li');
                li.style.padding = '8px';
                li.style.borderBottom = '1px solid #eee';
                li.style.cursor = 'pointer';
                li.textContent = n.message + ' (' + new Date(n.created_at).toLocaleTimeString() + ')';
                list.appendChild(li);

                // Add click event to mark notification as read
                li.addEventListener('click', () => {
                  fetch('mark_single_notification_read.php', {
                      method: 'POST',
                      headers: {
                        'Content-Type': 'application/json'
                      },
                      body: JSON.stringify({
                        id: n.id
                      })
                    })
                    .then(() => {
                      // Remove clicked notification from UI
                      li.remove();

                      // Update count badge
                      let currentCount = parseInt(countSpan.textContent) || 0;
                      currentCount = currentCount - 1;
                      if (currentCount <= 0) {
                        countSpan.style.display = 'none';
                      } else {
                        countSpan.textContent = currentCount;
                      }
                    })
                    .catch(err => console.error('Error marking notification read:', err));
                });
              });
            })
            .catch(e => console.error('Fetch error:', e));
        }
        document.addEventListener('DOMContentLoaded', fetchNotifications);
        document.addEventListener('click', (e) => {
          if (bell.contains(e.target)) {
            // Toggle dropdown on bell click
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

            if (dropdown.style.display === 'block') {
              // Mark all notifications read when dropdown opens
              fetch('mark_notifications_read.php', {
                method: 'POST'
              });
              countSpan.style.display = 'none';
            }
          } else if (!dropdown.contains(e.target)) {
            // Hide dropdown when clicking outside both bell and dropdown
            dropdown.style.display = 'none';
          }
        });



        // Initial fetch on page load
        document.addEventListener('DOMContentLoaded', fetchNotifications);

        // Optional: poll for new notifications every 30 seconds
        setInterval(fetchNotifications, 30000);
      </script>



      <div class="flex items-center cursor-pointer">
        <!-- Profile Dropdown -->
        <div class="relative group">
          <!-- Profile Image Button -->
          <div
            id="profileButton"
            class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden cursor-pointer">
            <div class="w-16 h-16 rounded-full overflow-hidden border">
              <img src="uploads/profile/icon.png" alt="Profile" class="object-cover w-full h-full">


              <!-- Dropdown Menu -->
              <div
                id="profileDropdown"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden z-50">
                <a
                  href="profile.php"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                <a
                  href="settings.php"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>

                <div class="px-4 py-2 text-sm text-gray-700 flex items-center justify-between hover:bg-gray-100">
                  <span>Dark Mode</span>
                  <label class="toggle-switch">
                    <input type="checkbox" id="darkmode-toggle">
                    <span class="toggle-slider"></span>
                  </label>
                </div>

                <a
                  href="logout.php"
                  class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log out</a>
              </div>

            </div>
          </div>
        </div>
</header>
<!-- Main Content Area -->
<section class="py-12 bg-white">
  <div class="container mx-auto px-4">
    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 mb-8">
      <?php
      $currentPage = basename($_SERVER['PHP_SELF']);
      ?>



      <!-- Tabs (Left) -->
      <form method="post" class="flex gap-2">
        <button type="submit" name="allresource"
          class="tab-button px-4 py-2 font-medium <?= $currentPage == 'index.php' ? 'text-primary active' : 'text-gray-600' ?>">
          All Resources
        </button>
        <button type="submit" name="myupload"
          class="tab-button px-4 py-2 font-medium <?= $currentPage == 'myupload.php' ? 'text-primary active' : 'text-gray-600' ?>">
          My Uploads
        </button>
        <button type="submit" name="saveditems"
          class="tab-button px-4 py-2 font-medium <?= $currentPage == 'saveditems.php' ? 'text-primary active' : 'text-gray-600' ?>">
          Saved Items
        </button>
      </form>

      <!-- Sort (Right) -->
      <form id="sortForm" method="GET" class="flex items-center gap-2 ml-auto">
        <label class="text-sm text-gray-600 whitespace-nowrap">Sort by:</label>
        <div class="border rounded-md px-2 py-1 bg-white">
          <select name="sort" onchange="document.getElementById('sortForm').submit()" class="text-sm cursor-pointer bg-transparent outline-none border-none">
            <option value="recent" <?= isset($_GET['sort']) && $_GET['sort'] == 'recent' ? 'selected' : '' ?>>Recent</option>
            <option value="liked" <?= isset($_GET['sort']) && $_GET['sort'] == 'liked' ? 'selected' : '' ?>>Most Liked</option>
            <option value="az" <?= isset($_GET['sort']) && $_GET['sort'] == 'az' ? 'selected' : '' ?>>A-Z</option>
          </select>
        </div>
      </form>

    </div>

    <!-- Main Content Grid -->
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar -->
      <div class="w-full md:w-64 shrink-0">
        <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
          <h3 class="font-medium text-gray-900 mb-4">Quick Filters</h3>
          <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">
              File Types
            </h4>
            <div class="space-y-2">
              <label class="custom-checkbox flex items-center gap-2">
                <input type="checkbox" checked />
                <span class="checkmark"></span>
                <span class="text-sm">All Files</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="checkbox" />
                <span class="checkmark"></span>
                <span class="text-sm">PDF Documents</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="checkbox" />
                <span class="checkmark"></span>
                <span class="text-sm">Word Documents</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="checkbox" />
                <span class="checkmark"></span>
                <span class="text-sm">Spreadsheets</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="checkbox" />
                <span class="checkmark"></span>
                <span class="text-sm">Images</span>
              </label>
            </div>
          </div>
          <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">
              Date Uploaded
            </h4>
            <div class="space-y-2">
              <label class="custom-checkbox flex items-center gap-2">
                <input type="radio" name="date" checked />
                <span class="checkmark"></span>
                <span class="text-sm">Last 24 hours</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="radio" name="date" />
                <span class="checkmark"></span>
                <span class="text-sm">Last week</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="radio" name="date" />
                <span class="checkmark"></span>
                <span class="text-sm">Last month</span>
              </label>
              <label class="custom-checkbox flex items-center gap-2">
                <input type="radio" name="date" />
                <span class="checkmark"></span>
                <span class="text-sm">All time</span>
              </label>
            </div>
          </div>

          <button
            class="w-full py-2 text-primary text-sm font-medium hover:underline">
            Clear All Filters
          </button>
        </div>
      </div>

      <script>
        document.addEventListener("DOMContentLoaded", function() {
          const profileBtn = document.getElementById("profileButton");
          const dropdown = document.getElementById("profileDropdown");

          document.addEventListener("click", function(e) {
            if (profileBtn.contains(e.target)) {
              dropdown.classList.toggle("hidden");
            } else if (!dropdown.contains(e.target)) {
              dropdown.classList.add("hidden");
            }
          });
        });
      </script>
      