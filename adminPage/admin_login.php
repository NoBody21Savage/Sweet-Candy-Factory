<?php
session_start();

$admin_username = 'admin';
$admin_password = 'admin123'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>


<body>
    <header>
        <h1>Orders Control Page</h1>
    </header>

    <main>
        
        <div class="form-wrapper">
            <h2>Admin Login</h2>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="post">
                <label>Username: <input type="text" name="username" required></label>
                <label>Password: <input type="password" name="password" required></label>
                <button type="submit">Login</button>
            </form>
        </div>

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
