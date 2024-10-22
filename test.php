<?php
session_start(); // Start the session
require('Admin_connection.php'); // Include your database connection file

// Fetch the username from the session
$Uname = $_SESSION['Uname'];

try {
    // Prepare the SQL query using placeholders to prevent SQL injection
    $query = "SELECT Firstname, Roles, admin_profile, Uname FROM users WHERE Uname = :Uname";
    $stmt = $conn->prepare($query);
    
    // Bind the parameter
    $stmt->bindParam(':Uname', $Uname, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Store the user's data for display
    if ($user) {
        $Firstname = $user['Firstname']; // User's first name
        $Roles = $user['Roles']; // User's roles
        $admin_profile = $user['admin_profile']; // User's profile (image path)
    } else {
        // Redirect to the login page if user data is not found
        header("Location: admin_registration.php");
        exit();
    }
    
} catch (PDOException $e) {
    // Handle query errors
    echo "Error fetching user data: " . $e->getMessage();
    exit();
}

// Initialize an empty array to hold error messages
$errorMessages = [];

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $oldUpass = $_POST['currentUpass'] ?? null; // Get current password from input
    $newFirstname = $_POST['newFirstname'] ?? ''; // New first name
    $newUpass = $_POST['newUpass'] ?? ''; // New password
    $confirmUpass = $_POST['confirmUpass'] ?? ''; // Confirm new password

    // Fetch current user's password from the database
    $query = "SELECT Upass FROM users WHERE Uname = :Uname"; // Use 'Upass' instead of 'password'
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':Uname', $Uname, PDO::PARAM_STR);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the current password
    if ($currentUser && $oldUpass !== $currentUser['Upass']) { // No hashing, direct comparison
        $errorMessages[] = "Current password is incorrect.";
    }

    // Validate new password if the current password is correct
    if (empty($errorMessages)) {
        if ($newUpass !== $confirmUpass) {
            $errorMessages[] = "Passwords do not match.";
        } elseif (strlen($newUpass) < 6) {
            $errorMessages[] = "Password must be at least 6 characters long.";
        }
    }


    // Handle file upload if provided
    $newFileName = null; // Initialize to null
    if (isset($_FILES['newProfileImage']) && $_FILES['newProfileImage']['error'] == 0) {
        $fileTmpPath = $_FILES['newProfileImage']['tmp_name'];
        $fileName = $_FILES['newProfileImage']['name'];
        $fileSize = $_FILES['newProfileImage']['size'];
        $fileType = $_FILES['newProfileImage']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Validate file extension and size
        $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
        if (in_array($fileExtension, $allowedfileExtensions) && $fileSize < 2000000) { // limit to 2MB
            // Set a new file name and directory
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // unique file name
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            // Move the file to the uploads directory
            if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                $errorMessages[] = "Error moving the uploaded file.";
            }
        } else {
            $errorMessages[] = "Invalid file type or file size too large.";
        }
    }

     // If there are no errors, update user info in the database
     if (empty($errorMessages)) {
        try {
            // Update user info in the database, no hashing for new password
            $query = "UPDATE users SET Firstname = :firstname, Upass = :password, admin_profile = :profile WHERE Uname = :Uname"; // Use 'Upass' here as well
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':firstname', $newFirstname, PDO::PARAM_STR);
            $stmt->bindParam(':password', $newUpass, PDO::PARAM_STR); // No hashing
            $stmt->bindParam(':profile', $newFileName, PDO::PARAM_STR);
            $stmt->bindParam(':Uname', $Uname, PDO::PARAM_STR);
            $stmt->execute();

            // Redirect to the admin page or display success message
            header("Location: test.php"); // Adjust the redirect as needed
            exit();

        } catch (PDOException $e) {
            echo "Error updating user data: " . $e->getMessage();
        }
    }
}



// Handle adding intern account
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['internID'])) {
    // Get and sanitize user input
    $internID = trim($_POST['internID']);
    $InternPass = trim($_POST['InternPass'] ?? '');

    // Validate input
    if (empty($internID) || empty($InternPass)) {
        $_SESSION['message'] = 'Intern ID and password are required.';
    } else {
        // Optionally, you could enforce length and character requirements for passwords
        if (strlen($InternPass) < 6) {
            $_SESSION['message'] = 'Password must be at least 6 characters long.';
        } else {
            // Prepare SQL query to insert data
            $sql = "INSERT INTO intacc (internID, Internpass) VALUES (:internID, :InternPass)";

            try {
                $stmt = $conn->prepare($sql);
                
                // Bind parameters
                $stmt->bindValue(':internID', $internID, PDO::PARAM_STR);
                $stmt->bindValue(':InternPass', $InternPass, PDO::PARAM_STR);

                // Execute the statement
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Intern account added successfully!'; // Set success message in session
                } else {
                    $_SESSION['message'] = 'Error: Could not add intern account.'; // Set error message in session
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage(); // Set error message in session
            }
        }
    }
}

