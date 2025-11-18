<?php
session_start();
$successMessage = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=yummiyo_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $username = $_POST['username'];
        $email = $_POST['email'];
        $rawPassword = $_POST['password'];

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errorMessage = "❌ Email already registered. Please login or use a different email.";
        } else {
            // Only hash password for admin
            if ($email === 'admin@example.com') {
                $role = 'admin';
                $password = password_hash($rawPassword, PASSWORD_DEFAULT);
            } else {
                $role = 'user';
                $password = $rawPassword; // plain text for normal users
            }

            // Insert user into database
            $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $password, $role]);

            $successMessage = "✅ Registration successful! <a href='login.php'>Login here</a>";
        }

    } catch (PDOException $e) {
        $errorMessage = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register | YummiYo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: Arial, sans-serif; }

    /* Container for left form and right image */
    .page-container {
      display: flex;
      height: 100vh;
      width: 100%;
    }

    /* Left side: register form */
    .register-container {
      width: 40%;
      background-color: rgba(62, 39, 35, 0.95); /* coffee-brown with slight transparency */
      color: #fff;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center; /* vertical center */
    }

    .register-container h2 {
      margin-bottom: 20px;
      text-align: center;
      color: #ffebcd; /* cream text */
    }

    .register-container input {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border-radius: 6px;
      border: none;
      font-size: 14px;
      background: #efebe9;
      color: #3e2723;
    }

    .register-container input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(255, 236, 179, 0.4);
    }

    .register-container button {
      width: 100%;
      padding: 12px;
      margin-top: 12px;
      border-radius: 6px;
      border: none;
      background-color: #795548; /* coffee button */
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }

    .register-container button:hover {
      background-color: #6d4c41;
    }

    .register-container p {
      text-align: center;
      margin-top: 12px;
      font-size: 14px;
      color: #ffcc80;
    }

    .register-container a {
      color: #ffcc80;
      text-decoration: none;
    }

    .register-container a:hover {
      text-decoration: underline;
    }

    /* Right side: image */
    .image-container {
      flex: 1;
      position: relative;
    }

    .image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    /* Messages */
    .register-container div[style] {
      margin-bottom: 10px;
      font-size: 14px;
    }

    /* Responsive for mobile */
    @media (max-width: 768px) {
      .page-container { flex-direction: column; }
      .register-container { width: 100%; height: 60vh; }
      .image-container { height: 40vh; }
    }
  </style>
</head>
<body>
  <div class="page-container">
    <!-- Left: Register Form -->
    <div class="register-container">
      <h2>Create Account</h2>
      <?php if (!empty($successMessage)): ?>
        <div style="color: lightgreen;"><?= $successMessage ?></div>
      <?php endif; ?>
      <?php if (!empty($errorMessage)): ?>
        <div style="color: #ffccbc;"><?= $errorMessage ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
      </form>
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <!-- Right: Background image -->
    <div class="image-container">
      <img src="images/final.jpg" alt="YummiYo Register">
    </div>
  </div>
</body>
</html>
