<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Start the session
require('db_Taskhouse/Admin_connection.php');

// Check if 'Uname' (faciID) is set in the session
if (isset($_SESSION['Uname'])) {
    $faciID = htmlspecialchars($_SESSION['Uname']); // Safely display the session value
} else {
    // Redirect to login page if session not set
    header("Location: login.php");
    exit();
}

// Start Kyle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['faci_image'])) {
    try {
        $uploadDir = 'uploaded_files/';
        
        // Check if the upload directory exists, if not, create it
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['faci_image'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate the file type
        if (!in_array($fileExtension, $allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        // Create a unique file name
        $fileName = 'faci_' . $_SESSION['Uname'] . '_' . time() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Update the database with the new image path
            $sql = "UPDATE facacc SET faci_image = :faci_image WHERE faciID = :faciID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':faci_image', $fileName);
            $stmt->bindParam(':faciID', $_SESSION['Uname']);
            
            // Execute the statement and check for success
            if ($stmt->execute()) {
                $_SESSION['upload_status'] = 'success'; // Set session variable for success
            } else {
                throw new Exception('Database update failed');
            }
        } else {
            throw new Exception('Failed to move uploaded file');
        }
    } catch (Exception $e) {
        $_SESSION['upload_status'] = 'error'; // Set session variable for error
        $_SESSION['upload_message'] = $e->getMessage(); // Store error message
    }
    
    // Redirect back to the original page
    header("Location: faci_facilitator.php");
    exit; // Ensure no further output is sent
}


// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_credentials'])) {
    $faciID = $_SESSION['Uname'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    try {
        // Verify current password
        $sql = "SELECT faciPass FROM facacc WHERE faciID = :faciID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':faciID', $faciID);
        $stmt->execute();
        $storedPassword = $stmt->fetchColumn();

        if ($currentPassword !== $storedPassword) {
            $_SESSION['error_message'] = 'Current password is incorrect';
        } elseif (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = 'New password must be at least 6 characters long';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'New passwords do not match';
        } else {
            // Update password
            $updateSql = "UPDATE facacc SET faciPass = :password WHERE faciID = :faciID";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':password', $newPassword);
            $updateStmt->bindParam(':faciID', $faciID);
            
            if ($updateStmt->execute()) {
                $_SESSION['success_message'] = 'Password updated successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to update password';
            }
        }
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
// End Kyle

// Display messages if they exist (add this near the top of your HTML)
if (isset($_SESSION['error_message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['error_message']) . "');</script>";
    unset($_SESSION['error_message']); // Clear the message after displaying
}

if (isset($_SESSION['success_message'])) {
    echo "<script>
        alert('" . htmlspecialchars($_SESSION['success_message']) . "');
        document.getElementById('credentialsModal').style.display = 'none';
    </script>";
    unset($_SESSION['success_message']); // Clear the message after displaying
}

$faciID = $_SESSION['Uname']; // Get the logged-in faciID

// Fetch the necessary data from the database
$sql = "SELECT id, internID, adminID, login_time, break_time, back_to_work_time, task, logout_time, status 
        FROM time_logs 
        WHERE faciID = :faciID AND status = 'pending' 
        ORDER BY login_time ASC"; 

$stmt = $conn->prepare($sql);
$stmt->bindParam(':faciID', $faciID);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count the number of pending approval requests
$approvalCount = count($logs);


// Handle form submission for approval, decline, and update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (isset($_POST['approveBtn'])) {
    // Sanitize input to avoid malicious data
    $internID = htmlspecialchars($_POST['internID']);
    $id = htmlspecialchars($_POST['id']); // Get the unique log ID
    
    // Query to fetch the current record for the specified intern and log ID
    $query = "SELECT login_time, break_time, back_to_work_time, task, logout_time FROM time_logs WHERE internID = :internID AND id = :id";
    
    try {
        // Fetch the record to check if any field is empty
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Fetch the data for the specific log entry
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if any of the necessary fields are empty
        if (empty($log['login_time']) || empty($log['break_time']) || empty($log['back_to_work_time']) || empty($log['task']) || empty($log['logout_time'])) {
            // Set error message in the session
            $_SESSION['alertMessage'] = "All time fields (Login Time, Break Time, Back to Work Time, Task, Logout Time) must be filled in before approving.";
            $_SESSION['alertType'] = 'error'; // Set alert type as error
        } else {
            // Define your update query to set the status to 'approved' only for the specific row (log_id)
            $sql = "UPDATE time_logs 
                    SET status = 'Approved' 
                    WHERE internID = :internID 
                    AND id = :id 
                    AND status = 'pending'"; // Ensure that only 'pending' logs are updated for the correct row
            
            // Prepare and execute the query using PDO
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Bind the log_id parameter
            $stmt->execute();
            
            // Set success message in the session
            $_SESSION['alertMessage'] = "Status updated to 'approved' for Intern ID: $internID";
            $_SESSION['alertType'] = 'success';  // Set type as 'success'
        }
        
        // Redirect to avoid re-submitting the form on page refresh (Post-Redirect-Get)
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        // Set error message in the session if an exception occurs
        $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
        $_SESSION['alertType'] = 'error';  // Set type as 'error'
    }
}
    

   // Handle Decline logic

  // Handle Decline logic
    if (isset($_POST['submitDeclineReason'])) {
        // Check if the required form fields are set
        if (isset($_POST['internID'], $_POST['id'], $_POST['decline_reason'])) {
            // Sanitize form data
            $internID = htmlspecialchars($_POST['internID']);
            $id = htmlspecialchars($_POST['id']);
            $decline_reason = htmlspecialchars($_POST['decline_reason']);

           

            // Check the current status before updating
            $stmt = $conn->prepare("SELECT status FROM time_logs WHERE internID = :internID AND id = :id");
            $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
               // echo "Current Status: " . htmlspecialchars($row['status']) . "<br>";
            } else {
               // echo "No row found with the specified internID and ID.<br>";
            }
            try {
                // Debugging: Print the SQL query before execution
                $sql = "UPDATE time_logs 
                        SET status = 'Declined', 
                            task = :decline_reason 
                        WHERE internID = :internID 
                        AND id = :id 
                        AND LOWER(status) = LOWER('Pending')";
               // echo "SQL Query: " . $sql . "<br>";
                $stmt = $conn->prepare($sql);
            
                // Bind the parameters
                $stmt->bindParam(':decline_reason', $decline_reason, PDO::PARAM_STR);
                $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
                // Execute the query
                // Execute your SQL query
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    // Set the success message if rows are affected
                    $_SESSION['alertMessage'] = "The status has been updated to 'Decline' and task has been updated successfully.<br>";
                    $_SESSION['alertType'] = "success"; // Success type for alert
                     // Redirect to avoid re-submitting the form on page refresh (Post-Redirect-Get)
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
                } else {
                    // If no rows were updated, you can display an error or log it
                    $_SESSION['alertMessage'] = "No rows were updated. Please check if the status was 'Pending' and data matches.<br>";
                    $_SESSION['alertType'] = "error"; // Error type for alert
                }
            } else {
                // If the query fails to execute, set the error message
                $_SESSION['alertMessage'] = "Failed to execute query. Error info: " . implode(", ", $stmt->errorInfo()) . "<br>";
                $_SESSION['alertType'] = "error"; // Error type for alert
            }

            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            
        } else {
            echo "Required form data is missing.<br>";
        }
    }


    

    
    
   // Handle Update logic (if needed for the form/modal)
