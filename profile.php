<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $depmt = trim($_POST['depmt']);
    $indexNo = trim($_POST['index_no']);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    }

    // If password entered, check confirmation
    if (!$error && !empty($password)) {
        if ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    // Handle profile picture upload
    if (!$error && isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed)) {
            $newFileName = uniqid('profile_', true) . '.' . $ext;
            $uploadPath = 'uploads/profile/' . $newFileName;
            if (move_uploaded_file($_FILES['profile']['tmp_name'], $uploadPath)) {
                $conn->query("UPDATE user SET profile = '$newFileName' WHERE id = $userId");
            }
        }
    }

    // Update user info
    if (!$error) {
        $query = "UPDATE user SET first_name = ?, last_name = ?, email = ?, depmt = ?, index_no = ?";
        if (!empty($password)) {
            $query .= ", password = ?";
        }
        $query .= " WHERE id = ?";

        if (!empty($password)) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssi", $firstName, $lastName, $email, $depmt, $indexNo, $hashedPassword, $userId);
        } else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $firstName, $lastName, $email, $depmt, $indexNo, $userId);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT first_name, last_name, email, depmt, index_no, profile FROM user WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $email, $depmt, $indexNo, $profile);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Edit Profile - UniDoc</title>
        <script src="https://cdn.tailwindcss.com"></script>
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
                        button: "8px"
                    }
                }
            }
        };
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.getElementById('toggleSensitive');
            const section = document.getElementById('sensitiveSection');

            toggleBtn.addEventListener('click', () => {
                section.classList.toggle('hidden');
            });
        });
    </script>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
</head>

<body class="bg-blue-50 min-h-screen flex items-center justify-center">
    <div class="relative bg-white p-8 rounded shadow-md w-full max-w-lg">
        <!-- Close Button -->
        <a href="index.php" class="absolute top-4 right-4 text-blue-500 hover:text-blue-700 text-xl">
            <i class="ri-close-line"></i>
        </a>
        <h2 class="text-2xl font-bold mb-4">Edit Your Profile</h2>

        <?php if ($success): ?>
            <p class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- Profile Picture -->
            <div class="flex items-center gap-4">
                <?php
                $profilePath = "uploads/profile/$profile";
                $defaultPath = "Frontend/uploads/profile/icon.png";
                $profileImg = ($profile && file_exists($profilePath)) ? $profilePath : $defaultPath;
                ?>
                <img src="<?= $profileImg ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover border" />
                <input type="file" name="profile" accept="image/*" class="text-sm" />
            </div>

            <!-- Basic Info -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">First Name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required class="mt-1 w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required class="mt-1 w-full border rounded px-3 py-2" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Department</label>
                <input type="text" name="depmt" value="<?= htmlspecialchars($depmt) ?>" required class="mt-1 w-full border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block text-sm font-medium">Index Number</label>
                <input type="text" name="index_no" value="<?= htmlspecialchars($indexNo) ?>" required class="mt-1 w-full border rounded px-3 py-2" />
            </div>

            <!-- Toggle Button -->
            <div>
                <button type="button" id="toggleSensitive" class="text-primary text-sm font-medium hover:underline">
                    Edit Email or Password
                </button>
            </div>

            <!-- Hidden Email + Password Section -->
            <div id="sensitiveSection" class="hidden space-y-4 mt-2">
                <div>
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" class="mt-1 w-full border rounded px-3 py-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">New Password</label>
                        <input type="password" name="password" class="mt-1 w-full border rounded px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Confirm Password</label>
                        <input type="password" name="confirm_password" class="mt-1 w-full border rounded px-3 py-2" />
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <button
                type="submit"
                class="bg-primary text-white px-6 py-3 rounded shadow hover:bg-primary/90 whitespace-nowrap">
                Save Changes
            </button>
        </form>

    </div>
</body>

</html>