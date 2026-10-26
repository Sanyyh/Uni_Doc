<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us - FileSharing</title>
  <style>
    body {
      background: linear-gradient(135deg, #007bff 0%, #66b2ff 100%);
      color: white;
      font-family: "Inter", sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background-color: rgba(0, 86, 179, 0.85);
      padding: 1rem 2rem;
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    main {
      flex: 1;
      max-width: 900px;
      margin: 3rem auto;
      background-color: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 2rem 3rem;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      backdrop-filter: blur(8px);
    }
    h1 {
      margin-bottom: 1rem;
      font-weight: 700;
      font-size: 2.5rem;
      color: #e0e7ff;
      text-align: center;
    }
    p {
      line-height: 1.6;
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
      color: #dbeafe;
    }
    a.button {
      display: inline-block;
      background-color: #004aad;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    a.button:hover {
      background-color: #002f6c;
    }
    footer {
      background-color: rgba(0, 86, 179, 0.85);
      color: white;
      text-align: center;
      padding: 1rem;
      font-size: 0.9rem;
      margin-top: auto;
      box-shadow: 0 -2px 8px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>
  <header>
    FileSharing
  </header>

  <main>
    <h1>About UniDoc</h1>
    <p>Welcome to FileSharing, your reliable and easy-to-use platform for securely sharing files with friends, colleagues, and collaborators. Whether you need to upload documents, photos, or other media, our system is designed to be fast, secure, and accessible from any device.</p>
    <p>Our mission is to simplify the way you share and manage your digital files by providing a clean interface, secure access controls, and quick upload/download functionality. We continuously improve our service to meet your evolving needs.</p>
    <p>If you have any questions, feedback, or need support, feel free to contact us anytime.</p>
    
    <a href="home.php" class="button">Go to Home</a>
  </main>

  <footer>
    &copy; <?php echo date("Y"); ?> FileSharing. All rights reserved.
  </footer>
</body>
</html>
