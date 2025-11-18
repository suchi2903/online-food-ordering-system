<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Home | YummiYo</title>
  <link rel="stylesheet" href="index.css">
</head>
<style>

/* Navbar styling */
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 20px;
  background-color: rgba(62, 39, 35, 0.9); /* semi-transparent coffee brown */
}

/* Logo styling */
.navbar .logo {
  width: 100px;         /* increase or decrease to zoom logo */
  height: auto;         /* maintain aspect ratio */
  border-radius: 5px;  /* rounded corners */
  transition: transform 0.3s; /* smooth animation */
}

/* Optional hover zoom */
.navbar .logo:hover {
  transform: scale(1.2); /* zoom 20% when hovered */
}

.navbar .logo {
  width: 80px;        /* adjust size as needed */
  height: auto;        /* maintain aspect ratio */
  border-radius: 60px;  /* smaller radius for subtle rounding */
  transition: transform 0.3s; /* smooth hover zoom */
}

.navbar .logo:hover {
  transform: scale(1.1); /* smaller zoom for subtle effect */
}

body {
  background-image: url('images/index.jpg'); /* replace with your image path */
  background-size: cover;       /* cover entire page */
  background-position: center;  /* center the image */
  background-repeat: no-repeat;
  background-attachment: fixed; /* optional: keeps image fixed while scrolling */
}
</style>
<body>
<!-- ✅ Navbar -->
<div class="navbar">
  <img src="images/logo6.png" alt="YummiYo Logo" class="logo">
  <div>
    <a href="index.php">Home</a>
    <a href="menu.php">Menu</a>
    <a href="register.php">Register</a>
    <a href="login.php">Login</a>
  </div>
</div>

<!-- ✅ Hero Section -->
<div class="hero">
  <h1>Welcome to YummiYo</h1>
  <p>Delicious food delivered to your door</p>
  <?php if (!isset($_SESSION['user'])): ?>
    <a href="login.php">Login Now</a>
  <?php else: ?>
    <a href="menu.php">login Now</a>
  <?php endif; ?>
</div>

<!-- ✅ Footer -->
<footer>
  <p>&copy; <?= date("Y") ?> YummiYo - Simple Food Ordering</p>
  <p>Contact: info@yummiyo.com | Phone: +91-9876543210</p>
  <p>Follow us on:
    <a href="#">Instagram</a> |
    <a href="#">Facebook</a>
  </p>
</footer>

</body>
</html>