// Handle Update logic (if needed for the form/modal)
if (isset($_POST['updateBtn'])) {
    // Sanitize input to avoid malicious data
    $internID = htmlspecialchars($_POST['internID']);
    $id = htmlspecialchars($_POST['id']); // Get the log ID

    // Sanitize the updated fields
    $updated_login_time = htmlspecialchars($_POST['login_time']);
    $updated_task = htmlspecialchars($_POST['task']);
    $updated_break_time = htmlspecialchars($_POST['break_time']);
    $updated_back_to_work_time = htmlspecialchars($_POST['back_to_work_time']);
    $updated_logout_time = htmlspecialchars($_POST['logout_time']);

    // Check if any required fields are empty before proceeding
    if (empty($updated_login_time) || empty($updated_task) || empty($updated_break_time) || empty($updated_back_to_work_time) || empty($updated_logout_time)) {
        // Set error message in the session
        $_SESSION['alertMessage'] = "All fields (Login Time, Task, Break Time, Back to Work Time, Logout Time) must be filled in before updating.";
        $_SESSION['alertType'] = 'error'; // Set alert type as error

        // Redirect to the same page to show the error message
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Set status to 'Approved' upon update
    $status = 'Approved';

    // SQL query for updating values, including status
    $sql = "UPDATE time_logs 
            SET login_time = :login_time, 
                break_time = :break_time,
                back_to_work_time = :back_to_work_time,
                task = :task,
                logout_time = :logout_time,
                status = :status
            WHERE internID = :internID 
            AND id = :id"; 

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
        $stmt->bindParam(':login_time', $updated_login_time, PDO::PARAM_STR);
        $stmt->bindParam(':break_time', $updated_break_time, PDO::PARAM_STR);
        $stmt->bindParam(':back_to_work_time', $updated_back_to_work_time, PDO::PARAM_STR);
        $stmt->bindParam(':task', $updated_task, PDO::PARAM_STR);
        $stmt->bindParam(':logout_time', $updated_logout_time, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();

        // Set success message
        $_SESSION['alertMessage'] = "Log updated for Intern ID: $internID";
        $_SESSION['alertType'] = 'success'; 

        // Redirect after the update
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
        $_SESSION['alertType'] = 'error';
    }
}

}




