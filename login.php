<?php
session_start();
$successMessage = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=yummiyo_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Admin: verify hashed password
            if ($user['role'] === 'admin' && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: admin_dashboard.php");
                exit;
            }
            // User: compare plain-text password
            elseif ($user['role'] === 'user' && $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: user_dashboard.php");
                exit;
            } else {
                $errorMessage = "❌ Invalid email or password.";
            }
        } else {
            $errorMessage = "❌ Invalid email or password.";
        }

    } catch (PDOException $e) {
        $errorMessage = "❌ Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login | YummiYo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: Arial, sans-serif; }

    /* Container for left form and right GIF */
    .page-container {
      display: flex;
      height: 100vh;
      width: 100%;
    }

    /* Left side: login form */
    .login-container {
      width: 40%;
      background-color: rgba(62, 39, 35, 0.95); /* coffee-brown with slight transparency */
      color: #fff;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center; /* vertical center */
    }

    .login-container h2 {
      margin-bottom: 20px;
      text-align: center;
      color: #ffebcd; /* cream text */
    }

    .login-container input {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border-radius: 6px;
      border: none;
      font-size: 14px;
      background: #efebe9;
      color: #3e2723;
    }

    .login-container input:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(255, 236, 179, 0.4);
    }

    .login-container button {
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

    .login-container button:hover {
      background-color: #6d4c41;
    }

    .login-container p {
      text-align: center;
      margin-top: 12px;
      font-size: 14px;
      color: #ffcc80;
    }

    .login-container a {
      color: #ffcc80;
      text-decoration: none;
    }

    .login-container a:hover {
      text-decoration: underline;
    }

    /* Right side: GIF */
    .gif-container {
      flex: 1;
      position: relative;
    }

    .gif-container img {
      width: 100%;
      height: 100%;
      object-fit: cover; /* fill entire area */
      display: block;
    }

    /* Messages */
    .login-container div[style] {
      margin-bottom: 10px;
      font-size: 14px;
    }

    /* Responsive for mobile */
    @media (max-width: 768px) {
      .page-container { flex-direction: column; }
      .login-container { width: 100%; height: 50vh; }
      .gif-container { height: 50vh; }
    }
  </style>
</head>
<body>
  <div class="page-container">
    <!-- Left: Login Form -->
    <div class="login-container">
      <h2>Login</h2>
      <?php if (!empty($successMessage)): ?>
        <div style="color: lightgreen;"><?= $successMessage ?></div>
      <?php endif; ?>
      <?php if (!empty($errorMessage)): ?>
        <div style="color: #ffccbc;"><?= $errorMessage ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
      </form>
      <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <!-- Right: GIF -->
    <div class="gif-container">
      <img src="images/login9.jpg" alt="YummiYo GIF">
    </div>
  </div>
</body>
</html>
