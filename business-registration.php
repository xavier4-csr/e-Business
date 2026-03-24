<?php
/**
 * Business Registration Form Page
 * Allows authenticated users to register their business
 * Requires login - auth guard redirects to login if not authenticated
 */

// Initialize the session for user authentication
session_start();

// Include database connection module
include "connectdb.php";

// Include authentication helper functions
include "includes/auth.php";

// Check if user is logged in, otherwise redirect to login page
require_login();

// Include HTML header with meta tags and styles
include "includes/header.php";

// Set page title for browser tab
$page_title = "Register Business - RegE";

// Ensure CSRF token exists in session for form security
if (empty($_SESSION['csrf'])) {
    // Generate cryptographically secure random CSRF token
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Initialize variables to track form submission status
$success = 0;
$unsuccess = 0;

// Check if form was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token to prevent cross-site request forgery
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        // CSRF token validation failed, terminate request
        die("Invalid CSRF token. Please go back and try again.");
    }

    // Get the current logged-in user's ID from session
    $user_id = get_current_user_id();

    // Retrieve and trim form inputs to remove extra whitespace
    $companyName        = trim($_POST['companyName'] ?? '');
    $owner              = trim($_POST['owner'] ?? '');
    $streetAddress      = trim($_POST['streetAddress'] ?? '');
    $city               = trim($_POST['city'] ?? '');
    $postalCode         = trim($_POST['postalCode'] ?? '');
    $website            = trim($_POST['website'] ?? '');
    $companyDescription = trim($_POST['companyDescription'] ?? '');

    // Validate required fields to ensure data completeness
    if (empty($companyName) || empty($owner) || empty($streetAddress)) {
        // Required fields missing, set error flag
        $unsuccess = 1;
    } else {
        // Prepare statement to check if company name already exists in database
        $stmt = $connect->prepare("SELECT companyID FROM Company_Information WHERE companyName = ?");
        
        // Bind company name parameter as string type
        $stmt->bind_param("s", $companyName);
        
        // Execute the check query
        $stmt->execute();
        
        // Store result to check number of rows found
        $stmt->store_result();

        // Check if a company with this name already exists
        if ($stmt->num_rows > 0) {
            // Company name is not unique, set error flag
            $unsuccess = 1;
        } else {
            // Close previous statement to prepare a new one
            $stmt->close();
            
            // Prepare INSERT statement to add new company to database
            $stmt = $connect->prepare(
                "INSERT INTO Company_Information
                 (companyName, owner, streetAddress, city, postalCode, website, companyDescription, owner_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            // Bind all parameters: company info (7 strings) and owner_id (integer)
            $stmt->bind_param(
                "sssssssi",
                $companyName, $owner, $streetAddress,
                $city, $postalCode, $website, $companyDescription, $user_id
            );

            // Execute the INSERT query
            if ($stmt->execute()) {
                // Company registration successful, set success flag
                $success = 1;
                // Redirect to dashboard to view the newly registered company
                header("location:dashboard.php");
                exit();
            } else {
                // Registration failed, terminate with error message
                die("Registration failed: " . $connect->error);
            }
        }
        
        // Close the prepared statement to free resources
        $stmt->close();
    }
}

?>

<!-- Include shared navigation bar component -->
<?php include "includes/nav.php"; ?>

<!-- Main container for business registration form -->
<div class="container">
    
    <!-- Page heading -->
    <h1>✎ Register Your Business</h1>
    
    <!-- Help text explaining form purpose -->
    <p style="color: #666; margin-bottom: 20px;">
        Use this form to register your business. Required fields are marked with <span style="color: red;">*</span>.
    </p>
    
    <!-- Error message container: Display errors from form validation -->
    <div id="errorMessages" class="error" style="display: none;"></div>
    
    <!-- Business registration form -->
    <form action="" method="post" onsubmit="return validateForm()" class="form-group">
        
        <!-- CSRF token hidden field: Protects against cross-site request forgery -->
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">

        <!-- Company Information Section -->
        <fieldset style="border: none; padding: 0;">
            <legend style="font-weight: bold; margin-bottom:15px; font-size: 16px;">Company Information</legend>

            <!-- Company Name Field -->
            <label for="companyName" style="font-weight: bold;">Company Name <span style="color: red;">*</span></label>
            <!-- Input expects only letters and spaces -->
            <input type="text" 
                   id="companyName" 
                   name="companyName" 
                   placeholder="e.g., Acme Corporation"
                   required 
                   style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

            <!-- Owner Name Field -->
            <label for="owner" style="font-weight: bold;">Owner Name <span style="color: red;">*</span></label>
            <!-- Input expects owner's full name -->
            <input type="text" 
                   id="owner" 
                   name="owner" 
                   placeholder="e.g., John Doe"
                   required
                   style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

            <!-- Address Section: Street, City, and Postal Code -->
            <div style="display: flex; gap: 15px;">
                <!-- Street Address Field -->
                <div style="flex: 2;">
                    <label for="streetAddress" style="font-weight: bold;">Street Address <span style="color: red;">*</span></label>
                    <!-- Full street address input -->
                    <input type="text" 
                           id="streetAddress" 
                           name="streetAddress" 
                           placeholder="e.g., 123 Main Street"
                           required
                           style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <!-- City Field -->
                <div style="flex: 1;">
                    <label for="city" style="font-weight: bold;">City</label>
                    <!-- City name input (optional) -->
                    <input type="text" 
                           id="city" 
                           name="city" 
                           placeholder="e.g., New York"
                           style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                
                <!-- Postal Code Field -->
                <div style="flex: 1;">
                    <label for="postalCode" style="font-weight: bold;">Postal Code</label>
                    <!-- ZIP/Postal code input (optional) -->
                    <input type="text" 
                           id="postalCode" 
                           name="postalCode" 
                           placeholder="e.g., 12345"
                           style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <!-- Website Field -->
            <label for="website" style="font-weight: bold;">Website</label>
            <!-- Company website URL input (optional) -->
            <input type="url" 
                   id="website" 
                   name="website" 
                   placeholder="e.g., https://www.example.com"
                   style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

            <!-- Company Description Field -->
            <label for="companyDescription" style="font-weight: bold;">Company Description</label>
            <!-- Textarea for detailed company information -->
            <textarea id="companyDescription" 
                      name="companyDescription" 
                      rows="4" 
                      placeholder="Describe your business, products, and services..."
                      style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>

            <!-- Verification Section -->
            <label for="verification" style="font-weight: bold;">Verification <span style="color: red;">*</span></label>
            <!-- Checkbox to verify user is human (placeholder for reCAPTCHA) -->
            <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background-color: #f9f9f9;">
                <input type="checkbox" 
                       id="verification" 
                       name="verification" 
                       required
                       style="margin-right: 10px;">
                <span>I confirm that the information provided is accurate and I'm not a robot</span>
            </div>
        </fieldset>

        <!-- Error/Success Messages from form processing -->
        <?php if ($unsuccess): ?>
            <!-- Display error message if company name already exists -->
            <div class="error" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                ❌ Company name already exists! Please choose a different name.
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <!-- Display success message if company registration was successful -->
            <div class="success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                ✅ Company registered successfully! Redirecting to dashboard...
            </div>
        <?php endif; ?>

        <!-- Submit Button -->
        <button type="submit" 
                class="btn" 
                style="background-color: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%;">
            ✎ Register Company
        </button>
    </form>
    
    <!-- Quick links -->
    <div style="margin-top: 20px; text-align: center;">
        <!-- Back to dashboard button -->
        <a href="dashboard.php" style="color: #007bff; text-decoration: none; margin-right: 15px;">
            ← Back to Dashboard
        </a>
    </div>
    
</div>

<!-- Form Validation JavaScript -->
<script>
    /**
     * Validate company registration form before submission
     * Checks field formats and displays errors to user
     */
    function validateForm() {
        // Get form field values
        const companyName = document.getElementById("companyName").value.trim();
        const owner = document.getElementById("owner").value.trim();
        const streetAddress = document.getElementById("streetAddress").value.trim();
        const city = document.getElementById("city").value.trim();
        const postalCode = document.getElementById("postalCode").value.trim();
        const website = document.getElementById("website").value.trim();
        
        // Initialize validation tracking
        let isValid = true;
        let errorMessages = "";

        // Validate company name: only letters and spaces allowed
        if (companyName && !/^[A-Za-z\s&\-.,]+$/.test(companyName)) {
            errorMessages += "❌ Company name must contain only letters, spaces, and basic punctuation.\n";
            isValid = false;
        }
        
        // Validate owner name: only letters and spaces allowed
        if (owner && !/^[A-Za-z\s\-]+$/.test(owner)) {
            errorMessages += "❌ Owner name must contain only letters and spaces.\n";
            isValid = false;
        }
        
        // Validate street address: minimum 5 characters required
        if (streetAddress.length < 5) {
            errorMessages += "❌ Street address must be at least 5 characters long.\n";
            isValid = false;
        }
        
        // Validate city: only letters and spaces if provided
        if (city && !/^[A-Za-z\s\-]+$/.test(city)) {
            errorMessages += "❌ City must contain only letters and spaces.\n";
            isValid = false;
        }
        
        // Validate postal code: must be 5-6 digits if provided
        if (postalCode && !/^\d{5,6}$/.test(postalCode)) {
            errorMessages += "❌ Postal code must be 5-6 digits.\n";
            isValid = false;
        }
        
        // Validate website URL: must be valid if provided
        if (website && !isValidUrl(website)) {
            errorMessages += "❌ Website URL is not valid. Use http:// or https://\n";
            isValid = false;
        }
        
        // Display errors or allow form submission
        if (!isValid) {
            // Show error messages in error container
            document.getElementById("errorMessages").style.display = "block";
            document.getElementById("errorMessages").innerHTML = "<strong>Please fix the following errors:</strong><br>" + 
                errorMessages.replace(/\n/g, "<br>");
        } else {
            // Hide error container if no errors
            document.getElementById("errorMessages").style.display = "none";
        }
        
        // Return validation result
        return isValid;
    }

    /**
     * Check if a URL is valid format
     * Accepts: http://, https://, ftp://
     */
    function isValidUrl(url) {
        // Regular expression for valid URL format
        return /^(ftp|http|https):\/\/[^ "]+$/.test(url);
    }
</script>

<!-- Include footer component with copyright -->
<?php include "includes/footer.php"; ?>