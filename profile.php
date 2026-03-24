<?php
// Session hardening + auth guard
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_samesite', 'Strict');

session_start();
include "connectdb.php";

if (!isset($_SESSION['email'])) {
    header("location:login.php");
    exit();
}

// Fix #1: Prepared statement for fetching owner
$stmt = $connect->prepare("SELECT * FROM bus_owner WHERE email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $bus_owner           = $result->fetch_assoc();
    $owner_id            = $bus_owner['owner_id'];
    $first_name          = $bus_owner['first_name'];
    $email               = $bus_owner['email'];
    $phone_no            = $bus_owner['phone_no'];
    $profile_picture_url = $bus_owner['profile_picture_url'];
} else {
    header("location:Home.php");
    exit();
}
$stmt->close();

// Handle profile photo upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['fileInput'])
    && $_FILES['fileInput']['error'] == UPLOAD_ERR_OK) {

    // Fix #5: Verify CSRF on photo upload too
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die("Invalid CSRF token.");
    }

    $fileInfo = $_FILES['fileInput'];

    if ($fileInfo['size'] > 2 * 1024 * 1024) {
        echo "Error: Image must be 2MB or less.";
    } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($fileInfo['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mime, $allowed, true)) {
            echo "Error: Invalid image type. Only JPG, PNG, GIF, and WEBP allowed.";
        } else {
            $fileData = file_get_contents($fileInfo['tmp_name']);
            $profile_picture_url = 'data:' . $mime . ';base64,' . base64_encode($fileData);

            // Fix #1: Prepared statement for photo UPDATE
            $stmt = $connect->prepare("UPDATE bus_owner SET profile_picture_url=? WHERE email=?");
            $stmt->bind_param("ss", $profile_picture_url, $_SESSION['email']);
            if (!$stmt->execute()) {
                echo "Error updating profile photo: " . $connect->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Profile</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
.container {
    max-width: 600px; margin: 50px auto; background-color: #fff;
    padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h1 { text-align: center; margin-bottom: 20px; font-size: 40px; }
.profile-photo {
    display: block; width: 190px; height: 190px; margin: 0 auto 20px;
    border-radius: 50%; background-color: #ccc; overflow: hidden;
}
.profile-photo img { width: 100%; height: 100%; object-fit: cover; }
.user-details { text-align: center; margin-bottom: 20px; font-size: 20px; }
.user-details p { margin: 5px 0; }
.btn-update {
    display: block; width: 100%; padding: 10px; text-align: center;
    background-color: #007bff; color: #fff; border: none; border-radius: 10px;
    cursor: pointer; transition: background-color 0.3s; font-size: 15px;
}
.btn-update:hover { background-color: #0056b3; }
nav { background-color: #333; color: #fff; padding: 10px; }
nav img { width: 40px; float: left; display: inline-block; margin: auto; }
.taskbar { list-style-type: none; text-align: center; margin-top: 10px; }
.taskbar li { display: inline; margin-right: 20px; }
.taskbar li a { text-decoration: none; color: #fff; }
table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
th { background-color: lightgrey; }
</style>
</head>
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
        <li style="float: right;"><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h1>User Profile</h1>

    <div class="profile-photo" id="profilePhotoContainer">
        <img id="profilePhoto" src="<?= htmlspecialchars($profile_picture_url ?: 'placeholder.png') ?>" alt="Profile photo">
    </div>

    <div class="user-details">
        <p><strong>Name:</strong> <?= htmlspecialchars($first_name) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($phone_no) ?></p>
    </div>

    <div style="text-align:center; margin-bottom:10px;">
        <a href="update-user info.php?owner_id=<?= (int)$owner_id ?>">
            <button style="padding:7px;">Edit</button>
        </a>
    </div>

    <!-- Fix #5: Photo upload form includes CSRF token -->
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <input type="file" id="fileInput" name="fileInput" style="display:none;" accept="image/*">
        <button type="button" class="btn-update" onclick="document.getElementById('fileInput').click();">
            Upload Photo
        </button>
        <input type="submit" value="Save Photo" class="btn-update" style="margin-top:8px;">
    </form>
</div>

<h1>COMPANIES REGISTERED</h1>
<div style="padding: 20px; overflow-x: auto;">
    <table style="width:100%;">
        <tr>
            <th>COMPANY NAME</th>
            <th>OWNER</th>
            <th>DESCRIPTION</th>
            <th>STREET</th>
            <th>CITY</th>
            <th>POSTAL</th>
            <th>WEBSITE</th>
            <th colspan="2">ACTIONS</th>
        </tr>

        <?php
        // Fix #1: No raw interpolation — plain SELECT with no user input is safe here
        $result = $connect->query("SELECT * FROM Company_Information");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cID   = (int) $row['companyID'];
                $cName = htmlspecialchars($row['companyName']);
                $cOwn  = htmlspecialchars($row['owner']);
                $cDesc = htmlspecialchars($row['companyDescription']);
                $cStr  = htmlspecialchars($row['streetAddress']);
                $cCity = htmlspecialchars($row['city']);
                $cPost = htmlspecialchars($row['postalCode']);
                $cWeb  = htmlspecialchars($row['website']);
                $csrf  = htmlspecialchars($_SESSION['csrf']);

                echo "
                <tr>
                    <td>$cName</td>
                    <td>$cOwn</td>
                    <td>$cDesc</td>
                    <td>$cStr</td>
                    <td>$cCity</td>
                    <td>$cPost</td>
                    <td>$cWeb</td>
                    <td>
                        <!-- Fix #5: Delete is now a POST form with CSRF — not a bare GET link -->
                        <form method='post' action='delete.php'
                              onsubmit=\"return confirm('Delete this company?')\">
                            <input type='hidden' name='csrf' value='$csrf'>
                            <input type='hidden' name='companyID' value='$cID'>
                            <button type='submit'>Delete</button>
                        </form>
                    </td>
                    <td>
                        <a href='update.php?companyID=$cID'>
                            <button>Update</button>
                        </a>
                    </td>
                </tr>";
            }
        }
        ?>
    </table>
</div>

<script>
const fileInput    = document.getElementById('fileInput');
const profilePhoto = document.getElementById('profilePhoto');

fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            profilePhoto.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>