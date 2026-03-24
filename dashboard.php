<?php
/**
 * Dashboard - Main Application View
 * Shows all companies registered by the logged-in user
 * Provides quick actions: add, edit, delete companies
 * Displays key metrics and recent activity
 */

// Initialize the session for user authentication
session_start();

// Import database connection from centralized includes folder
include "connectdb.php";

// Import authentication helper functions
include "includes/auth.php";

// Check if user is logged in, otherwise redirect to login page
require_login();

// Import HTML header with meta tags and CSS styles
include "includes/header.php";

// Set page title to be displayed in browser tab
$page_title = "Dashboard - RegE";

// Get the current logged-in user's ID from session
$user_id = get_current_user_id();

// Create prepared statement to fetch all companies owned by current user
$stmt = $connect->prepare(
    "SELECT companyID, companyName, owner, streetAddress, city, postalCode, website, companyDescription 
     FROM Company_Information 
     WHERE owner_id = ? 
     ORDER BY companyID DESC"
);

// Bind the user_id parameter as integer type
$stmt->bind_param("i", $user_id);

// Execute the prepared statement
$stmt->execute();

// Get the result set from the query
$result = $stmt->get_result();

// Fetch all rows as associative arrays
$companies = $result->fetch_all(MYSQLI_ASSOC);

// Close the prepared statement to free up resources
$stmt->close();

// Count total number of companies for statistics display
$total_companies = count($companies);

?>

<!-- Include the navigation bar component -->
<?php include "includes/nav.php"; ?>

<!-- Main container for dashboard content -->
<div class="container">
    
    <!-- Page heading -->
    <h1>📊 Dashboard</h1>
    
    <!-- Statistics Cards Row: Display key metrics -->
    <div class="stats">
        
        <!-- Total Companies Card -->
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <!-- Display total company count -->
            <div class="stat-number"><?php echo $total_companies; ?></div>
            <!-- Card label -->
            <div class="stat-label">Total Companies Registered</div>
        </div>
        
        <!-- Active Companies Card (same as total for now, can be enhanced later) -->
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <!-- Display active count (for demo purposes, same as total) -->
            <div class="stat-number"><?php echo $total_companies; ?></div>
            <!-- Card label -->
            <div class="stat-label">Active Listings</div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <!-- Action button to register new company -->
            <div style="font-size: 24px; margin-bottom: 10px;">➕</div>
            <!-- Card label and link -->
            <div class="stat-label">
                <a href="business-registration.php" style="color: white; text-decoration: none; font-weight: bold;">
                    Add New Company
                </a>
            </div>
        </div>
        
        <!-- Profile Card: Quick access to user profile -->
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <!-- Profile icon -->
            <div style="font-size: 24px; margin-bottom: 10px;">👤</div>
            <!-- Card label and link -->
            <div class="stat-label">
                <a href="profile.php" style="color: white; text-decoration: none; font-weight: bold;">
                    Your Profile
                </a>
            </div>
        </div>
    </div>
    
    <!-- Section heading for companies list -->
    <h2>Your Registered Companies</h2>
    
    <!-- Check if user has any companies registered -->
    <?php if ($total_companies === 0): ?>
        
        <!-- Display message when no companies exist -->
        <div class="info" style="text-align: center; padding: 30px;">
            <!-- Empty state message -->
            <p style="font-size: 16px; margin-bottom: 10px;">
                📭 No companies registered yet.
            </p>
            <!-- Help text with next steps -->
            <p style="font-size: 14px; color: #666;">
                Get started by registering your first business!
            </p>
            <!-- Button to register first company -->
            <a href="business-registration.php" class="btn" style="margin-top: 10px; display: inline-block;">
                Register Your First Company
            </a>
        </div>
        
    <?php else: ?>
        
        <!-- Display companies table when companies exist -->
        <table>
            <!-- Table header row -->
            <thead>
                <tr>
                    <!-- Company name column -->
                    <th>Company Name</th>
                    <!-- Company owner column -->
                    <th>Owner</th>
                    <!-- Company location column -->
                    <th>Location</th>
                    <!-- Company website column -->
                    <th>Website</th>
                    <!-- Company description column -->
                    <th>Description</th>
                    <!-- Action buttons column -->
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            
            <!-- Table body: Iterate through each company -->
            <tbody>
                <?php foreach ($companies as $company): ?>
                    <!-- Table row for each company -->
                    <tr>
                        <!-- Company name cell: Display company name with sanitization -->
                        <td>
                            <strong><?php echo htmlspecialchars($company['companyName']); ?></strong>
                        </td>
                        
                        <!-- Owner cell: Display company owner name -->
                        <td><?php echo htmlspecialchars($company['owner']); ?></td>
                        
                        <!-- Location cell: Display city and postal code -->
                        <td>
                            <?php 
                                // Combine city and postal code for location display
                                $location = htmlspecialchars($company['city']) . 
                                           (htmlspecialchars($company['postalCode']) ? ', ' . htmlspecialchars($company['postalCode']) : '');
                                echo !empty($location) ? $location : 'N/A';
                            ?>
                        </td>
                        
                        <!-- Website cell: Display company website as clickable link -->
                        <td>
                            <?php 
                                // Check if website URL is provided
                                if (!empty($company['website'])) {
                                    // Display website as external link
                                    echo '<a href="' . htmlspecialchars($company['website']) . '" target="_blank" style="color: #007bff; text-decoration: none;">
                                            Visit Site
                                          </a>';
                                } else {
                                    // Show N/A if no website provided
                                    echo 'N/A';
                                }
                            ?>
                        </td>
                        
                        <!-- Description cell: Show truncated company description -->
                        <td>
                            <?php 
                                // Truncate description to 50 characters for better table display
                                $desc = htmlspecialchars($company['companyDescription']);
                                echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                            ?>
                        </td>
                        
                        <!-- Actions cell: Edit and Delete buttons -->
                        <td style="display: flex; gap: 8px;">
                            
                            <!-- Edit button: Redirect to update page -->
                            <a href="update.php?companyID=<?php echo (int)$company['companyID']; ?>" 
                               class="btn" 
                               style="background-color: #28a745; flex: 1; text-align: center; text-decoration: none;">
                                ✏️ Edit
                            </a>
                            
                            <!-- Delete button: Open confirmation dialog and submit form -->
                            <form method="POST" 
                                  action="delete.php" 
                                  style="flex: 1;" 
                                  onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                                
                                <!-- CSRF token for security: Prevents cross-site request forgery -->
                                <input type="hidden" 
                                       name="csrf" 
                                       value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
                                
                                <!-- Company ID to delete: Sent to delete.php -->
                                <input type="hidden" 
                                       name="companyID" 
                                       value="<?php echo (int)$company['companyID']; ?>">
                                
                                <!-- Delete button: Submit form to delete endpoint -->
                                <button type="submit" 
                                        class="btn btn-danger" 
                                        style="width: 100%; cursor: pointer;">
                                    🗑️ Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Action buttons below table -->
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            
            <!-- Button to register another company -->
            <a href="business-registration.php" class="btn" style="text-decoration: none;">
                ➕ Register Another Company
            </a>
            
            <!-- Button to view user profile -->
            <a href="profile.php" class="btn" style="background-color: #6c757d; text-decoration: none;">
                👤 View Profile
            </a>
        </div>
        
    <?php endif; ?>
    
</div>

<!-- Include footer component with copyright and links -->
<?php include "includes/footer.php"; ?>
