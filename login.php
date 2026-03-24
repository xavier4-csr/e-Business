<?php
// Fix #4: session_start() must be at the very top, before any output
session_start();
include "connectdb.php";

// Fix #5: Generate CSRF token on page load
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$unsuccess = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fix #5: Verify CSRF token on every POST
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Fix #1: Prepared statement — no more string interpolation
    $stmt = $connect->prepare("SELECT * FROM bus_owner WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $bus_owner     = $result->fetch_assoc();
        $password_hash = $bus_owner['password'];

        if (password_verify($password, $password_hash)) {
            $_SESSION['email'] = $email;
            $_SESSION['id']    = $bus_owner['id'];
            header("location:profile.php");
            exit();
        } else {
            $unsuccess = 1;
        }
    } else {
        $unsuccess = 1;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        h3 { text-align: left; }
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            text-align: center;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            border: 5px #e0e0e0;
            border-radius: 5px;
        }
        h1 { font-weight: bold; font-size: 24px; color: #000000; }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 7px;
            margin: 17px 0;
            border: 2px solid #d0d0d0;
            border-radius: 2px;
        }
        button {
            background-color: #ff69b4;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
        }
        a { color: #808080; text-decoration: none; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body style="background-image: url('sky.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <h3 style="font-weight: 30px;">RegE</h3>
    <hr>
    <div class="container">
        <h1>SIGN IN</h1>
        <form method="post">
            <!-- Fix #5: CSRF hidden field in every form -->
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="text" name="email" placeholder="EMAIL ADDRESS">
            <input type="password" name="password" placeholder="PASSWORD">
            <?php if ($unsuccess): ?>
                <div class="error">Invalid credentials</div>
            <?php endif; ?>
            <button type="submit">SIGN IN</button>
            <a href="#">Forgot password?</a>
            <p>New to RegE? <a href="bus-owner register.php" style="color: blue;">Register here</a></p>
            <p><a href="Home.php" style="color: blue;">Continue Browsing</a></p>
        </form>
    </div>
</body>
</html>