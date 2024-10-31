<?php
session_start(); // Start the session
require('Admin_connection.php'); // Include your database connection file

// Fetch the username from the session
$Uname = $_SESSION['Uname'];


try {
    // Prepare the SQL query using placeholders to prevent SQL injection
    $query = "SELECT Firstname, Roles, admin_profile, Uname, adminID FROM users WHERE Uname = :Uname"; // Include adminID
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
        $adminID = $user['adminID']; // Store adminID for later use
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
    $messages = []; // Array to hold any error messages

    // Fetch current user's password from the database
    $query = "SELECT Upass FROM users WHERE Uname = :Uname";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':Uname', $Uname, PDO::PARAM_STR);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the current password
    if ($currentUser && $oldUpass !== $currentUser['Upass']) { // No hashing, direct comparison
        $messages[] = "Current password is incorrect.";
    }

    // Validate new password if the current password is correct
    if (empty($messages)) {
        if ($newUpass !== $confirmUpass) {
            $messages[] = "Passwords do not match.";
        } elseif (strlen($newUpass) < 6) {
            $messages[] = "Password must be at least 6 characters long.";
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
            
            // Ensure the upload directory exists
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true); // Create directory if it doesn't exist
            }
            
            $dest_path = $uploadFileDir . $newFileName;

            // Move the file to the uploads directory
            if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                $messages[] = "Error moving the uploaded file.";
            }
        } else {
            $messages[] = "Invalid file type or file size too large.";
        }
    }

    // If there are no errors, update user info in the database
    if (empty($messages)) {
        try {
            // Build the query
            $query = "UPDATE users SET Firstname = :firstname";
            if (!empty($newUpass)) {
                $query .= ", Upass = :password"; // Include password only if not empty
            }
            if (!empty($newFileName)) {
                $query .= ", admin_profile = :profile"; // Include profile only if not empty
            }
            $query .= " WHERE Uname = :Uname"; // Finalize the WHERE clause
            
            $stmt = $conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':firstname', $newFirstname, PDO::PARAM_STR);
            if (!empty($newUpass)) {
                $stmt->bindParam(':password', $newUpass, PDO::PARAM_STR); // No hashing
            }
            if (!empty($newFileName)) {
                $stmt->bindParam(':profile', $newFileName, PDO::PARAM_STR);
            }
            $stmt->bindParam(':Uname', $Uname, PDO::PARAM_STR);
            
            // Execute the query
            $stmt->execute();
            
            // Redirect to the admin page or display success message
            header("Location: test.php"); // Adjust the redirect as needed
            exit();

        } catch (PDOException $e) {
            // Log the error message instead of echoing it
            error_log("Error updating user data: " . $e->getMessage());
            echo "There was an error updating your data. Please try again later.";
        }
    }
}



// Handle adding intern account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize user input
    $internID = trim($_POST['internID'] ?? '');
    $InternPass = trim($_POST['InternPass'] ?? '');

    // Validate input for creating an account
    if (empty($internID) || empty($InternPass)) {
        $_SESSION['message'] = 'Intern ID and password are required.';
    } else {
        // Optional: Check password length
        if (strlen($InternPass) < 6) {
            $_SESSION['message'] = 'Password must be at least 6 characters long.';
        } else {
            // Check if the internID already exists
            $checkQuery = "SELECT COUNT(*) FROM intacc WHERE internID = :internID";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':internID', $internID, PDO::PARAM_STR);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn();

            if ($exists > 0) {
                // Intern ID already exists
                $_SESSION['message'] = 'Intern ID already exists.';
            } else {
                // Prepare SQL query to insert data
                $sql = "INSERT INTO intacc (internID, Internpass, adminID) VALUES (:internID, :InternPass, :adminID)"; 

                try {
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':internID', $internID, PDO::PARAM_STR);
                    $stmt->bindValue(':InternPass', $InternPass, PDO::PARAM_STR);
                    $stmt->bindValue(':adminID', $adminID, PDO::PARAM_STR); 

                    if ($stmt->execute()) {
                        $_SESSION['message'] = 'Intern account added successfully!';
                    } else {
                        $_SESSION['message'] = 'Error: Could not add intern account.';
                    }
                } catch (PDOException $e) {
                    $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage();
                }
            }
        }
    }
}


