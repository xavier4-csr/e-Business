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

    $companyName        = $_POST['companyName'];
    $owner              = $_POST['owner'];
    $streetAddress      = $_POST['streetAddress'];
    $city               = $_POST['city'];
    $postalCode         = $_POST['postalCode'];
    $website            = $_POST['website'];
    $companyDescription = $_POST['companyDescription'];

    // Fix #1: Prepared statement to check for duplicate company name
    $stmt = $connect->prepare("SELECT companyID FROM Company_Information WHERE companyName = ?");
    $stmt->bind_param("s", $companyName);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $unsuccess = 1;
    } else {
        $stmt->close();
        // Fix #1: Prepared statement for INSERT
        $stmt = $connect->prepare(
            "INSERT INTO Company_Information
             (companyName, owner, streetAddress, city, postalCode, website, companyDescription)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssssss",
            $companyName, $owner, $streetAddress,
            $city, $postalCode, $website, $companyDescription
        );

        if ($stmt->execute()) {
            $success = 1;
            header("location:profile.php");
            exit();
        } else {
            die("Registration failed: " . $connect->error);
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Registration Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
body {
    font-family: Arial, sans-serif;
    background-image: url('background.jpg');
    background-size: cover;
    margin: 0;
    height: 100vh;
    display: flex;
    flex-direction: row;
}
.container {
    background-color: rgba(255,255,255,0.9);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 0px rgba(0,0,0,0.1);
    margin-left: auto;
    margin-right: auto;
}
nav {
    background-color: #333;
    color: #fff;
    padding: 10px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
nav img { width: 40px; margin: auto; }
.taskbar { list-style-type: none; margin-top: 10px; padding-left: 0; }
.taskbar li { margin-bottom: 10px; }
.taskbar li a { text-decoration: none; color: #fff; display: block; }
h1 { color: #333333; text-align: center; margin-bottom: 20px; }
p { color: #666666; }
form { width: 100%; }
fieldset { border: none; padding: 5px; }
legend { font-weight: bold; margin-bottom: 10px; }
label { color: #333333; display: block; margin-bottom: 0.5em; }
input[type="text"], input[type="url"], textarea {
    width: 100%; padding: 10px; margin-bottom: 20px;
    border-radius: 5px; border: 1px solid #ccc; transition: border-color 0.3s ease;
}
input[type="text"]:focus, input[type="url"]:focus, textarea:focus { border-color: #007bff; }
.required::after { content: "*"; color: red; }
.address { display: flex; }
.address div { flex-grow: 1; margin-right: 10px; }
.captcha { margin-top: 10px; }
.registerbtn {
    background-color: #007bff; color: white; padding: 10px 20px;
    border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;
}
.registerbtn:hover { background-color: #0056b3; }
.error { color: red; text-align: center; font-size: 20px; padding: 20px; }
</style>

<body>
    <h1>RegE</h1>
    <nav>
        <img src="logo.png" alt="Logo">
        <ul class="taskbar">
            <li><a href="Home.php">Home</a></li>
            <li><a href="business-registration.php">Register Business</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="profile.php">Profile</a></li>
        </ul>
    </nav>

    <div id="errorMessages" class="error"></div>
    <br>
    <h1>Company Registration Form</h1>
    <hr>
    <p>Register your business using this form. Required fields are marked with an asterisk (*).</p>

    <form action="" method="post" onsubmit="return validateForm()">
        <!-- Fix #5: CSRF hidden field -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <fieldset>
            <legend>Company Information</legend>

            <label for="companyName">Company Name*</label>
            <input type="text" id="companyName" name="companyName" required>

            <label for="owner">Owner*</label>
            <input type="text" id="owner" name="owner" required>

            <div class="address">
                <div>
                    <label for="streetAddress">Street Address*</label>
                    <input type="text" id="streetAddress" name="streetAddress" required>
                </div>
                <div>
                    <label for="city">City</label>
                    <input type="text" id="city" name="city">
                </div>
                <div>
                    <label for="postalCode">Postal Code</label>
                    <input type="text" id="postalCode" name="postalCode">
                </div>
            </div>

            <label for="website">Website</label>
            <input type="url" id="website" name="website">

            <label for="companyDescription">Company Description</label>
            <textarea id="companyDescription" name="companyDescription" rows="4"></textarea>

            <?php if ($unsuccess): ?>
                <div class="error">Company Name already exists!</div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="error">Company Registered Successfully</div>
            <?php endif; ?>

            <label for="verification">Verification*</label>
            <div class="captcha">
                <input type="checkbox" id="verification" name="verification" required>
                <span>I'm not a robot</span>
            </div>
        </fieldset>

        <button type="submit" class="registerbtn">Register Company</button>
    </form>
    </div>

    <script>
    function validateForm() {
        var companyName   = document.getElementById("companyName").value;
        var owner         = document.getElementById("owner").value;
        var streetAddress = document.getElementById("streetAddress").value;
        var city          = document.getElementById("city").value;
        var postalCode    = document.getElementById("postalCode").value;
        var website       = document.getElementById("website").value;
        var isValid       = true;
        var errorMessages = "";

        if (!/^[A-Za-z\s]+$/.test(companyName)) {
            errorMessages += "Company name must contain only letters and spaces.\n";
            isValid = false;
        }
        if (!/^[A-Za-z\s]+$/.test(owner)) {
            errorMessages += "Owner name must contain only letters and spaces.\n";
            isValid = false;
        }
        if (streetAddress.length < 5) {
            errorMessages += "Street address must be at least 5 characters long.\n";
            isValid = false;
        }
        if (city && !/^[A-Za-z\s]+$/.test(city)) {
            errorMessages += "City must contain only letters and spaces.\n";
            isValid = false;
        }
        if (postalCode && !/^\d{5}$/.test(postalCode)) {
            errorMessages += "Postal code must be a 5-digit number.\n";
            isValid = false;
        }
        if (website.trim() !== "" && !isValidUrl(website)) {
            errorMessages += "Website URL is not valid.\n";
            isValid = false;
        }
        if (!isValid) {
            document.getElementById("errorMessages").innerHTML = errorMessages;
        }
        return isValid;
    }

    function isValidUrl(url) {
        return /^(ftp|http|https):\/\/[^ "]+$/.test(url);
    }
    </script>
</body>
</html>