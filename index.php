<?php
session_start();

$server   = "localhost";
$client   = "root";
$password = "";
$dbname   = "candy_shop";
$conn     = mysqli_connect($server, $client, $password, $dbname);
if (!$conn) {
    die("DB connection error: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candy_id'])) {
    $candyId  = intval($_POST['candy_id']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $existingQty = isset($_SESSION['cart'][$candyId]) && is_numeric($_SESSION['cart'][$candyId])
    ? (int)$_SESSION['cart'][$candyId]
    : 0;

    $_SESSION['cart'][$candyId] = $existingQty + $quantity;

    echo "<form id='post-redirect' method='post' action=''>";
    foreach ($_POST as $key => $val) {
        if ($key === 'candy_id' || $key === 'quantity') continue;
        $val = htmlspecialchars($val, ENT_QUOTES);
        echo "<input type='hidden' name='{$key}' value='{$val}'>";
    }
    echo "<input type='hidden' name='category_id' value='".($_POST['category_id'] ?? 'all')."'>";
    echo "<input type='hidden' name='min_price' value='".($_POST['min_price'] ?? '')."'>";
    echo "<input type='hidden' name='max_price' value='".($_POST['max_price'] ?? '')."'>";
    if (isset($_POST['show_alcohol'])) {
        echo "<input type='hidden' name='show_alcohol' value='on'>";
    }
    echo "<input type='hidden' name='page' value='".($_POST['page'] ?? 1)."'>";
    echo "</form>
    <script>document.getElementById('post-redirect').submit();</script>";
    exit;
}

$cartCount   = 0;
$cartPreview = [];

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $validIds = array_filter(array_keys($_SESSION['cart']), 'is_numeric');

    if (!empty($validIds)) {
        $placeholders = implode(',', array_fill(0, count($validIds), '?'));
        $types        = str_repeat('i', count($validIds));
        $stmt         = $conn->prepare("SELECT id, name FROM candies WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$validIds);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($r = $res->fetch_assoc()) {
            $id = $r['id'];
            $qty = isset($_SESSION['cart'][$id]) && is_numeric($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;
            $cartPreview[] = "{$r['name']} × {$qty}";
            $cartCount += $qty;
        }

        $stmt->close();
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candy Shop</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <aside>
        <header>
            <svg height="200px" width="200px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 511.999 511.999" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path style="fill:#F3437F;" d="M384.828,133.992c39.346,39.346,39.347,103.139,0.001,142.485L276.765,384.541 c-39.347,39.347-103.141,39.347-142.489,0l-6.818-6.818c-39.346-39.346-39.346-103.142,0.001-142.489l108.063-108.063 c39.347-39.347,103.14-39.346,142.485,0L384.828,133.992z"></path> <path style="fill:#FFD41D;" d="M310.355,97.746c-26.969-0.956-54.247,8.838-74.833,29.424l-33.879,33.879v252.909 c27.062,1.037,54.461-8.757,75.122-29.417l33.59-33.59V97.746z"></path> <g> <path style="fill:#0084FF;" d="M353.788,109.278c0,0-12.739-57.504,28.585-98.829c28.19,81.385,85.375,44.28,112.224,126.132 c-35.795,35.795-91.77,21.776-91.77,21.776L353.788,109.278z"></path> <path style="fill:#0084FF;" d="M157.489,401.96c0,0,13.462,58.267-27.862,99.59c-28.19-81.384-85.375-44.279-112.224-126.131 c35.795-35.795,91.77-21.776,91.77-21.776L157.489,401.96z"></path> </g> <path d="M310.355,164.744c-5.77,0-10.449-4.678-10.449-10.449v-0.245c0-5.771,4.679-10.449,10.449-10.449 s10.449,4.678,10.449,10.449v0.245C320.804,160.066,316.126,164.744,310.355,164.744z"></path> <path d="M201.643,366.629c-5.77,0-10.449-4.678-10.449-10.449v-0.245c0-5.771,4.679-10.449,10.449-10.449 c5.771,0,10.449,4.678,10.449,10.449v0.245C212.092,361.951,207.414,366.629,201.643,366.629z"></path> <path d="M504.525,133.324c-14.803-45.128-39.322-58.013-60.954-69.379c-20.737-10.897-38.645-20.308-51.325-56.916 c-1.179-3.401-4.025-5.957-7.533-6.764c-3.505-0.806-7.184,0.249-9.729,2.794c-30.129,30.129-34.208,67.554-33.465,89.666 c-11.07-3.628-22.765-5.514-34.753-5.514c-29.703,0-57.628,11.567-78.632,32.571L120.07,227.845 c-30.505,30.505-39.548,74.468-27.13,112.953c-21.356-1.508-56.602,0.908-82.926,27.231c-2.786,2.787-3.767,6.902-2.54,10.645 c14.803,45.128,39.322,58.013,60.954,69.379c20.737,10.897,38.645,20.308,51.325,56.916c1.179,3.401,4.025,5.957,7.533,6.764 c0.776,0.179,1.561,0.265,2.341,0.265c2.74,0,5.407-1.078,7.388-3.06c30.124-30.125,33.898-67.862,32.935-90.227 c11.312,3.806,23.288,5.788,35.57,5.788c29.703,0,57.628-11.567,78.633-32.57l108.064-108.064 c30.42-30.421,39.494-74.229,27.226-112.637c2.911,0.197,6.072,0.322,9.429,0.322c21.319,0,50.507-4.977,73.113-27.581 C504.771,141.183,505.752,137.067,504.525,133.324z M132.682,481.541c-15.22-31.325-35.947-42.219-54.532-51.985 c-19.509-10.251-36.516-19.191-48.59-51.07c25.999-21.336,61.919-17.39,73.393-15.428c3.736,6.323,8.162,12.345,13.253,17.974 l-15.292,15.292c-4.08,4.08-4.08,10.697,0,14.778c2.041,2.041,4.715,3.06,7.388,3.06c2.674,0,5.348-1.02,7.388-3.06l15.291-15.291 c5.315,4.811,11.015,9.063,17.036,12.714C149.63,420.064,152.017,453.069,132.682,481.541z M377.44,269.089l-56.636,56.636V188.1 c0-5.771-4.679-10.449-10.449-10.449s-10.449,4.678-10.449,10.449v158.524l-30.531,30.531 c-17.056,17.057-39.733,26.449-63.855,26.449s-46.8-9.394-63.856-26.449l-6.818-6.818c-35.21-35.21-35.209-92.502,0.001-127.713 l56.347-56.347v135.854c0,5.771,4.679,10.449,10.449,10.449c5.771,0,10.449-4.678,10.449-10.449V165.378l30.818-30.818 c17.056-17.056,39.732-26.45,63.854-26.45c24.12,0,46.798,9.394,63.854,26.45l6.82,6.82 C412.649,176.59,412.65,233.88,377.44,269.089z M409.545,149.026c-3.774-6.448-8.252-12.595-13.434-18.327l15.323-15.323 c4.08-4.08,4.08-10.697,0-14.778c-4.081-4.08-10.696-4.08-14.778,0l-15.32,15.32c-5.614-5.087-11.665-9.537-18.062-13.323 c-1.517-11.586-3.466-43.883,16.017-72.19c15.222,31.367,35.962,42.266,54.558,52.038c19.509,10.251,36.516,19.191,48.59,51.07 C456.807,154.549,421.533,151.011,409.545,149.026z"></path> </g></svg>            </svg>
            <h1>Sweet Candy Factory</h1>
            <a href="cart.php" class="cart-button">🛒 Cart (<?= $cartCount ?>)</a>

        </header>

        <main>
            <form id="candy-form" method="post" action="">
                <label for="category_id">Candy category filter</label>
                <select name="category_id" id="category_id">
                    <option value="all">All</option>
                    <?php
                    $cats = $conn->query("SELECT id,name FROM category");
                    while ($cat = $cats->fetch_assoc()) {
                        $sel = (isset($_POST['category_id']) && $_POST['category_id']==$cat['id']) ? ' selected':'';
                        echo "<option value='{$cat['id']}'{$sel}>{$cat['name']}</option>";
                    }
                    ?>
                </select>

                <div class="checkbox-wrapper">
                    <input type="checkbox" name="show_alcohol" id="alcoholCheckbox" <?= isset($_POST["show_alcohol"]) ? 'checked' : '' ?>> 
                    <label for="alcoholCheckbox">Alcohol Content</label>
                </div>

                <label for="price-wrapper">Price range</label>
                <div class="price-wrapper">
                    <input type="number" placeholder="Min" name="min_price" id="min_price" step="0.01" min="0" value="<?= $_POST['min_price'] ?? '' ?>">
                        
                    <input type="number" placeholder="Max" name="max_price" id="max_price" step="0.01" min="0" value="<?= $_POST['max_price'] ?? '' ?>">
                </div>
                    
                <button type="submit">Filter</button>
            </form>

        </main>

        <footer>
            <p>Check out our social media</p>
            <div class="socialmedia-icons">
                <div id="facebook-wrapper">
                    <a href="#"><img src="socialmedia/facebook-svgrepo-com.png" alt="Facebook_icon" name="facebook"></a>
                    <label for="facebook">Facebook</label>
                </div>

                <div id="instagram-wrapper">
                    <a href="#"><img src="socialmedia/instagram-svgrepo-com.png" alt="Instagram_icon" name="instagram"></a>
                    <label for="instagram">Instagram</label>
                </div>
                <div id="twitter-wrapper">
                    <a href="#"><img src="socialmedia/twitter-svgrepo-com.png" alt="Twitter_icon" name="twitter"></a>
                    <label for="twitter">Twitter</label>
                </div>

            </div>
        </footer>
    </aside>

    <main>
    <div class="current-category">
          <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || !isset($_POST['category_id'])) {
                $min = isset($_POST['min_price']) && is_numeric($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
                $max = isset($_POST['max_price']) && is_numeric($_POST['max_price']) ? floatval($_POST['max_price']) : 1000000;
                $where = "WHERE price BETWEEN {$min} AND {$max}";
            
                if (isset($_POST['category_id']) && $_POST['category_id']!=='all') {
                    $catId = intval($_POST['category_id']);
                    $where .= " AND category_id={$catId}";
                }
            
              if (!isset($_POST['show_alcohol'])) {
                    $where .= " AND contains_alcohol=0";
                }
            
                if (!empty($catId)) {
                    $cname = $conn->query("SELECT name FROM category WHERE id={$catId}")
                                  ->fetch_assoc()['name'];
                    echo "<div class='category-title'>Selected category: {$cname}</div>";
                } else {
                    echo "<div class='category-title'>Showing all candies</div>";
                }
            
                $page   = max(1, intval($_POST['page'] ?? 1));
                $limit  = 9;
                $offset = ($page-1)*$limit;
                $total  = $conn->query("SELECT COUNT(*) t FROM candies {$where}")
                               ->fetch_assoc()['t'];
                $pages  = ceil($total/$limit);
            
                $sql = "SELECT id,name,description,stock,price,img
                        FROM candies {$where}
                        LIMIT {$limit} OFFSET {$offset}";
                $res = $conn->query($sql);
                if ($res->num_rows) {
                    echo "<div class='candy-list'>";
                    while ($r = $res->fetch_assoc()) {
                        echo "<div class='candy-container'>"
                             ."<div class='candy-main'>";
                        if ($r['img']) {
                            $b64 = base64_encode($r['img']);
                            echo "<img src='data:image/jpeg;base64,{$b64}'>";
                        } else {
                            echo "<img src='default_image.jpg'>";
                        }
                        echo "<h3>{$r['name']}</h3></div>"
                           ."<div class='candy-info'><p>{$r['description']}</p>"
                           ."<div class='candy-footer'>"
                           ."<span class='candy-price'>$".number_format($r['price'],2)."</span>";
                    
                      if ($r['stock']==0) {
                            echo "<button class='out-of-stock' disabled>Out of stock</button>";
                        } else {
                            echo "<form method='post' action='' class='add-to-cart-form'>
                                  <input type='hidden' name='candy_id' value='{$r['id']}'>
                                  <input type='hidden' name='quantity' value='1'>";
                              foreach (['category_id','min_price','max_price','page'] as $key) {
                                  if (isset($_POST[$key])) {
                                      $val = htmlspecialchars($_POST[$key], ENT_QUOTES);
                                      echo "<input type='hidden' name='{$key}' value='{$val}'>";
                                  }
                              }
                              if (isset($_POST['show_alcohol'])) {
                                  echo "<input type='hidden' name='show_alcohol' value='on'>";
                              }
                              echo "<button type='submit'>Add to cart</button>
                              </form>";

                        }
                    
                      echo "</div></div></div>";
                    }
                    echo "</div>";
                
                    echo "<div class='pagination'>"
                       ."<button onclick='changePage(".max(1,$page-1).")'>&lt;</button>"
                       ."<span>Page {$page}/{$pages}</span>"
                       ."<button onclick='changePage(".min($pages,$page+1).")'>&gt;</button>"
                       ."</div>";
                } else {
                    echo "<p>No candies match the criteria.</p>";
                }
            }
                ?>
    </div>
    </main>

    <script>
    function changePage(page) {
      const form = document.getElementById('candy-form');
      const pageInput = document.createElement('input');
      pageInput.type = 'hidden';
      pageInput.name = 'page';
      pageInput.value = page;
      form.appendChild(pageInput);
      form.submit();
    }
    </script>

</body>
</html>