// Handle deleting and updating intern account
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Check if internID is set for actions
    if (isset($_POST['internID'])) {
        // Get and sanitize user input
        $internID = trim($_POST['internID']);
        $InternPass = trim($_POST['InternPass'] ?? ''); // Use null coalescing operator to avoid undefined index
        $action = $_POST['action'] ?? '';

        // Validate input for update or delete actions
        if ($action === 'update') {
            // Validate internID and new password if provided
            if (empty($internID) || empty($InternPass)) {
                $_SESSION['message'] = 'Intern ID and new password are required for update.';
            } else {
                // Optional: Check password length
                if (strlen($InternPass) < 6) {
                    $_SESSION['message'] = 'Password must be at least 6 characters long.';
                } else {
                    // Prepare SQL query to update data
                    $sql = "UPDATE intacc SET InternPass = :InternPass WHERE internID = :internID AND adminID = :adminID"; 

                    try {
                        $stmt = $conn->prepare($sql);
                        $stmt->bindValue(':InternPass', $InternPass, PDO::PARAM_STR);
                        $stmt->bindValue(':internID', $internID, PDO::PARAM_STR);
                        $stmt->bindValue(':adminID', $adminID, PDO::PARAM_STR); // Bind adminID

                        if ($stmt->execute()) {
                            $_SESSION['message'] = 'Intern account updated successfully!';
                        } else {
                            $_SESSION['message'] = 'Error: Could not update intern account.';
                        }
                    } catch (PDOException $e) {
                        $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage();
                    }
                }
            }
        } elseif ($action === 'delete') {
            // Prepare SQL query to delete data
            $sql = "DELETE FROM intacc WHERE internID = :internID AND adminID = :adminID"; 

            try {
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':internID', $internID, PDO::PARAM_STR);
                $stmt->bindValue(':adminID', $adminID, PDO::PARAM_STR); // Bind adminID

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Intern account deleted successfully!';
                } else {
                    $_SESSION['$errorMessages'] = 'Error: Could not delete intern account.';
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage();
            }
        }
    } 
}



// Initialize a variable for the search ID
$searchInternID = '';

// Check if the request method is GET and the searchInternID is set
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['searchInternID'])) {
    $searchInternID = trim($_GET['searchInternID']);
    
    // Query to fetch intern accounts matching the search ID
    $sql = "SELECT * FROM intacc WHERE adminID = :adminID AND internID LIKE :internID"; 
    $stmt = $conn->prepare($sql);
    $likeInternID = '%' . $searchInternID . '%'; // Use LIKE for partial matching
    $stmt->bindParam(':adminID', $adminID, PDO::PARAM_STR);
    $stmt->bindParam(':internID', $likeInternID, PDO::PARAM_STR);
    $stmt->execute();
    $internAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if any accounts were found
    if (empty($internAccounts)) {
        $searchMessage = "No intern found with ID: " . htmlspecialchars($searchInternID);
    }
}


// Pagination settings
$recordsPerPage = 10; // Number of records to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $recordsPerPage; // Offset for SQL query

// Fetch total number of intern accounts for pagination
try {
    if (isset($adminID)) {
        $sql = "SELECT COUNT(*) FROM intacc WHERE adminID = :adminID"; 
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':adminID', $adminID, PDO::PARAM_STR);
        $stmt->execute();
        $totalRecords = $stmt->fetchColumn(); // Total number of records
        $totalPages = ceil($totalRecords / $recordsPerPage); // Total number of pages

        // Fetch the current page records
        $sql = "SELECT * FROM intacc WHERE adminID = :adminID LIMIT :limit OFFSET :offset"; 
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':adminID', $adminID, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT); // Set limit
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT); // Set offset
        $stmt->execute();
        $internAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['message'] = 'Admin ID is not set.';
        $internAccounts = [];
    }
    } catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['message'] = 'Error fetching intern accounts. Please try again later.';
    $internAccounts = [];
}








// Handle adding facilitator account

