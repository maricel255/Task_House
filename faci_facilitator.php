<?php
require('Admin_connection.php');

session_start(); // Start the session

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
// Handle form submission for approval, decline, and update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approveBtn'])) {
        // Sanitize input to avoid malicious data
        $internID = htmlspecialchars($_POST['internID']);
        $id = htmlspecialchars($_POST['id']); // Get the unique log ID

        // Define your update query to set the status to 'approved' only for the specific row (log_id)
        $sql = "UPDATE time_logs 
                SET status = 'approved' 
                WHERE internID = :internID 
                AND id = :id 
                AND status = 'pending'"; // Ensure that only 'pending' logs are updated for the correct row

        // Prepare and execute the query using PDO
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Bind the log_id parameter
            $stmt->execute();

            // Set success message in the session
            $_SESSION['alertMessage'] = "Status updated to 'approved' for Intern ID: $internID";
            $_SESSION['alertType'] = 'success';  // Set type as 'success'

            // Redirect to avoid re-submitting the form on page refresh (Post-Redirect-Get)
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            // Set error message in the session if an exception occurs
            $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
            $_SESSION['alertType'] = 'error';  // Set type as 'error'
        }
    }

    if (isset($_POST['submitDeclineReason'])) {
        $internID = htmlspecialchars($_POST['internID']);
        $id = htmlspecialchars($_POST['id']);
        $decline_reason = htmlspecialchars($_POST['decline_reason']); // Get reason for decline

        // Update status to 'declined' and store reason in task
        $sql = "UPDATE time_logs 
                SET status = 'declined', 
                    task = :decline_reason
                WHERE internID = :internID 
                AND id = :id"; // Only update the task (reason) and status

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':decline_reason', $updated_task, PDO::PARAM_STR); // Store the reason
            $stmt->execute();

            $_SESSION['alertMessage'] = "Time log declined and reason added for Intern ID: $internID";
            $_SESSION['alertType'] = 'success';

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
            $_SESSION['alertType'] = 'error';
        }
    }

    // Handle Update logic (if needed for the form/modal)
    if (isset($_POST['updateBtn'])) {
        // Sanitize and update the fields as needed
        $internID = htmlspecialchars($_POST['internID']);
        $id = htmlspecialchars($_POST['id']); // Get the log ID

        // Update specific fields (depending on your form)
        $updated_login_time = htmlspecialchars($_POST['login_time']);
        $updated_task = htmlspecialchars($_POST['task']);

        // SQL query for updating values
        $sql = "UPDATE time_logs 
                SET login_time = :login_time, 
                    task = :task
                WHERE internID = :internID 
                AND id = :id"; 

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); 
            $stmt->bindParam(':login_time', $updated_login_time, PDO::PARAM_STR);
            $stmt->bindParam(':task', $updated_task, PDO::PARAM_STR);
            $stmt->execute();

            // Set success message
            $_SESSION['alertMessage'] = "Log updated for Intern ID: $internID";
            $_SESSION['alertType'] = 'success'; 

            // Redirect
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
            $_SESSION['alertType'] = 'error';
        }
    }
}




// Query to fetch approved logs for the specific faciID
$sql = "SELECT * FROM time_logs WHERE status = 'approved' AND faciID = :faciID";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT); // Bind the faciID
    $stmt->execute();
    $approvedLogs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows as an associative array
} catch (PDOException $e) {
    // Handle error
    $_SESSION['alertMessage'] = "Error: " . $e->getMessage();
    $_SESSION['alertType'] = 'error';
}


// SQL query to fetch intern data and determine status based on login_time, break_time, and logout_time
$query = "
SELECT i.internID, p.first_name, ia.profile_image, i.login_time, i.break_time, i.back_to_work_time, i.logout_time,
       CASE
           -- If logout_time is not NULL or empty, show 'Logged Out' (highest priority)
           WHEN (i.logout_time IS NOT NULL AND i.logout_time != '') THEN 'Logged Out'
           
           -- If break_time is present and back_to_work_time is NULL or empty, show 'On Break'
           WHEN (i.break_time IS NOT NULL AND i.break_time != '') 
                AND (i.back_to_work_time IS NULL OR i.back_to_work_time = '') THEN 'On Break'
           
           -- If login_time is present, back_to_work_time is NULL or empty, and logout_time is NULL, show 'Active Now'
           WHEN (i.login_time IS NOT NULL AND i.login_time != '') 
                AND (i.back_to_work_time IS NULL OR i.back_to_work_time = '') 
                AND (i.logout_time IS NULL OR i.logout_time = '') THEN 'Active Now'
           
           -- If back_to_work_time is present (indicating the intern is back to work), show 'Back to Work'
           WHEN (i.back_to_work_time IS NOT NULL AND i.back_to_work_time != '') THEN 'Back to Work'
           
           -- Default to 'Unknown' if no status is detected
           ELSE 'Unknown'
       END AS status
FROM time_logs i
JOIN profile_information p ON i.internID = p.internID
JOIN intacc ia ON i.internID = ia.internID  -- Join with intacc table for profile_image
WHERE i.faciID = :faciID
AND (
    DATE(i.login_time) = CURDATE() OR
    DATE(i.break_time) = CURDATE() OR
    DATE(i.back_to_work_time) = CURDATE() OR
    DATE(i.logout_time) = CURDATE()
)
";


// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);
$stmt->execute();

// Fetch the results
$interns = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Prepare and execute the query to get the count of active interns
$stmt = $conn->prepare($query);
$stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);
$stmt->execute();
$interns = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all interns' details
$activeInternCount = count($interns);  // Get the count of active interns







