<?php
session_start();
include 'connectdb.php';

if (!isset($_SESSION['id'])) {
    header('location:login.php');
    exit();
}

// Fix #5: Generate CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$owner_id = isset($_GET['owner_id']) ? (int) $_GET['owner_id'] : 0;
if ($owner_id <= 0 || $owner_id !== (int)$_SESSION['id']) {
    header('location:profile.php');
    exit();
}

// Fix #1: Prepared statement for SELECT
$stmt = $connect->prepare("SELECT * FROM bus_owner WHERE owner_id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $record     = $result->fetch_assoc();
    $first_name = $record['first_name'];
    $last_name  = $record['last_name'];
    $email      = $record['email'];
    $phone_no   = $record['phone_no'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fix #5: Verify CSRF token
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $phone_no   = $_POST['phone_no'];

    // Fix #1: Prepared statement for UPDATE
    // Fix #2: Original query had a trailing comma before WHERE —
    //         "phone_no= $phone_no,  WHERE owner_id=..." — now removed and safely bound
    $stmt = $connect->prepare(
        "UPDATE bus_owner
         SET first_name=?, last_name=?, email=?, phone_no=?
         WHERE owner_id=?"
    );
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone_no, $owner_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("location:profile.php");
        exit();
    } else {
        die("Update failed: " . $connect->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Owner Details</title>
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
    width: 100%; padding: 10px; margin-bottom: 15px;
    border: 1px solid #ccc; border-radius: 5px;
}
label { font-weight: bold; }
.update-btn {
    background-color: #007bff;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    cursor: pointer;
    font-weight: bold;
    width: 100%;
}
</style>
<body>
    <div class="container">
        <h1>Update Owner Details</h1>
        <form action="" method="post">
            <!-- Fix #5: CSRF hidden field -->
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required
                   value="<?= htmlspecialchars($first_name) ?>">

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required
                   value="<?= htmlspecialchars($last_name) ?>">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($email) ?>">

            <label for="phone_no">Phone No:</label>
            <input type="number" id="phone_no" name="phone_no" required
                   value="<?= htmlspecialchars($phone_no) ?>">

            <button type="submit" class="update-btn">Update</button>
        </form>
    </div>
</body>
</html>