<?php
session_start();

$server   = "localhost";
$client   = "root";
$password = "";
$dbname   = "candy_shop";
$conn     = mysqli_connect($server, $client, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["place_order"])) {
        $customer_name = trim($_POST["customer_name"]);
        $delivery_address = trim($_POST["delivery_address"]);
        $contact_info = trim($_POST["contact_info"]);

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $stmt = $conn->prepare("INSERT INTO orders (customer_name, delivery_address, contact_info, total_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $customer_name, $delivery_address, $contact_info, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, candy_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt->execute();
        }
        $stmt->close();

        unset($_SESSION['cart']);
        header("Location: cart.php?done=1");
        exit;
    }

    if (isset($_POST["clear"])) {
        unset($_SESSION['cart']);
        header("Location: cart.php");
        exit;
    }

    if (isset($_POST["remove"])) {
        $id = $_POST["remove"];
        unset($_SESSION['cart'][$id]);
        header("Location: cart.php");
        exit;
    }
}

$items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT id, name, price FROM candies WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $itemData = $_SESSION['cart'][$r['id']];
        $qty = is_array($itemData) ? $itemData['quantity'] : $itemData;
        $sub = $r['price'] * $qty;
        $items[] = [
            'id' => $r['id'],
            'name' => $r['name'],
            'price' => $r['price'],
            'qty' => $qty,
            'sub' => $sub
        ];
        $total += $sub;
        $_SESSION['cart'][$r['id']] = [
            'price' => $r['price'],
            'quantity' => $qty,
            'name' => $r['name']
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Your Cart</title>
  <link rel="stylesheet" href="cart.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <section>
        <h1>Your Cart 🛒</h1>
        <a href="index.php" class="cart-button">← Continue Shopping</a>
    </section>
</header>

<main>
  <?php if (isset($_GET['done'])): ?>
    <h2 style="color: black;">Thank you! Your order has been placed.</h2>
    <div id="emptywrap">
        <p style="text-decoration: none;"><a href="index.php" style="text-decoration: none; color: black; font-size: 30px; font-family: 'Fredoka', sans-serif; font-weight: 500;">Back to shop</a></p>
        <img src="socialmedia/cursor-click-svgrepo-com.png" alt="cursor">
    </div>

    <footer>
        <p>Thank you for buying candies in our Candy Factory!</p>
    </footer>
  <?php else: ?>
    <?php if (empty($items)): ?>
      <h2>Your cart is empty.</h2>
      <div id="emptywrap">
          <p><a href="index.php">Go pick some sweets!</a></p>
          <img src="socialmedia/cursor-click-svgrepo-com.png" alt="cursor">
      </div>
      <footer>
        <p>Thank you for buying candies in our Candy Factory!</p>
      </footer>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Candy</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= htmlspecialchars($it['name']) ?></td>
              <td>$<?= number_format($it['price'], 2) ?></td>
              <td><?= $it['qty'] ?></td>
              <td>$<?= number_format($it['sub'], 2) ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <button name="remove" id="remove-button" value="<?= $it['id'] ?>">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" align="right">Total:</th>
            <th>$<?= number_format($total, 2) ?></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
      <div class="buttonSS" style="width: 80%; display: flex; justify-content: space-between;">
          <div style="margin-top: 20px; display: flex; justify-content:center; align-items: center; width: 90%;">
            <form method="post" style="display: flex; flex-direction: column; align-items: center; height: 300px; justify-content: space-around; margin-left: 310px;">
              <label for="customer_name">Your name:</label>
              <input type="text" id="customer_name" name="customer_name" required style="padding: 8px; width: 250px; margin-top: 5px;">
    
              <label for="delivery_address">Delivery address:</label>
              <input type="text" id="delivery_address" name="delivery_address" required style="padding: 8px; width: 250px; margin-top: 5px;">
    
              <label for="contact_info">Contact info (phone or email):</label>
              <input type="text" id="contact_info" name="contact_info" required style="padding: 8px; width: 250px; margin-top: 5px;">
    
              <button type="submit" name="place_order" id="checkout-button">Place Order</button>
            </form>
    
        </div>
        <div style="display: flex; flex-direction: row-reverse;">
            <form method="post" style="display: flex; justify-content: center; aling-items: end; padding: 2px; width: 329px;">
              <button type="submit" name="clear" id="clear-button">Empty Cart</button>
            </form>
        </div>
      </div>

      <footer>
        <p>Thank you for buying candies in our Candy Factory!</p>
      </footer>
    <?php endif; ?>
  <?php endif; ?>
</main>
</body>
</html>