?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task_House-Hr/ Manager</title>
    <link rel="stylesheet" href="faci_styles.css">
</head>
<body>
    <!-- Alert Message Box - This will be displayed after form submission -->
    <?php
    if (isset($_SESSION['alertMessage'])) {
        // Determine the alert class based on the message type
        $alertClass = isset($_SESSION['alertType']) && $_SESSION['alertType'] === 'error' ? 'alert-error' : 'alert-success';
        
        // Display the alert message
        echo "<div class='alert-box {$alertClass}' id='alertBox'>";
        echo "<span>" . $_SESSION['alertMessage'] . "</span>";
        echo "<button class='close-btn' onclick='this.parentElement.style.display=\"none\"'>x</button>";
        echo "</div>";

        // Clear the alert message and type from the session
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
                                $pdfPath = "http://localhost/uploaded_files/" . rawurlencode($fileName);
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

          <img src="image/announce.png" alt="Announcement Image" class="anno-img">
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
                <p>5 Active Intern's</p>
            </div>
            <div class="card green">
                <h3>APPROVAL REQUESTS</h3>
                <p>5 Active Intern's</p>
            </div>
        </div>

        <div class="intern-status-dashboard">
    <h1>Intern Status Dashboard</h1>


    <?php if ($interns): ?>
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
                <?php foreach ($interns as $intern): ?>
                    <tr class="<?php echo strtolower(str_replace(" ", "-", $intern['status'])); ?>">
                        <td><?php echo htmlspecialchars($intern['internID']); ?></td>
                        <td><?php echo htmlspecialchars($intern['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($intern['status']); ?></td>
                        <td>
                            <?php if (!empty($intern['profile_image'])): ?>
                                <!-- Display profile image -->
                                <img id="imagePreview" src="uploaded_files/<?php echo htmlspecialchars($intern['profile_image']); ?>" alt="Profile Preview" style="width: 160px; height: 160px; border-radius: 50%;">
                                <?php else: ?>
                                <!-- Default image if no profile image exists -->
                                <img id="imagePreview" src="image/USER_ICON.png" alt="Default Profile Preview" width="150" height="150">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No interns found under your facilitation.</p>
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
                                    <button type="submit" class="action-button approve" name="approveBtn" <?php echo ($disableApprove ? 'disabled' : ''); ?>>Approve</button>
                                </form>

                                    <button type="button" class="action-button decline" id="declineBtn<?php echo $log['id']; ?>" onclick="openModal(<?php echo $log['id']; ?>)">Update</button>
                                
                                         <!-- Decline button -->
                                         <form method="POST" action=" ">
    <input type="hidden" name="internID" value="<?php echo $internID; ?>"> <!-- Hidden internID -->
    <input type="hidden" name="id" value="<?php echo $id; ?>"> <!-- Hidden log ID -->
    <button type="button" id="declineBtn">Decline</button>
</form>

<!-- Decline reason form, initially hidden -->
<form id="declineReasonForm" method="POST" action=" " style="display:none;">
    <input type="hidden" name="internID" value="<?php echo $internID; ?>"> <!-- Hidden internID -->
    <input type="hidden" name="id" value="<?php echo $id; ?>"> <!-- Hidden log ID -->
    <label for="decline_reason">Enter reason for decline:</label>
    <textarea name="decline_reason" id="decline_reason" required></textarea>
    <button type="submit" name="submitDeclineReason">Submit Reason</button>
</form>
                            </div>
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
<div id="declineModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Decline Request</h2>
        <form action="" method="post">
            <input type="hidden" name="internID" value="<?php echo $log['internID']; ?>" />
            <input type="hidden" name="id" value="<?php echo $log['id']; ?>" /> <!-- Unique identifier -->

            <!-- Include the input values for editing the data before decline -->
            <label for="decline_login_time">Login Time:</label>
            <input type="text" name="decline_login_time" value="<?php echo htmlspecialchars($log['login_time']); ?>" required />

            <label for="decline_break_time">Break Time:</label>
            <input type="text" name="decline_break_time" value="<?php echo htmlspecialchars($log['break_time'] ?? 'N/A'); ?>" required />

            <label for="decline_back_to_work_time">Back to Work Time:</label>
            <input type="text" name="decline_back_to_work_time" value="<?php echo htmlspecialchars($log['back_to_work_time'] ?? 'N/A'); ?>" required />

            <label for="decline_task">Task:</label>
            <input type="text" name="decline_task" value="<?php echo htmlspecialchars($log['task'] ?? 'N/A'); ?>" required />

            <label for="decline_logout_time">Logout Time:</label>
            <input type="text" name="decline_logout_time" value="<?php echo htmlspecialchars($log['logout_time'] ?? 'N/A'); ?>" required />

            <button type="submit" class="action-button decline" name="declineBtn">Update to Approve</button>
        </form>
    </div>
</div>




<div class="content-section" id="report">
    <div class="rep-content">
        <div class="wrapper">
        <h2 style="font-size: 32px; font-weight: 600; color: #006400; margin-bottom: 20px; text-align: left; border-bottom: 2px solid #006400; padding-bottom: 10px;">
            Approved Logs
        </h2>            <?php if (count($approvedLogs) > 0): ?>
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
                                <td><?php echo htmlspecialchars($log['internID']); ?></td>
                                <td><?php echo htmlspecialchars($log['task']); ?></td>
                                <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                                <td><?php echo htmlspecialchars($log['logout_time']); ?></td>
                                <td class="status-<?php echo strtolower($log['status']); ?>">
                                    <?php echo htmlspecialchars($log['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No approved logs found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


    <script src="faci_script.js"></script>
</body>
</html>