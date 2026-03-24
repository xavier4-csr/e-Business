<?php
/**
 * User Profile & Account Settings Page
 * Displays and manages user account information
 * Includes: personal details, photo upload, and account settings
 * Company listings moved to dashboard.php for better separation of concerns
 */

// Initialize session for user authentication
session_start();

// Include database connection module
include "connectdb.php";

// Include authentication helper functions (require_login, logout_user, etc.)
include "includes/auth.php";

// Verify user is logged in, otherwise redirect to login page
require_login();

// Include HTML header with meta tags, styles, and CSRF token generation
include "includes/header.php";

// Set page title for browser tab
$page_title = "Profile Settings - RegE";

// Get current user ID from session
$user_id = get_current_user_id();

// Prepare database query to fetch current user's full information
$stmt = $connect->prepare(
    "SELECT owner_id, first_name, last_name, email, phone_no, profile_picture_url 
     FROM bus_owner 
     WHERE owner_id = ?"
);

// Bind the user ID parameter as integer type for security
$stmt->bind_param("i", $user_id);

// Execute the prepared statement against the database
$stmt->execute();

// Retrieve the result set from the query
$result = $stmt->get_result();

// Check if user record was found in database
if ($result && $result->num_rows > 0) {
    // Fetch the user's data as an associative array
    $user_data = $result->fetch_assoc();
    
    // Extract individual user fields for template display
    $owner_id = $user_data['owner_id'];
    $first_name = $user_data['first_name'];
    $last_name = $user_data['last_name'];
    $email = $user_data['email'];
    $phone_no = $user_data['phone_no'];
    $profile_picture_url = $user_data['profile_picture_url'];
} else {
    // User not found, redirect to home page as fallback
    header("location:Home.php");
    exit();
}

// Close prepared statement to free database resources
$stmt->close();

// Initialize success and error message variables
$success_message = '';
$error_message = '';


// Handle profile photo upload POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['fileInput'])
    && $_FILES['fileInput']['error'] == UPLOAD_ERR_OK) {

    // Verify CSRF token to prevent cross-site request forgery attacks
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        // Invalid CSRF token, terminate request
        die("Invalid CSRF token. Photo upload failed.");
    }

    // Get uploaded file information
    $fileInfo = $_FILES['fileInput'];

    // Check file size limit (2MB maximum)
    if ($fileInfo['size'] > 2 * 1024 * 1024) {
        // File too large, set error message
        $error_message = "❌ Error: Image must be 2MB or less.";
    } else {
        // Initialize file info resource to check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        
        // Get actual MIME type of uploaded file
        $mime = $finfo->file($fileInfo['tmp_name']);
        
        // Define whitelist of allowed image MIME types
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        // Check if uploaded file MIME type is in allowed list
        if (!in_array($mime, $allowed, true)) {
            // MIME type not allowed, set error message
            $error_message = "❌ Error: Invalid image type. Only JPG, PNG, GIF, and WEBP allowed.";
        } else {
            // Read file content into memory
            $fileData = file_get_contents($fileInfo['tmp_name']);
            
            // Create data URI combining MIME type and base64 encoded image data
            $profile_picture_url = 'data:' . $mime . ';base64,' . base64_encode($fileData);

            // Prepare UPDATE statement to store image in database
            $stmt = $connect->prepare("UPDATE bus_owner SET profile_picture_url=? WHERE owner_id=?");
            
            // Bind the image data (string) and user ID (integer) parameters
            $stmt->bind_param("si", $profile_picture_url, $user_id);
            
            // Execute the update query
            if ($stmt->execute()) {
                // Photo update successful, set success message
                $success_message = "✅ Profile photo updated successfully!";
            } else {
                // Update failed, set error message with database error details
                $error_message = "❌ Error updating profile photo: " . $connect->error;
            }
            
            // Close prepared statement to free resources
            $stmt->close();
        }
    }
}

?>

<!-- Include shared navigation bar component -->
<?php include "includes/nav.php"; ?>

