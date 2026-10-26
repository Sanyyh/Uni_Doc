<?php
session_start();
include 'includes/db.php';
$departments = [];
$result = $conn->query("SELECT id, name FROM courses ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $departments[$row['id']] = $row['name'];
}
$errors = [];
$success = "";

// Handle Signup
if (isset($_POST['signup'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $department = $_POST['department'] ?? '';

if (!$department || !is_numeric($department)) {
    $errors[] = "Please select a department.";
}


    // Validation
    if (!$firstName || !$lastName || !$email || !$password) {
        $errors[] = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        }
        $stmt->close();
    }

    // If no errors, insert user
    if (empty($errors)) {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, password, depmt) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $firstName, $lastName, $email, $hashedPass, $department);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now log in.";
        } else {
            $errors[] = "Failed to register user. Please try again.";
        }
        $stmt->close();
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $input = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$input || !$password) {
        $errors[] = "Please enter username/email and password.";
    } else {
        // Check for user by email or first_name
        $stmt = $conn->prepare("SELECT id, first_name, password FROM user WHERE email = ? OR first_name = ?");
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "User not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>UniDoc - Login & Signup</title>
<style>
  body { font-family: Arial, sans-serif; background: #f0f4f8; margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh; }
  .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 360px; }
  h2 { margin-bottom: 20px; color: #333; text-align:center; }
  input[type=text], input[type=email], input[type=password] {
    width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
  }
  button {
    width: 100%; background-color: #007bff; color: white; padding: 12px; border:none; border-radius: 4px; cursor:pointer; font-size: 16px;
  }
  button:hover { background-color: #0056b3; }
  .tabs {
    display: flex; justify-content: space-around; margin-bottom: 20px; cursor: pointer;
  }
  .tab {
    flex: 1; text-align:center; padding: 10px 0; font-weight: bold; border-bottom: 3px solid transparent; color: #666;
  }
  .tab.active { border-color: #007bff; color: #007bff; }
  .tab-content { display: none; }
  .tab-content.active { display: block; }
  .message { margin-bottom: 15px; font-size: 14px; }
  .error { color: #d93025; }
  .success { color: #188038; }
</style>
<script>
  function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    document.querySelector('.tab[data-tab="'+tabName+'"]').classList.add('active');
  }
  window.onload = function() {
    switchTab('login'); // default tab
  };
</script>
</head>
<body>

<div class="container">
  <div class="tabs">
    <div class="tab active" data-tab="login" onclick="switchTab('login')">Login</div>
    <div class="tab" data-tab="signup" onclick="switchTab('signup')">Sign Up</div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="message error">
      <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Login Form -->
  <div id="login" class="tab-content active">
    <form method="post" novalidate>
      <input type="text" name="username" placeholder="Username or Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit" name="login">Login</button>
    </form>
  </div>

  <!-- Signup Form -->
  <div id="signup" class="tab-content">
    <form method="post" novalidate>
      <input type="text" name="first_name" placeholder="First Name" required />
      <input type="text" name="last_name" placeholder="Last Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <select name="department" required>
  <option value="">-- Select Department --</option>
  <?php foreach ($departments as $id => $name): ?>
    <option value="<?= $id ?>" <?= isset($_POST['department']) && $_POST['department'] == $id ? 'selected' : '' ?>>
      <?= htmlspecialchars($name) ?>
    </option>
  <?php endforeach; ?>
</select>
      <input type="password" name="password" placeholder="Password (min 6 chars)" required />
      <button type="submit" name="signup">Sign Up</button>
    </form>
  </div>
</div>

</body>
</html>
