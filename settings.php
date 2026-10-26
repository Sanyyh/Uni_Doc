
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings</title>
    <link rel="stylesheet" href="style_settings.css"/>

  </head>
<main class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-6 text-blue-700">Account Settings</h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Profile Info -->
    <form class="bg-white p-6 rounded shadow space-y-4">
      <h2 class="text-xl font-semibold text-blue-600">Edit Profile</h2>
      <input type="text" placeholder="Full Name" class="w-full border p-2 rounded" required />
      <input type="text" placeholder="Username" class="w-full border p-2 rounded" required />
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Profile</button>
    </form>

    <!-- Change Email -->
    <form class="bg-white p-6 rounded shadow space-y-4">
      <h2 class="text-xl font-semibold text-blue-600">Change Email</h2>
      <input type="email" placeholder="Current Email" class="w-full border p-2 rounded" required />
      <input type="email" placeholder="New Email" class="w-full border p-2 rounded" required />
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Change Email</button>
    </form>

    <!-- Change Password -->
    <form class="bg-white p-6 rounded shadow space-y-4">
      <h2 class="text-xl font-semibold text-blue-600">Change Password</h2>
      <input type="password" placeholder="Current Password" class="w-full border p-2 rounded" required />
      <input type="password" placeholder="New Password" class="w-full border p-2 rounded" required />
      <input type="password" placeholder="Confirm New Password" class="w-full border p-2 rounded" required />
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Password</button>
    </form>

    <!-- Dark Mode -->
    <form class="bg-white p-6 rounded shadow space-y-4">
      <h2 class="text-xl font-semibold text-blue-600">Preferences</h2>
      <label for="darkmode" class="flex items-center gap-4">
        <span>Enable Dark Mode</span>
        <label class="toggle-switch">
          <input type="checkbox" id="darkmode">
          <span class="toggle-slider"></span>
        </label>
      </label>
    </form>

    <!-- Delete Account -->
    <form class="bg-white p-6 rounded shadow space-y-4 border border-red-300">
      <h2 class="text-xl font-semibold text-red-600">Danger Zone</h2>
      <p class="text-gray-700">This will permanently delete your account and all files.</p>
      <button type="button" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete Account</button>
    </form>
  </div>
</main>