// Query to fetch approved logs for the specific faciID
$sql = "SELECT * FROM time_logs WHERE status IN ('approved', 'declined') AND faciID = :faciID";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT); // Bind the faciID
    $stmt->execute();
    $approvedLogs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as an associative array

    $approvedCount = count($approvedLogs);

} catch (PDOException $e) {
    // Handle error
    $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
    $_SESSION['alertType'] = 'error';
}







// Assuming you have a valid queryfor availability statud


$query = "SELECT time_logs.*, 
profile_information.first_name,
CASE
    WHEN time_logs.status = 'Declined' THEN 'Declined'
    WHEN time_logs.logout_time IS NOT NULL THEN 'Logged Out'
    WHEN time_logs.break_time IS NOT NULL AND time_logs.back_to_work_time IS NULL THEN 'On Break'
    WHEN time_logs.login_time IS NOT NULL 
         AND time_logs.back_to_work_time IS NULL 
         AND time_logs.logout_time IS NULL THEN 'Active Now'
    WHEN time_logs.back_to_work_time IS NOT NULL THEN 'Active Now'
    ELSE 'Unknown'
END AS status
FROM time_logs
JOIN profile_information ON time_logs.faciID = profile_information.faciID
WHERE time_logs.faciID = :faciID
  AND time_logs.status != 'Declined'  
  AND DATE(time_logs.login_time) = CURDATE()  -- Only show today's logs
  AND (
      time_logs.logout_time IS NULL  -- Only show if not logged out
      OR DATE(time_logs.logout_time) = CURDATE()
  )
ORDER BY time_logs.login_time DESC  -- Get the most recent log first
LIMIT 1;  -- Only get the most recent status for each intern

// Prepare the query
$stmt = $conn->prepare($query);

// Bind the parameter
$stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);

try {
    // Execute the query
    $stmt->execute();
    
    // Fetch all the results
    $interns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are results
    if (!empty($interns)) {
        // Process the results
        foreach ($interns as $intern) {
           
        }
    } else {
       
    }
    
} catch (PDOException $e) {
    // Handle any error that occurs during query execution
   // echo "<p>Error: " . $e->getMessage() . "</p>";
}



// Prepare and execute the query to get the count of active interns
$stmt = $conn->prepare($query);
$stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);
$stmt->execute();
$interns = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all interns' details
$activeInternCount = count($interns);  // Get the count of active interns





