<?php
session_start();
include "connectdb.php";

// Fix #5: Verify CSRF token — delete must be a POST action, not a bare GET link
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Reject direct GET requests; deletion must come from a form submission
    header("location:profile.php");
    exit();
}

if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    die("Invalid CSRF token. Please go back and try again.");
}

$companyID = (int) $_POST['companyID']; // Fix #1: cast to int; no interpolation

// Fix #1: Prepared statement for DELETE
$stmt = $connect->prepare("DELETE FROM Company_Information WHERE companyID = ?");
$stmt->bind_param("i", $companyID);

if ($stmt->execute()) {
    $stmt->close();
    // Fix #3: was "locaton" — corrected to "location"
    header("location:profile.php");
    exit();
} else {
    die($connect->error);
}
?>