<!-- Main container for profile content -->
<div class="container">
    
    <!-- Page heading -->
    <h1>👤 Profile Settings</h1>
    
    <!-- Success message if profile was updated -->
    <?php if (!empty($success_message)): ?>
        <!-- Display success message in green alert box -->
        <div class="success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Error message if something went wrong -->
    <?php if (!empty($error_message)): ?>
        <!-- Display error message in red alert box -->
        <div class="error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Profile information section -->
    <div class="card">
        <!-- Section heading -->
        <h2>Account Information</h2>
        
        <!-- Profile photo section -->
        <div style="text-align: center; margin-bottom: 20px;">
            <!-- Profile photo container with circular styling -->
            <div style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 15px; background-color: #f0f0f0; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 3px solid #ddd;">
                <!-- Profile image: Show uploaded photo or placeholder -->
                <img id="profilePhoto" 
                     src="<?php echo !empty($profile_picture_url) ? htmlspecialchars($profile_picture_url) : 'placeholder.png'; ?>" 
                     alt="Profile photo"
                     style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            
            <!-- Photo upload form section -->
            <form method="post" enctype="multipart/form-data">
                <!-- Hidden CSRF token for form security -->
                <input type="hidden" 
                       name="csrf" 
                       value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
                
                <!-- Hidden file input: Triggered by button click -->
                <input type="file" 
                       id="fileInput" 
                       name="fileInput" 
                       style="display:none;" 
                       accept="image/*"
                       onchange="document.querySelector('form').submit();">
                
                <!-- Button to trigger file selection dialog -->
                <button type="button" 
                        class="btn" 
                        onclick="document.getElementById('fileInput').click();"
                        style="margin-bottom: 10px;">
                    🖼️ Change Photo
                </button>
            </form>
        </div>
        
        <!-- User information display -->
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <!-- First name display -->
            <p style="margin: 8px 0;">
                <strong>First Name:</strong> 
                <span><?php echo htmlspecialchars($first_name); ?></span>
            </p>
            
            <!-- Last name display -->
            <p style="margin: 8px 0;">
                <strong>Last Name:</strong> 
                <span><?php echo htmlspecialchars($last_name); ?></span>
            </p>
            
            <!-- Email address display -->
            <p style="margin: 8px 0;">
                <strong>Email:</strong> 
                <span><?php echo htmlspecialchars($email); ?></span>
            </p>
            
            <!-- Phone number display -->
            <p style="margin: 8px 0;">
                <strong>Phone:</strong> 
                <span><?php echo htmlspecialchars($phone_no); ?></span>
            </p>
        </div>
        
        <!-- Edit profile button -->
        <a href="update-user%20info.php?owner_id=<?php echo (int)$owner_id; ?>" 
           class="btn" 
           style="background-color: #28a745; text-decoration: none; display: inline-block;">
            ✏️ Edit Account Information
        </a>
    </div>
    
    <!-- Quick actions section -->
    <div class="card">
        <!-- Section heading -->
        <h2>Quick Actions</h2>
        
        <!-- Buttons for common user actions -->
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            
            <!-- Dashboard link: View all companies -->
            <a href="dashboard.php" 
               class="btn" 
               style="background-color: #17a2b8; text-decoration: none;">
                📊 Back to Dashboard
            </a>
            
            <!-- Register business link: Add new company -->
            <a href="business-registration.php" 
               class="btn" 
               style="background-color: #28a745; text-decoration: none;">
                ➕ Register New Business
            </a>
            
            <!-- Logout link: End user session -->
            <a href="logout.php" 
               class="btn btn-danger" 
               style="text-decoration: none;">
                🚪 Logout
            </a>
        </div>
    </div>
    
    <!-- Account management section -->
    <div class="card">
        <!-- Section heading -->
        <h2>Account Management</h2>
        
        <!-- Account options -->
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
            <p style="margin: 10px 0;">
                <strong>⚙️ Future Features:</strong>
            </p>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <!-- Placeholder for password change feature -->
                <li>Change Password (Coming Soon)</li>
                <!-- Placeholder for two-factor authentication -->
                <li>Two-Factor Authentication (Coming Soon)</li>
                <!-- Placeholder for activity log -->
                <li>Activity Log (Coming Soon)</li>
            </ul>
        </div>
    </div>
    
</div>

<!-- JavaScript: Update profile photo preview when user selects image -->
<script>
    // Get file input element reference
    const fileInput = document.getElementById('fileInput');
    // Get profile photo image element reference
    const profilePhoto = document.getElementById('profilePhoto');

    // Listen for file selection change event
    fileInput.addEventListener('change', function () {
        // Check if files were selected by user
        if (this.files && this.files[0]) {
            // Create new FileReader instance to read file content
            const reader = new FileReader();
            
            // Event handler when file is successfully loaded
            reader.onload = function (event) {
                // Update image source to show preview of selected file
                profilePhoto.src = event.target.result;
            };
            
            // Read the selected file as a data URL (base64 encoded string)
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>

<!-- Include footer component with copyright and links -->
<?php include "includes/footer.php"; ?>