// search button in intern report nga content section
// Initialize search variables with empty strings or values from POST request
$searchInternID = isset($_POST['internID']) ? $_POST['internID'] : '';
$searchStatus = isset($_POST['status']) ? $_POST['status'] : '';

// Modify SQL query to filter based on search criteria
$sql = "SELECT * FROM time_logs WHERE faciID = :faciID AND status IN ('approved', 'declined')";

if (!empty($searchInternID)) {
    $sql .= " AND internID LIKE :internID";
}
if (!empty($searchStatus)) {
    $sql .= " AND status = :status";
}

try {
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);

    if (!empty($searchInternID)) {
        $searchInternID = "{$searchInternID}"; // Use wildcards for LIKE search
        $stmt->bindParam(':internID', $searchInternID, PDO::PARAM_STR);
    }

    if (!empty($searchStatus)) {
        $stmt->bindParam(':status', $searchStatus, PDO::PARAM_STR);
    }

    $stmt->execute();
    $approvedLogs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as an associative array
    $logsCount = count($approvedLogs);

} catch (PDOException $e) {
    // Handle error
    $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
    $_SESSION['alertType'] = 'error';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hr/Managers</title>
    <link rel="stylesheet" href="css/faci_styles.css">
</head>
<body>
    <!-- Alert Message Box - This will be displayed after form submission -->
    <?php
  if (isset($_SESSION['alertMessage'])) {
      $alertType = $_SESSION['alertType'];
      echo "<div class='alert alert-$alertType'>" . $_SESSION['alertMessage'] . "</div>";
      
      // Clear the message after displaying it
      unset($_SESSION['alertMessage']);
      unset($_SESSION['alertType']);
  }
?>


    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar hide-content">
            <div class="user-info">
            <img src="<?php 
                    $sql = "SELECT faci_image FROM facacc WHERE faciID = :faciID";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':faciID', $_SESSION['Uname']);
                    $stmt->execute();
                    $faciImage = $stmt->fetchColumn();
                    echo $faciImage ? 'uploaded_files/' . htmlspecialchars($faciImage) : 'image/USER_ICON.png';
                ?>" alt="Toggle Sidebar" class="user-icon">
                <div class="user-details">
                    <p class="user-name">Uname: <?php echo $faciID; ?></p>
                    <p class="role">Intern Facilitator</p>
                </div>

            </div>
            <nav class="navigation">
                <a href="#" class="home-link" onclick="showContent('dashboard')">
                    <i class="fa fa-home"></i><span> Dashboard</span>
                </a>
                <a href="#" onclick="showContent('requests')">
                    <i class="fa fa-cog"></i><span> Requests</span>
                </a>
                <a href="#" onclick="showContent('report')">
                    <i class="fa fa-cog"></i><span> Intern Reports</span>
                </a>
                
            </nav>
        </div>
        
        <!-- Header -->
        <div class="header" id="header">
            
        <button onclick="openCredentialsModal()" class="settings-btn">
                    <img src="image/USER_ICON.png" alt="Settings" class="settings-icon">
                </button>
            <button class="logout-btn" onclick="logout()">
                <img src="image/logout.png" alt="logout icon" class="logout-icon">
                | LOG OUT
            </button>
        </div>
    </div>
<!-- Start Kyle -->
    <div id="credentialsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeCredentialsModal()">&times;</span>
        <h2>Update Password</h2>

        <!-- Add image upload section -->
        <div class="image-upload-container">
        <img id="imagePreview" src="<?php 
            $sql = "SELECT faci_image FROM facacc WHERE faciID = :faciID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':faciID', $_SESSION['Uname']);
            $stmt->execute();
            $faciImage = $stmt->fetchColumn();
            echo $faciImage ? 'uploaded_files/' . htmlspecialchars($faciImage) : 'image/USER_ICON.png';
        ?>" alt="Profile Preview">
        <form id="imageForm" method="POST" enctype="multipart/form-data">
            <input type="file" id="profileImageInput" name="faci_image" accept="image/*" style="display: none;" onchange="this.form.submit()">
            <button type="button" class="choose-image-btn" onclick="document.getElementById('profileImageInput').click()">
                Choose Image
            </button>
        </form>
    </div>

        <form id="credentialsForm" method="POST">
            <div class="form-group">
                <label>Facilitator Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['Uname']); ?>" readonly 
                    style="background-color: #f5f5f5; border: 1px solid #ddd;">
            </div>
            <div class="form-group">
                <label>Current Password:</label>
                <input type="password" name="currentPassword" required>
            </div>
            <div class="form-group">
                <label>New Password:</label>
                <input type="password" name="newPassword" required>
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirmPassword" required>
            </div>
            <button type="submit" name="update_credentials">Update Password</button>
        </form>
    </div>