if (isset($_POST['submitFacilitator'])) {
    $faciID = $_POST['faciID'];
    $faciPass = $_POST['faciPass'];

   
    try {
        // Check if the faciID already exists
        $checkSql = "SELECT COUNT(*) FROM facacc WHERE faciID = :faciID";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':faciID', $faciID);
        $checkStmt->execute();

        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message'] = "Error: The Facilitator ID '$faciID' already exists. Please use a different ID.";
        } else {
            // Create the SQL query to insert into the facacc table
            $sql = "INSERT INTO facacc (faciID, faciPass, adminID) VALUES (:faciID, :faciPass, :adminID)";
            
            // Prepare and execute the statement
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':faciID', $faciID);
            $stmt->bindParam(':faciPass', $faciPass);
            $stmt->bindParam(':adminID', $adminID, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Facilitator account added successfully!";
            } else {
                $_SESSION['message'] = "Error: Could not add facilitator account.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }



}

// Handle deleting and updating facilitator account
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Check if faciID is set for actions
    if (isset($_POST['faciID'])) {
        // Get and sanitize user input
        $faciID = trim($_POST['faciID']);
        $faciPass = trim($_POST['faciPass'] ?? ''); // Use null coalescing operator to avoid undefined index
        $action = $_POST['action'] ?? '';

        // Validate input for update or delete actions
        if ($action === 'update') {
            // Validate faciID and new password if provided
            if (empty($faciID) || empty($faciPass)) {
                $_SESSION['message'] = 'Facilitator ID and new password are required for update.';
            } else {
                // Optional: Check password length
                if (strlen($faciPass) < 6) {
                    $_SESSION['message'] = 'Password must be at least 6 characters long.';
                } else {
                    // Prepare SQL query to update data
                    $sql = "UPDATE facacc SET faciPass = :faciPass WHERE faciID = :faciID AND adminID = :adminID";

                    try {
                        $stmt = $conn->prepare($sql);
                        $stmt->bindValue(':faciPass', $faciPass, PDO::PARAM_STR);
                        $stmt->bindValue(':faciID', $faciID, PDO::PARAM_STR);
                        $stmt->bindValue(':adminID', $adminID, PDO::PARAM_STR); // Bind adminID

                        if ($stmt->execute()) {
                            $_SESSION['message'] = 'Facilitator account updated successfully!';
                        } else {
                            $_SESSION['message'] = 'Error: Could not update facilitator account.';
                        }
                    } catch (PDOException $e) {
                        $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage();
                    }
                }
            }
        } elseif ($action === 'delete') {
            // Prepare SQL query to delete data
            $sql = "DELETE FROM facacc WHERE faciID = :faciID AND adminID = :adminID";

            try {
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':faciID', $faciID, PDO::PARAM_STR);
                $stmt->bindValue(':adminID', $adminID, PDO::PARAM_STR); // Bind adminID

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Facilitator account deleted successfully!';
                } else {
                    $_SESSION['$errorMessages'] = 'Error: Could not delete facilitator account.';
                }
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Error preparing statement: ' . $e->getMessage();
            }
        }
    }
}

// Initialize a variable for the search ID
$searchFaciID = '';


// Check if the request method is GET and the searchFaciID is set
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['searchFaciID'])) {
    $searchFaciID = trim($_GET['searchFaciID']);
}

// Query to fetch facilitator accounts matching the search ID and adminID
$sql = "SELECT * FROM facacc WHERE adminID = :adminID AND faciID LIKE :faciID";
$stmt = $conn->prepare($sql);
$likeFaciID = '%' . $searchFaciID . '%'; // Use LIKE for partial matching
$stmt->bindParam(':adminID', $adminID, PDO::PARAM_STR); // Make sure adminID is used
$stmt->bindParam(':faciID', $likeFaciID, PDO::PARAM_STR);
$stmt->execute();
$faccAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);



//for counting facilititator account under a adminID
$sql = "SELECT COUNT(*) AS totalAccounts FROM facacc WHERE adminID = :adminID";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':adminID', $adminID, PDO::PARAM_INT);
$stmt->execute();

$totalAccounts = $stmt->fetchColumn();




