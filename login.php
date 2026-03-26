<?php
session_start();

// Hardcoded admin credentials
$admin_username = 'admin';
$admin_password = 'admin';

$error = '';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    if($username === $admin_username && $password === $admin_password){
        $_SESSION['admin_logged_in'] = true;
        header("Location: items.php"); // Redirect to items page
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>Username:</label>
            <input type="text" name="username" required><br><br>

            <label>Password:</label>
            <input type="password" name="password" required><br><br>

            <input type="submit" name="login" value="Login">
        </form>
    </div>
</body>
</html>
