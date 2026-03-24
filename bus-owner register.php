<?php
session_start();
include "connectdb.php";

// Fix #5: Generate CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$success   = 0;
$unsuccess = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fix #5: Verify CSRF token
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    $first_name      = trim($_POST['first_name'] ?? '');
    $last_name       = trim($_POST['last_name'] ?? '');
    $email           = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $phone_no        = trim($_POST['phone_no'] ?? '');

    if (!$email || !$first_name || !$last_name || !$password || !$confirmPassword || $password !== $confirmPassword) {
        $unsuccess = 1;
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Fix #1: Prepared statement to check for existing email
    $stmt = $connect->prepare("SELECT owner_id FROM bus_owner WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $unsuccess = 1; // Email already exists
    } else {
        $stmt->close();
        // Fix #1: Prepared statement for INSERT
        $stmt = $connect->prepare(
            "INSERT INTO bus_owner (first_name, last_name, email, password, phone_no)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $phone_no);

        if ($stmt->execute()) {
            $success = 1;
            header("location:login.php");
            exit();
        } else {
            die("Registration failed: " . $connect->error);
        }
    }
    $stmt->close();
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign up</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .container {
        border-radius: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 30px;
        width: 600px;
        border: 5px #e0e0e0;
        margin: auto;
        justify-content: center;
    }
    h1 { font-size: 24px; margin-bottom: 20px; }
    input {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    label { font-weight: bold; }
    .checkbox-container { display: flex; align-items: center; }
    .checkbox-label { margin-left: 10px; }
    .create-account-btn {
        background-color: #007bff;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        cursor: pointer;
        font-weight: bold;
    }
    .error { color: red; text-align: center; font-size: 20px; padding: 20px; }
</style>
<body style="background-image: url('sky.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="container">
        <h3 style="text-align: left;">RegE</h3>
        <hr>
        <h1>REGISTER AND SIGNUP:</h1>
        <div id="error-message" style="color: red; margin-bottom: 10px;"></div>

        <form action="" method="post">
            <!-- Fix #5: CSRF hidden field -->
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone_no">Phone No:</label>
            <input type="number" id="phone_no" name="phone_no" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>

            <?php if ($unsuccess): ?>
                <div class="error">Email already exists!</div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="error">Signup successful</div>
            <?php endif; ?>

            <div class="checkbox-container">
                <input type="checkbox" id="updates" name="updates">
                <label class="checkbox-label" for="updates">Receive updates, ads, and offers</label>
            </div>

            <p>By creating an account, you agree to the <a href="#">Terms of Use</a> and have read our <a href="#">Privacy Policy</a>.</p>
            <button class="create-account-btn" type="submit">REGISTER</button>
        </form>
    </div>
</body>
</html>