// Check if form is submitted in posting a announcement 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the posted values
    if (isset($_POST['title']) && isset($_POST['announcement'])) {
        $title = trim($_POST['title']);
        $announcement = trim($_POST['announcement']);

        // Ensure the file upload is handled correctly
        if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
            // Process file upload
            $fileTmpPath = $_FILES['fileUpload']['tmp_name'];
            $fileName = $_FILES['fileUpload']['name'];
            $fileSize = $_FILES['fileUpload']['size'];
            $fileType = $_FILES['fileUpload']['type'];

            // Define the path where the file will be uploaded
            $uploadFileDir = './uploaded_files/';
            $dest_path = $uploadFileDir . $fileName;

            // Move the file to the desired directory
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // File is successfully uploaded
                // Prepare the SQL statement
                $sql = "INSERT INTO announcements (title, imagePath, content, adminID) VALUES (:title, :imagePath, :content, :adminID)";
                $stmt = $conn->prepare($sql);

                // Bind the parameters
                $stmt->bindValue(':title', $title, PDO::PARAM_STR);
                $stmt->bindValue(':imagePath', $dest_path, PDO::PARAM_STR); // Store the file path in the database
                $stmt->bindValue(':content', $announcement, PDO::PARAM_STR);
                $stmt->bindValue(':adminID', $adminID, PDO::PARAM_INT); // Replace with the actual admin ID

                // Execute the statement
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Announcement posted successfully!';
                   
                } else {
                    $_SESSION['message'] = "Error posting announcement.";
                }
            } else {
                $_SESSION['message'] = "Error uploading the file.";
            }
        } else {
            $_SESSION['message'] = "No file uploaded or there was an upload error.";
        }
    }
}




// Fetch announcements for the current admin
$sql = "SELECT title,announcementID, imagePath, content FROM announcements WHERE adminID = :adminID";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':adminID', $adminID, PDO::PARAM_INT); // Use your actual admin ID
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);


// do not change anything above----------------------------------------------------------------

// Handle the deletion for annoucement 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcementID'])) {
    $announcementID = $_POST['announcementID'];

    // Prepare the SQL statement to delete the announcement
    $stmt = $conn->prepare("DELETE FROM announcements WHERE announcementID = :announcementID");
    $stmt->bindParam(':announcementID', $announcementID);

    try {
        if ($stmt->execute()) {
            // Set a success message if the deletion was successful
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page

            $_SESSION['message'] = "Announcement deleted successfully.";
            exit; // Prevent further script execution

        } else {
            $_SESSION['message'] = "Failed to delete the announcement.";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
} else {
    $_SESSION['message'] = "Invalid request.";

}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="css/Admin_style.css"> <!-- Link to your CSS -->
</head>
<body>
<?php
    // Display any messages
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']); // Clear the message after displaying
        ?>
        <div class="alert alert-success" role="alert" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; background-color: #f2b25c; color: white; padding: 15px; border-radius: 5px;" id="alertBox">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <script type="text/javascript">
            // Hide the alert after 5 seconds
            setTimeout(function() {
                var alertBox = document.getElementById('alertBox');
                if (alertBox) {
                    alertBox.style.display = 'none';
                }
            }, 5000);
        </script>
        <?php
    }
