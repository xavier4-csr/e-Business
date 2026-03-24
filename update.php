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

// Fix #1: Prepared statement for SELECT
$companyID = (int) $_GET['companyID']; // cast to int as basic sanity check
$stmt = $connect->prepare("SELECT * FROM Company_Information WHERE companyID = ?");
$stmt->bind_param("i", $companyID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $record             = $result->fetch_assoc();
    $companyName        = $record['companyName'];
    $owner              = $record['owner'];
    $streetAddress      = $record['streetAddress'];
    $city               = $record['city'];
    $postalCode         = $record['postalCode'];
    $website            = $record['website'];
    $companyDescription = $record['companyDescription'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fix #5: Verify CSRF token
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF token. Please go back and try again.");
    }

    $companyName        = $_POST['companyName'];
    $owner              = $_POST['owner'];
    $streetAddress      = $_POST['streetAddress'];
    $city               = $_POST['city'];       // Fix: was missing quotes in original query
    $postalCode         = $_POST['postalCode'];
    $website            = $_POST['website'];
    $companyDescription = $_POST['companyDescription'];

    // Fix #1: Prepared statement for UPDATE (city was also unquoted in original — now safely bound)
    $stmt = $connect->prepare(
        "UPDATE Company_Information
         SET companyName=?, owner=?, streetAddress=?, city=?, postalCode=?, website=?, companyDescription=?
         WHERE companyID=?"
    );
    $stmt->bind_param(
        "sssssssi",
        $companyName, $owner, $streetAddress,
        $city, $postalCode, $website, $companyDescription,
        $companyID
    );

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
    <title>Update Company</title>
</head>
<style>
body {
    font-family: Arial, sans-serif;
    background-image: url('background.jpg');
    background-size: cover;
    margin: 0;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: left;
}
.container {
    background-color: rgba(255,255,255,0.9);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 0px rgba(0,0,0,0.1);
}
h1 { color: #333333; text-align: center; margin-bottom: 20px; }
form { width: 100%; }
fieldset { border: none; padding: 5px; }
legend { font-weight: bold; margin-bottom: 10px; }
label { color: #333333; display: block; margin-bottom: 0.5em; }
input[type="text"], input[type="url"], textarea {
    width: 100%; padding: 10px; margin-bottom: 20px;
    border-radius: 5px; border: 1px solid #ccc; transition: border-color 0.3s ease;
}
.address { display: flex; }
.address div { flex-grow: 1; margin-right: 10px; }
.captcha { margin-top: 10px; }
.registerbtn {
    background-color: #007bff; color: white; padding: 10px 20px;
    border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;
}
.registerbtn:hover { background-color: #0056b3; }
</style>
<body>
    <h1>Update Company Details</h1>
    <div class="container">
        <form method="post">
            <!-- Fix #5: CSRF hidden field -->
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <fieldset>
                <legend>Company Information</legend>

                <label for="companyName">Company Name*</label>
                <input type="text" id="companyName" name="companyName"
                       value="<?= htmlspecialchars($companyName) ?>" required>

                <label for="owner">Owner*</label>
                <input type="text" id="owner" name="owner"
                       value="<?= htmlspecialchars($owner) ?>" required>

                <div class="address">
                    <div>
                        <label for="streetAddress">Street Address*</label>
                        <input type="text" id="streetAddress" name="streetAddress"
                               value="<?= htmlspecialchars($streetAddress) ?>" required>
                    </div>
                    <div>
                        <label for="city">City</label>
                        <input type="text" id="city" name="city"
                               value="<?= htmlspecialchars($city) ?>">
                    </div>
                    <div>
                        <label for="postalCode">Postal Code</label>
                        <input type="text" id="postalCode" name="postalCode"
                               value="<?= htmlspecialchars($postalCode) ?>">
                    </div>
                </div>

                <label for="website">Website</label>
                <input type="url" id="website" name="website"
                       value="<?= htmlspecialchars($website) ?>">

                <label for="companyDescription">Company Description</label>
                <textarea id="companyDescription" name="companyDescription" rows="4"><?= htmlspecialchars($companyDescription) ?></textarea>

                <label for="verification">Verification*</label>
                <div class="captcha">
                    <input type="checkbox" id="verification" name="verification" required>
                    <span>I'm not a robot</span>
                </div>
            </fieldset>

            <input type="submit" name="update" value="Update" class="registerbtn">
        </form>
    </div>
</body>
</html>