</div>
<!-- End Kyle-->

    <!-- Main Content -->
    <div class="content-section" id="dashboard">
    <div class="main-content">
    <div class="announcement-board">
    <h2>ANNOUNCEMENT BOARD</h2>
    <img src="image/announce.gif" alt="Announcement Image" class="anno-img">

    <?php
        // Fetch the adminID for the current faciID
        $sql_admin = "SELECT adminID FROM facacc WHERE faciID = :faciID";
        $stmt_admin = $conn->prepare($sql_admin);
        $stmt_admin->bindValue(':faciID', $faciID, PDO::PARAM_STR);

        if ($stmt_admin->execute()) {
            $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                $adminID = $admin['adminID'];

                // Fetch announcements and corresponding faciID from the facacc table
                $sql = "SELECT a.*, f.faciID
                        FROM announcements a
                        INNER JOIN facacc f ON a.adminID = f.adminID
                        WHERE a.adminID = :adminID";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':adminID', $adminID, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Check if announcements exist
                    if ($announcements) {
                        // Slider structure
                        echo '<div class="announcement-slider">';
                        echo '<div class="slider-container">';

                        // Loop through the announcements and display them
                        foreach ($announcements as $index => $announcement) {
                            $filePath = htmlspecialchars($announcement['imagePath']);
                            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                            $activeClass = ($index === 0) ? 'active' : ''; // First item will be active

                            // Each announcement item
                            echo '<div class="announcement-item ' . $activeClass . '">';
                            echo '<h3>' . htmlspecialchars($announcement['title']) . '</h3>';
                            echo '<p class="announcement-details">' . nl2br(htmlspecialchars($announcement['content'])) . '</p>';

                            // Display image or PDF link based on file type
                            echo '<div class="Announcement-Image">';
                            if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])) {
                                echo '<img src="' . $filePath . '" alt="Announcement Image" class="ann_img" style="max-width: 100%; height: auto;">';
                            } elseif (strtolower($fileExtension) === 'pdf') {
                                $fileName = basename($filePath);
                                $pdfPath = "/uploaded_files/" . rawurlencode($fileName);
                                echo '<a href="' . $pdfPath . '" target="_blank" class="pdf-link">View PDF</a>';
                            } else {
                                echo '<p>Unsupported file type.</p>';
                            }
                            echo '</div>'; // Close .Announcement-Image
                            echo '</div>'; // Close .announcement-item
                        }

                        echo '</div>'; // Close .slider-container

                        // Navigation buttons
                        echo '<button class="prev" onclick="moveSlide(-1)">&#10094;</button>';
                        echo '<button class="next" onclick="moveSlide(1)">&#10095;</button>';
                        echo '</div>'; // Close .announcement-slider
                    } else {
                        echo '<p>No announcements available.</p>';
                    }
                } else {
                    echo '<p>Error fetching announcements.</p>';
                }
            } else {
                echo '<p>Admin information not found for this facilitator.</p>';
            }
        } else {
            echo '<p>Error fetching admin information.</p>';
        }
    ?>