// Handle adding facilitator account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faciID']) && !isset($_POST['updateFaci']) && !isset($_POST['deleteFaci'])) {
    // Get and sanitize user input
    $faciID = trim($_POST['faciID']);
    $faciPass = trim($_POST['faciPass'] ?? '');

    // Validate input
    if (empty($faciID) || empty($faciPass)) {
        $_SESSION['message'] = 'Facilitator ID and password are required.';
    } else {
        // Enforce length and character requirements for the password
        if (strlen($faciPass) < 6) {
            $_SESSION['message'] = 'Password must be at least 6 characters long.';
        } else {
            // Prepare SQL query to insert data without hashing the password
            $sql = "INSERT INTO facacc (faciID, faciPass) VALUES (:faciID, :faciPass)";

            try {
                $stmt = $conn->prepare($sql);
                
                // Bind parameters
                $stmt->bindValue(':faciID', $faciID, PDO::PARAM_STR);
                $stmt->bindValue(':faciPass', $faciPass, PDO::PARAM_STR); // Use plain password

                // Execute the statement
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Facilitator account added successfully!'; // Set success message in session
                } else {
                    $_SESSION['message'] = 'Error: Could not add facilitator account.'; // Set error message in session
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage(); // Set error message in session
            }
        }
    }
}



// Fetch existing intern accounts for display
try {
    $sql = "SELECT * FROM intacc";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $internAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $internAccounts = []; // Handle error, you can log it if needed
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="Admin_style.css"> <!-- Link to your CSS -->
</head>
<body>
    <div id="header" class="header">
        <button class="logout-btn" onclick="logout()">
            <img src="image/logout.png" alt="Logout Icon" class="logout-icon"> | LOG OUT
        </button>

        <button class="modal-btn" onclick="openModal('myModal')"></button>
        <div class="overlay" id="overlay"></div>

        <!-- Modal Structure -->
        <div id="myModal" class="modal">
            <div class="modal-content">
            <span class="close" onclick="closeModal('myModal')">&times;</span>
            <h2>My Profile</h2>

                <form id="updateProfileForm" method="POST" action="test.php" enctype="multipart/form-data">
                    <!-- Display error messages if any -->
                    <?php if (!empty($errorMessages)): ?>
                        <div class="error-messages" style="color: red;">
                            <?php foreach ($errorMessages as $message): ?>
                                <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="newFirstname">New First Name:</label>
                        <input type="text" id="newFirstname" name="newFirstname" value="<?php echo htmlspecialchars($Firstname, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                            <label for="currentUpass">Current Password:</label>
                            <input type="password" id="currentUpass" name="currentUpass" placeholder="Enter current password" required>
                        </div>

                        <div class="form-group">
                            <label for="newUpass">New Password:</label>
                            <input type="password" id="newUpass" name="newUpass" placeholder="Enter new password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirmUpass">Confirm New Password:</label>
                            <input type="password" id="confirmUpass" name="confirmUpass" placeholder="Re-enter new password" required>
                        </div>


                    <div class="form-group">
                        <label for="newProfileImage">New Profile Image:</label>
                        <input type="file" id="newProfileImage" name="newProfileImage" accept="image/*">
                    </div>
                    
                    <input type="hidden" name="Uname" value="<?php echo htmlspecialchars($Uname, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

    <div id="sidebar" class="sidebar">
        <!-- Display admin profile image -->
        <div class="profile-image"> 
            <img src="uploads/<?php echo htmlspecialchars($admin_profile, ENT_QUOTES, 'UTF-8'); ?>" class="logo" alt="Admin Profile" >
        </div>

        <div id="detail">
            <div class="firstname"><?php echo htmlspecialchars($Firstname, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="roles"><?php echo htmlspecialchars($Roles, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <!-- Dashboard Links -->
        <a href="#" class="home-link" onclick="showContent('Dashboard')"><i class="fa fa-home"></i><span> Dashboard</span></a>
        <a href="#" onclick="showContent('Intern_profile')"><i class="fa fa-cog"></i><span> Intern Profiles</span></a>
        <a href="#" onclick="showContent('Intern_Account')"><i class="fa fa-info-circle"></i><span> Intern Logins</span></a>
        <a href="#" onclick="showContent('Facilitator_Account')"><i class="fa fa-phone"></i><span> Facilitator Logins</span></a>
        <a href="#" onclick="showContent('report')"><i class="fa fa-phone"></i><span> Report</span></a>
    </div>

    <div class="main-content" id="main-content">
        <div class="content-section active" id="Dashboard">
            <h1>Dashboard</h1>
            <div class="dashboard-cards">
                <div class="card course"><h2>Course & Section</h2><p>1 Course & Section</p></div>
                <div class="card shift"><h2>Intern’s Shift</h2><p>2 Interns' Shift</p></div>
                <div class="card intern"><h2>Intern</h2><p>3 Intern</p></div>
                <div class="card company"><h2>Company</h2><p>4 Company</p></div>
            </div>
            <div class="announcement-board">
                <h2>Announcement Board</h2>
                <div class="input-container">
                    <input type="text" placeholder="Enter your Announcement here" class="styled-input">
                </div>
                <button class="post-button">POST</button>
            </div>
        </div>
        
        <div class="content-section" id="Intern_profile">
            <h1>Intern Profile</h1>
            <h1 class="db_Details">Database here!</h1>
            </div>
                <div class="content-section" id="Intern_Account">
                    <h1>Intern Logins</h1>
                    <button class="intern_acc" onclick="openModal('InternAccModal')">Intern Accounts</button>

                            <!-- Intern Modal -->
                            <div id="InternAccModal" class="modal">
                                <div class="modal-content">
                                    <span class="close" onclick="closeModal('InternAccModal')">&times;</span>
                                    
                                    <h2>Add Intern Account</h2>
                                    <form id="addInterAccForm" method="POST" action="add_intern.php" onsubmit="return validateForm()">
                                        <div class="form-group">
                                            <label for="internID">Intern ID:</label>
                                            <input type="text" id="internID" name="internID" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="InternPass">Password:</label>
                                            <input type="password" id="InternPass" name="InternPass" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Display Existing Intern Accounts Outside the Modal -->
                            <h2>Existing Intern Accounts</h2>
                        <?php if ($internAccounts): ?>
                            <table>
                                <tr>
                                    <th>Intern ID</th>
                                    <th>Password</th>
                                    <th>Actions</th>
                                </tr>
                                <?php foreach ($internAccounts as $account): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($account['internID']); ?></td>
                                        <td><?php echo htmlspecialchars($account['InternPass']); ?></td> <!-- Use correct key here -->
                                        <td>
                                            <form method="POST" action="update.php" style="display:inline;">
                                                <input type="hidden" name="internID" value="<?php echo htmlspecialchars($account['internID']); ?>" />
                                                <input type="submit" value="Update" />
                                            </form>
                                            <form method="POST" action="delete.php" style="display:inline;">
                                                <input type="hidden" name="internID" value="<?php echo htmlspecialchars($account['internID']); ?>" />
                                                <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete this record?');" />
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <p>No intern accounts found.</p>
                        <?php endif; ?>
             </div>
            
        </div>      
                 

        
   
            <div class="content-section" id="Facilitator_Account">
                <h1>Facilitator Logins</h1>
                <button class="faci_acc" onclick="openModal('FaccAccModal')">Facilitator Accounts</button>
                <div id="FaccAccModal" class="modal" style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);height: 30%;">
                            <span class="close" onclick="closeModal('FaccAccModal')">&times;</span>
                            <h2>Add Facilitator Account</h2>
                            <form id="addFaccAccForm" method="POST">
                            <div class="form-group">
                                <label for="faciID">Facilitator ID:</label>
                                <input type="text" id="faciID" name="faciID" required>
                            </div>
                            <div class="form-group">
                                <label for="faciPass">Password:</label>
                                <input type="password" id="faciPass" name="faciPass" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                </div>

       

        <div class="content-section" id="report">
            <h1>Report</h1>
            <h1 class="db_Details">Database here!</h1>
        </div>
    
    
    
    </div>
    <script src="admin_script.js"></script>
</body>
</html>
