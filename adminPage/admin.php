<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "candy_shop");

if (isset($_POST['update_status'])) {
    $id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['delete_order'])) {
    $id = (int)$_POST['order_id'];
    $check = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->bind_result($status);
    $check->fetch();
    $check->close();

    if ($status === 'Completed') {
        $conn->query("DELETE FROM order_items WHERE order_id = $id");
        $conn->query("DELETE FROM orders WHERE id = $id");
    }
}

$res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="panel.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h2>Admin Panel</h2>
    </header>
    <main>

        <p>Welcome, Admin | <a href="logout.php">Logout</a></p>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Address</th><th>Contact</th>
                    <th>Total</th><th>Status</th><th>Change</th><th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['delivery_address']) ?></td>
                        <td><?= htmlspecialchars($order['contact_info']) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><?= $order['status'] ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status">
                                    <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Progress" <?= $order['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($order['status'] === 'Completed'): ?>
                                <form method="post" style="display: flex; justify-content: center; align-items: center;" onsubmit="return confirm('Are you sure you want to delete this completed order?');">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="delete_order">Delete</button>
                                </form>
                            <?php else: ?>
                                <em>Only if completed</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <img src="image/tyler_durden.png" alt="Tyler">
        <div>
            <p>Where's My Mind</p>
            <p>First Rule of coding is: you do not talk about your code</p>    
            <p>Second Rule of coding is: you DO NOT talk about your code</p>
        </div>
        <img src="image/The_Narrator.png" alt="Narrator">
    </footer>
</body>
</html>
