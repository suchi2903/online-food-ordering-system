<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "yummiyo_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM menu WHERE id = $del_id");
    header("Location: admin_menu.php");
    exit;
}

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $image = $conn->real_escape_string($_POST['image']);
    $category = $conn->real_escape_string($_POST['category']);

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE menu SET name='$name', description='$description', price=$price, image='$image', category='$category' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO menu (name, description, price, image, category) VALUES ('$name', '$description', $price, '$image', '$category')");
    }
    header("Location: admin_menu.php");
    exit;
}

$result = $conn->query("SELECT * FROM menu ORDER BY id DESC");

$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM menu WHERE id = $edit_id LIMIT 1");
    $edit_item = $res->fetch_assoc();
}
?>

<?php include 'sidebar.php'; ?>

<h1>Admin Menu Management</h1>

<form method="POST" action="admin_menu.php" style="background: #fff8f0; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width: 600px; margin-bottom: 30px;">
    <input type="hidden" name="id" value="<?= $edit_item ? $edit_item['id'] : '' ?>">

    <label for="name">Name</label>
    <input type="text" id="name" name="name" required value="<?= $edit_item ? htmlspecialchars($edit_item['name']) : '' ?>" style="width: 100%; padding: 8px; margin-bottom: 15px;">

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3" required style="width: 100%; padding: 8px; margin-bottom: 15px;"><?= $edit_item ? htmlspecialchars($edit_item['description']) : '' ?></textarea>

    <label for="price">Price (₹)</label>
    <input type="number" step="0.01" min="0" id="price" name="price" required value="<?= $edit_item ? $edit_item['price'] : '' ?>" style="width: 100%; padding: 8px; margin-bottom: 15px;">

    <label for="image">Image Path or URL</label>
    <input type="text" id="image" name="image" value="<?= $edit_item ? htmlspecialchars($edit_item['image']) : '' ?>" style="width: 100%; padding: 8px; margin-bottom: 15px;">

    <label for="category">Category</label>
    <select id="category" name="category" required style="width: 100%; padding: 8px; margin-bottom: 15px;">
        <option value="">-- Select Category --</option>
        <option value="Pizza" <?= $edit_item && $edit_item['category'] == 'Pizza' ? 'selected' : '' ?>>🍕 Pizza</option>
        <option value="Snacks" <?= $edit_item && $edit_item['category'] == 'Snacks' ? 'selected' : '' ?>>🍔 Snacks</option>
        <option value="Drinks" <?= $edit_item && $edit_item['category'] == 'Drinks' ? 'selected' : '' ?>>🥤 Drinks</option>
        <option value="Desserts" <?= $edit_item && $edit_item['category'] == 'Desserts' ? 'selected' : '' ?>>🍰 Desserts</option>
        <option value="Meals" <?= $edit_item && $edit_item['category'] == 'Meals' ? 'selected' : '' ?>>🍛 Meals</option>
    </select>

    <button type="submit" style="background: #6d4c41; color: white; padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer;">
        <?= $edit_item ? 'Update' : 'Add' ?> Menu Item
    </button>
    <?php if ($edit_item): ?>
        <a href="admin_menu.php" style="margin-left: 10px; font-weight: bold; color: #6d4c41;">Cancel</a>
    <?php endif; ?>
</form>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #d2b48c;">
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price (₹)</th>
            <th>Image</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr style="border: 1px solid #ccc;">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>₹<?= number_format($row['price'], 2) ?></td>
            <td>
                <?php if ($row['image']): ?>
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="" style="width:60px; height:40px; object-fit:cover; border-radius:5px;">
                <?php else: ?>
                N/A
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td>
                <a href="admin_menu.php?edit=<?= $row['id'] ?>" style="color: #6d4c41; font-weight: bold; margin-right: 10px;">Edit</a>
                <a href="admin_menu.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this menu item?');" style="color:red; font-weight: bold;">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div> <!-- close content -->

<?php
$conn->close();
?>