?>

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
                        <?php if (!empty($messages)): ?>
                            <div class="error-messages" style="color: red;">
                                <?php foreach ($messages as $message): ?>
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
            <div class="card intern"><h2>Intern Account</h2>
                <strong><?php echo count($internAccounts); ?></strong>
            </div>
            <div class="card company"><h2>Facilitator Account</h2>
                <strong> <?php echo $totalAccounts; ?></strong>
            </div>
        </div>
        <div class="announcement-board">
        <img src="image/announce.png" alt="Announcement Image" class="img">
        <div class="form-container">
            <h2>Announcement Board</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                <label for="title"  class="styled-inputann">Title:</label>
                <input type="text" id="title" name="title" class="styled-input"required>
                </div>
                <div class="form-group">
                <label for="announcement" class="styled-inputann">Announcement:</label>
                <textarea id="announcement" name="announcement" class="styled-input" required></textarea>
                </div>
                <div class="form-group">
                <label for="fileUpload"  class="styled-inputannup" >Upload File:</label>
                <input type="file" id="fileUpload" name="fileUpload" required>
                </div>
                <button type="submit" class="post-button">Submit</button>
            </form>
        </div>
        <div class="announcement-slider">
            <div class="slider-container">
                <?php if ($announcements): ?>
                    <?php foreach ($announcements as $index => $announcement): ?>
                        <div class="announcement-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>

                            <?php if ($announcement['imagePath']): ?>
                                <?php
                                // Get the file extension
                                $filePath = htmlspecialchars($announcement['imagePath']);
                                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                                ?>
                                <div class="Announcement-Image">
                                    <?php if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Announcement Image" class="ann_img">
                                    <?php elseif (strtolower($fileExtension) === 'pdf'): ?>
                                        <?php
                                        // Extract the file name and construct the file path
                                        $fileName = basename($filePath);
                                        $pdfPath = "http://localhost/Task_HOuse/Task_House/uploaded_files/" . rawurlencode($fileName);
                                        ?>
                                        <a href="<?php echo $pdfPath; ?>" target="_blank" class="pdf-link">View PDF</a>
                                    <?php else: ?>
                                        <p>Unsupported file type.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                                <!-- Delete Button Form -->
                                <form enctype="multipart/form-data" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                        <input type="hidden" name="announcementID" value="<?php echo htmlspecialchars($announcement['announcementID']); ?>">
                                        <button type="submit" class="delete-button">Delete</button>
                                </form>
                        </div>
                    
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No announcements found.</p>
                <?php endif; ?>
                
                <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
                <button class="next" onclick="moveSlide(1)">&#10095;</button>
            </div>
        </div>

</div>


</div>



        
<div class="content-section" id="Intern_Account">
                        <h1>Intern Logins</h1>
                        <button class="intern_acc" onclick="openModal('InternAccModal')">Intern Accounts</button>

                        <!-- Intern Modal -->
                        <div id="InternAccModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal('InternAccModal')">&times;</span>

                                <h2>Add Intern Account</h2>
                                <form id="addInterAccForm" method="POST" action="" onsubmit="return validateForm()">
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

                        <!-- Search Form Positioned in Upper Right of the Table -->
                        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                            <form method="GET" action="">
                                <input type="text" name="searchInternID" value="<?php echo htmlspecialchars($searchInternID); ?>" placeholder="Search Intern ID" class="search-input" />
                                <button type="submit" class="search-button">Search</button>
                            </form>
                        </div>

                        <!-- Message Display for Search Results -->
                        <?php if ($searchInternID): ?>
                            <p>Search Results for: <strong><?php echo htmlspecialchars($searchInternID); ?></strong></p>
                        <?php endif; ?>

                        <!-- Intern Accounts Table -->
                        <?php if (!empty($internAccounts)): ?>
                            <div class="table-container"> <!-- Added container for scrolling -->

                            <table class="intern-accounts-table">
                                <tr>
                                    <th class="table-header">Intern ID</th>
                                    <th class="table-header">Current Password</th>
                                    <th class="table-header" style="padding-left: 30%;">Actions</th>
                                </tr>

                                <?php 
                                // To store the filtered and non-filtered accounts
                                $highlightedRow = [];
                                $otherRows = [];

                                foreach ($internAccounts as $account): 
                                    if (isset($account['internID']) && strpos($account['internID'], $searchInternID) !== false) {
                                        $highlightedRow[] = $account; 
                                    } else {
                                        $otherRows[] = $account; 
                                    }
                                endforeach; 

                                // Merge highlighted row(s) with other rows
                                $sortedAccounts = array_merge($highlightedRow, $otherRows);
                                foreach ($sortedAccounts as $account): ?>
                                    <tr class="<?php echo isset($account['internID']) && strpos($account['internID'], $searchInternID) !== false ? 'highlight' : ''; ?>">
                                        <td class="table-data"><?php echo isset($account['internID']) ? htmlspecialchars($account['internID']) : 'N/A'; ?></td>
                                        <td class="table-data"><?php echo isset($account['InternPass']) ? htmlspecialchars($account['InternPass']) : 'N/A'; ?></td>
                                        <td class="table-actions">
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="internID" value="<?php echo isset($account['internID']) ? htmlspecialchars($account['internID']) : ''; ?>" />
                                                <input type="password" name="InternPass" class="password-input" placeholder="New Password" />
                                                <button type="submit" name="action" value="update" class="update-button">Update</button>
                                                <button type="submit" name="action" value="delete" class="delete-button" onclick="return confirm('Are you sure you want to delete this record?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                            </div>

                        <?php else: ?>
                            <p>No intern accounts found.</p>
                        <?php endif; ?>