</div>

    </div>
    

    
    
    
        <div class="dash-content" >
        <div class="dashboard">
            <h2>Dashboard</h2>
            <div class="card red">
            <h3>ACTIVE INTERN'S</h3>
            <p><?php echo $activeInternCount; ?> Active Intern's</p>
        </div>
        <div class="card orange">
            <h3>APPROVAL REQUESTS</h3>
            <p><?php echo $approvalCount; ?> Pending</p>
        </div>
            <div class="card green">
                <h3>Requests</h3>
                <p><?php echo $approvedCount; ?> Approved</p>
            </div>
        </div>
        <div class="intern-status-dashboard">
    <h2>Availability Status</h2>
    <?php
    $displayedInternIDs = []; // Track displayed internIDs
    if (!empty($interns)): ?>
        <table class="intern-status-table">
            <thead>
                <tr>
                    <th>Intern ID</th>
                    <th>Intern Name</th>
                    <th>Status</th>
                    <th>Profile Image</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $hasActiveInterns = false; // Track if there are active interns
                foreach ($interns as $intern):
                    // Skip this intern if the internID has already been displayed
                    if (in_array($intern['internID'], $displayedInternIDs)) {
                        continue;
                    }
                    $displayedInternIDs[] = $intern['internID']; // Mark internID as displayed
                    $hasActiveInterns = true; // Mark that at least one intern is active today
                ?>
                    <tr class="<?php echo strtolower(str_replace(" ", "-", $intern['status'])); ?>">
                        <td><?php echo htmlspecialchars($intern['internID']); ?></td>
                        <td><?php echo htmlspecialchars($intern['first_name'] ?? 'N/A'); ?></td>
                        <td class="<?php echo strtolower($intern['status'] ?? 'unknown'); ?>">
                            <strong><?php echo htmlspecialchars($intern['status'] ?? 'Unknown'); ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($intern['profile_image'])): ?>
                                <img id="imagePreview" src="uploaded_files/<?php echo htmlspecialchars($intern['profile_image']); ?>" alt="Profile Preview" style="width: 160px; height: 160px; border-radius: 50%;">
                            <?php else: ?>
                                <img id="imagePreview" src="image/USER_ICON.png" alt="Default Profile Preview">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (empty($interns) || !$hasActiveInterns): ?>
        <p>No interns found under your facilitation that are active today.</p>
    <?php endif; ?>
</div>

</div>
</div>





