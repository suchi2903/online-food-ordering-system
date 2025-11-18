<?php
session_start();
require 'db.php';

// Get user_id if logged in, otherwise null
$user_id = $_SESSION['user_id'] ?? null;

// Fetch logged-in user info
if ($user_id) {
    $stmt = $pdo->prepare("SELECT username AS name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $user = null; // guest user
}

// Handle add to cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$user_id) {
        // Redirect guest users to login
        header("Location: login.php");
        exit;
    }

    $item_id = (int)$_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = (float)$_POST['item_price'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $item_id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $item_id,
            'name' => $item_name,
            'price' => $item_price,
            'quantity' => 1
        ];
    }

    // Preserve category & search params after adding to cart
    $category_param = urlencode($_GET['category'] ?? '');
    $search_param = urlencode($_GET['search'] ?? '');
    header("Location: menu.php?category={$category_param}&search={$search_param}");
    exit;
}

// Fetch menu items with category + search
$category = $_GET['category'] ?? null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$query = "SELECT * FROM menu WHERE 1=1";
$params = [];

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}
if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total items in cart for badge
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu | YummiYo</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; }
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  display: flex;
  background: #fef6e4;
  color: #4b2e2e;
}

/* Sidebar */
.sidebar {
  width: 260px;
  background: #fff8f0;
  padding: 20px;
  height: 100vh;
  border-right: 2px solid #e0c097;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: fixed;
  overflow-y: auto;
}

.sidebar .profile {
  text-align: center;
  margin-bottom: 20px;
}
.sidebar .profile h2 { font-size: 20px; color: #5d4037; }
.sidebar .profile p { font-size: 14px; color: #7b5e4c; margin: 5px 0; }

.sidebar nav a {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 15px;
  margin: 6px 0;
  border-radius: 8px;
  text-decoration: none;
  color: #4b2e2e;
  font-weight: 600;
  transition: 0.3s;
  background: #f4e1d2;
  position: relative;
}
.sidebar nav a:hover { background: #6d4c41; color: white; }

/* Cart badge */
.cart-badge {
  background: red;
  color: white;
  font-size: 12px;
  font-weight: bold;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 5px;
  right: 10px;
}

/* Categories */
.sidebar .categories {
  margin-top: 20px;
}
.sidebar .categories h3 {
  font-size: 16px;
  margin-bottom: 10px;
  color: #5d4037;
}
.sidebar .categories a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 15px;
  margin: 6px 0;
  border-radius: 8px;
  background: #f4e1d2;
  text-decoration: none;
  color: #4b2e2e;
  font-weight: 600;
  transition: 0.3s;
}
.sidebar .categories a:hover,
.sidebar .categories a.active {
  background: #6d4c41;
  color: white;
}

/* Main content */
.main {
  margin-left: 260px;
  padding: 30px;
  flex-grow: 1;
}
h1 {
  text-align: center;
  color: #3e2723;
  margin-bottom: 20px;
  font-size: 2rem;
}

/* Search bar */
.search-bar {
  text-align: center;
  margin-bottom: 25px;
}
.search-bar input[type="text"] {
  padding: 10px;
  width: 60%;
  max-width: 400px;
  border: 2px solid #d7ccc8;
  border-radius: 20px;
  font-size: 14px;
}
.search-bar button {
  padding: 10px 18px;
  margin-left: 10px;
  background: #ff7043;
  border: none;
  border-radius: 20px;
  color: white;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}
.search-bar button:hover { background: #e64a19; }

/* Menu grid */
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 20px;
}
.menu-item {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 10px;
  text-align: center;
  background: #fff;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.menu-item img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 6px;
  margin-bottom: 8px;
}
.menu-item h3 { font-size: 16px; margin: 6px 0; }
.menu-item p { font-size: 12px; color: #555; margin: 4px 0; }
.menu-item .menu-price {
  font-size: 14px;
  font-weight: bold;
  color: #d35400;
  margin: 4px 0;
}

/* Button */
.menu-item button {
  background: #ff7043;
  color: white;
  border: none;
  padding: 8px 12px;
  font-size: 14px;
  border-radius: 20px;
  cursor: pointer;
  transition: background 0.3s;
}
.menu-item button:hover { background: #e64a19; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div>
    <div class="profile">
      <?php if ($user): ?>
        <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
      <?php else: ?>
        <h2>Welcome, Guest</h2>
      <?php endif; ?>
    </div>

    <nav>
      <a href="user_dashboard.php">Home</a>  
      <a href="menu.php">📦 Menu</a>  
      <a href="order_history.php">📦 Recent Orders</a>
      <a href="cart.php">🛒 Cart
        <?php if ($cart_count > 0): ?>
          <span class="cart-badge"><?= $cart_count ?></span>
        <?php endif; ?>
      </a>
      <a href="logout.php">🚪 Logout</a>
    </nav>

    <div class="categories">
      <h3>🍴 Categories</h3>
      <a href="menu.php?category=Pizza" class="<?= $category==='Pizza' ? 'active' : '' ?>">🍕 Pizza</a>
      <a href="menu.php?category=Fries" class="<?= $category==='Fries' ? 'active' : '' ?>">🍟 Fries</a>
      <a href="menu.php?category=Snacks" class="<?= $category==='Snacks' ? 'active' : '' ?>">🍔 Burger</a>
      <a href="menu.php?category=Drinks" class="<?= $category==='Drinks' ? 'active' : '' ?>">🥤 Drinks</a>
      <a href="menu.php?category=Desserts" class="<?= $category==='Desserts' ? 'active' : '' ?>">🍰 Desserts</a>
      <a href="menu.php?category=Meals" class="<?= $category==='Meals' ? 'active' : '' ?>">🍛 Meals</a>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main">
  <h1>🍴 YummiYo Menu</h1>

  <!-- Search Bar -->
  <div class="search-bar">
    <form method="get" action="menu.php">
      <?php if ($category): ?>
        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
      <?php endif; ?>
      <input type="text" name="search" placeholder="Search for dishes..." value="<?= htmlspecialchars($search ?? '') ?>">
      <button type="submit">🔍 Search</button>
    </form>
  </div>

  <div class="menu-grid">
    <?php if (count($menu_items) > 0): ?>
      <?php foreach ($menu_items as $item): ?>
        <div class="menu-item">
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          <h3><?= htmlspecialchars($item['name']) ?></h3>
          <p><?= htmlspecialchars($item['description']) ?></p>
          <div class="menu-price">₹<?= number_format($item['price'], 2) ?></div>
          <form method="POST" action="menu.php?category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>">
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
            <input type="hidden" name="item_name" value="<?= htmlspecialchars($item['name']) ?>">
            <input type="hidden" name="item_price" value="<?= $item['price'] ?>">
            <button type="submit" name="add_to_cart">Add to Cart</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php elseif ($category || $search): ?>
      <p style="text-align:center;">No items found. Try another search.</p>
    <?php else: ?>
      <p style="text-align:center;">⬅️ Select a category from the sidebar or search above.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