</div>
            
           
        
   
                <div class="content-section" id="Facilitator_Account">
                        <h1>Facilitator Logins</h1>
                        <button class="faci_acc" onclick="openModal('FaccAccModal')">Facilitator Accounts</button>

                      <!-- Facilitator Modal -->
                      <div id="FaccAccModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal('FaccAccModal')">&times;</span>
                                <h2>Add Facilitator Account</h2>
                                <form id="addFaccAccForm" method="POST" action="">
                                    <div class="form-group">
                                        <label for="faciID">Facilitator ID:</label>
                                        <input type="text" id="faciID" name="faciID" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="faciPass">Password:</label>
                                        <input type="password" id="faciPass" name="faciPass" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" name="submitFacilitator">Submit</button>
                                </form>
                                
                            </div>
                        </div>

                                                                            <!-- Display Existing Facilitator Accounts Outside the Modal -->
                        <h2>Existing Facilitator Accounts</h2>

                        <!-- Search Form Positioned in Upper Right of the Table -->
                        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                            <form method="GET" action="">
                            <input type="text" name="searchFaciID" value="<?php echo htmlspecialchars($searchFaciID); ?>" placeholder="Search Facilitator ID" class="search-input" />
                            <button type="submit" class="search-button">Search</button>
                            </form>
                        </div>

                        <!-- Message Display for Search Results -->
                        <?php if ($searchFaciID): ?>
                            <p>Search Results for: <strong><?php echo htmlspecialchars($searchFaciID); ?></strong></p>
                        <?php endif; ?>

                        <!-- Facilitator Accounts Table -->
                        <?php if (!empty($faccAccounts)): ?>
                            <div class="table-container"> <!-- Added container for scrolling -->

                                        <table class="intern-accounts-table"> <!-- Use the same class as intern accounts -->
                                            <tr>
                                                <th class="table-header">Facilitator ID</th>
                                                <th class="table-header">Current Password</th>
                                                <th class="table-header" style="padding-left: 30%;">Actions</th>
                                            </tr>

                                            <?php 
                                            // To store the filtered and non-filtered accounts
                                            $highlightedRow = [];
                                            $otherRows = [];

                                            foreach ($faccAccounts as $account): 
                                                if (isset($account['faciID']) && strpos($account['faciID'], $searchFaciID) !== false) {
                                                    $highlightedRow[] = $account; 
                                                } else {
                                                    $otherRows[] = $account; 
                                                }
                                            endforeach; 

                                            // Merge highlighted row(s) with other rows
                                            $sortedAccounts = array_merge($highlightedRow, $otherRows);
                                            foreach ($sortedAccounts as $account): ?>
                                                <tr class="<?php echo isset($account['faciID']) && strpos($account['faciID'], $searchFaciID) !== false ? 'highlight' : ''; ?>">
                                                    <td class="table-data"><?php echo isset($account['faciID']) ? htmlspecialchars($account['faciID']) : 'N/A'; ?></td>
                                                    <td class="table-data"><?php echo isset($account['faciPass']) ? htmlspecialchars($account['faciPass']) : 'N/A'; ?></td>
                                                    <td class="table-actions">
                                                        <form method="POST" action="" style="display:inline;">
                                                            <input type="hidden" name="faciID" value="<?php echo isset($account['faciID']) ? htmlspecialchars($account['faciID']) : ''; ?>" />
                                                            <input type="password" name="faciPass" class="password-input" placeholder="New Password" />
                                                            <button type="submit" name="action" value="update" class="update-button">Update</button>
                                                            <button type="submit" name="action" value="delete" class="delete-button" onclick="return confirm('Are you sure you want to delete this record?');">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>
                                        </div>

                        <?php else: ?>
                            <p>No facilitator accounts found.</p>
                        <?php endif; ?>

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
