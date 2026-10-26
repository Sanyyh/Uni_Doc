<?php
session_start();
include 'includes/db.php';


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Welcome to UniDoc</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style></style>
      <link rel="stylesheet" href="style2.css"/>
</head>
<body>
  
    <div class="card-glass">
</div>

<!-- Top Nav -->
<div class="top-bar">
  <div class="project-name">UniDoc</div>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="about.php">About</a>
    <a href="login.php">Login</a>
  </div>
</div>

<!-- Hero Section -->
<section class="hero">
  <h1>Welcome to UniDoc</h1>
  <p>A simple and secure platform for uploading, managing, and sharing your files — built for students of Cumputing Feculty.</p>
  <a href="login.php" class="explore-btn">Explore Now</a>
</section>

<!-- Feature Cards -->
<section class="features">
  <div class="feature-card">
    <h3>Upload & Share</h3>
    <p>Quickly upload files and share via secure links with anyone.</p>
  </div>
  <div class="feature-card">
    <h3>Cloud Access</h3>
    <p>Access your documents from any device, anywhere, anytime.</p>
  </div>
  <div class="feature-card">
    <h3>Easy to Use</h3>
    <p>Modern, intuitive design with no learning curve needed.</p>
  </div>
</section>

<footer>
  &copy; <?php echo date("Y"); ?> Homex. All rights reserved.
</footer>

</body>
</html>