<div class="content-section" id="requests">
    <div class="req-content">
        <div class="req-container">
            <h1>Request</h1>

            <?php if (count($logs) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Intern ID</th>
                            <th>Admin ID</th>
                            <th>Login Time</th>
                            <th>Break Time</th>
                            <th>Back to Work Time</th>
                            <th>Task</th>
                            <th>Logout Time</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['internID']); ?></td>
                                <td><?php echo htmlspecialchars($log['adminID']); ?></td>
                                <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                                <td><?php echo htmlspecialchars($log['break_time'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['back_to_work_time'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['task'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['logout_time'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['status']); ?></td>

                                <!-- Action Buttons (Approve and Decline) -->
                                <td style="display: flex; justify-content: flex-start; align-items: center;">
                                    <form action="" method="post" style="margin-right: 10px;">
                                        <input type="hidden" name="internID" value="<?php echo $log['internID']; ?>" />
                                        <input type="hidden" name="id" value="<?php echo $log['id']; ?>" /> <!-- Unique identifier -->

                                        <?php 
                                        // Check if any of the fields are empty
                                        $disableApprove = empty($log['login_time']) || empty($log['break_time']) || empty($log['back_to_work_time']) || empty($log['task']) || empty($log['logout_time']);
                                        ?>

                                        <!-- Disable approve button if any field is empty -->
                                        <button type="submit" class="action-button approve" name="approveBtn">Approve</button>
                              
                                     </form>

                                    <!-- Update button -->
                                    <button type="button" class="action-button update" id="updateBtn<?php echo $log['id']; ?>" onclick="openModal(<?php echo $log['id']; ?>)">Update</button>
                                    
                                    <!-- Decline button -->
                                    <form method="POST" action="">
                                        <input type="hidden" name="internID" value="<?php echo $log['internID']; ?>"> <!-- Hidden internID -->
                                        <input type="hidden" name="id" value="<?php echo $log['id']; ?>"> <!-- Hidden log ID -->
                                        <button type="button" class="action-button decline" onclick="showDeclineForm(<?php echo $log['id']; ?>)">Decline</button>
                                    </form>

                                    <!-- Decline reason form, initially hidden -->
                                    <form id="declineReasonForm<?php echo $log['id']; ?>" method="POST" action="" style="display:none;">
                                        <input type="hidden" name="internID" value="<?php echo $log['internID']; ?>"> <!-- Hidden internID -->
                                        <input type="hidden" name="id" value="<?php echo $log['id']; ?>"> <!-- Hidden log ID -->
                                        <label for="decline_reason">Enter reason for decline:</label>
                                        <textarea name="decline_reason" id="decline_reason_<?php echo $log['id']; ?>" required></textarea>
                                        <button type="submit" name="submitDeclineReason">Submit Reason</button>
                                     
                                  </form>
                                </td>

                                


                        <?php endforeach; ?>
                    </tbody>

                </table>
            <?php else: ?>
                <p>No pending requests.</p>
            <?php endif; ?>
        </div>
    </div>
</div>




<!-- Modal Structure for Decline Action -->
<div id="updateModal<?php echo $log['id']; ?>" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal(<?php echo $log['id']; ?>)">&times;</span>
        <h2>Update Request</h2>
        <form action="" method="post">
            <input type="hidden" name="internID" value="<?php echo $log['internID']; ?>" />
            <input type="hidden" name="id" value="<?php echo $log['id']; ?>" /> <!-- Unique identifier -->

            <!-- Include the input values for editing the data before update -->
            <label for="update_login_time">Login Time:</label>
            <input type="datetime-local" name="login_time" value="<?php echo date('Y-m-d\TH:i', strtotime($log['login_time'])); ?>" required />

            <label for="update_break_time">Break Time:</label>
            <input type="datetime-local" name="break_time" value="<?php echo $log['break_time'] ? date('Y-m-d\TH:i', strtotime($log['break_time'])) : ''; ?>" required />

            <label for="update_back_to_work_time">Back to Work Time:</label>
            <input type="datetime-local" name="back_to_work_time" value="<?php echo $log['back_to_work_time'] ? date('Y-m-d\TH:i', strtotime($log['back_to_work_time'])) : ''; ?>" required />

            <label for="update_task">Task:</label>
            <input type="text" name="task" value="<?php echo htmlspecialchars($log['task'] ?? 'N/A'); ?>" required />

            <label for="update_logout_time">Logout Time:</label>
            <input type="datetime-local" name="logout_time" value="<?php echo $log['logout_time'] ? date('Y-m-d\TH:i', strtotime($log['logout_time'])) : ''; ?>" required />

            <button type="submit" class="action-button update" name="updateBtn">Update to Approve</button>
        </form>
    </div>
</div>


<div class="content-section" id="report">
    <div class="rep-content">
        <div class="wrapper">
            <!-- Search Form -->
            <form method="POST" action="" class="search-form">
                <label for="internID">InternID:</label>
                <input type="text" name="internID" id="internID" value="<?php echo htmlspecialchars($searchInternID ?? ''); ?>" placeholder="Search by Intern ID">

                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">Select Status</option>
                    <option value="approved" <?php echo ($searchStatus == 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="declined" <?php echo ($searchStatus == 'declined' ? 'selected' : ''); ?>>Declined</option>
                </select>
                <button type="submit">
                    <img src="./image/search.png" alt="Search" />
                </button>
            </form>

            <h2 class="approved-logs-header">Approved Logs</h2>

            <?php if (!empty($approvedLogs) && count($approvedLogs) > 0): ?>
                <div class="approved-logs-table-wrapper">
                    <table class="approved-logs-table">
                        <thead>
                            <tr>
                                <th>Intern ID</th>
                                <th>Task</th>
                                <th>Login Time</th>
                                <th>Logout Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedLogs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['internID'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['task'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['login_time'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['logout_time'] ?? 'N/A'); ?></td>
                                    <td class="status-<?php echo strtolower($log['status'] ?? 'n/a'); ?>">
                                        <?php echo htmlspecialchars($log['status'] ?? 'N/A'); ?>
                                    </td>
                                    
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No approved logs fsdadsdfsound.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


    <script src="js/faci_script.js"></script>
</body>
</html>