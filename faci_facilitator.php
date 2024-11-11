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

// Handle form submission for approval and decline
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

    if (isset($_POST['declineBtn'])) {
        // Sanitize input to avoid malicious data
        $internID = htmlspecialchars($_POST['internID']);
        $id = htmlspecialchars($_POST['id']); // Get the unique log ID
        
        // Get the new values to update
        $decline_login_time = htmlspecialchars($_POST['decline_login_time']);
        $decline_break_time = htmlspecialchars($_POST['decline_break_time']);
        $decline_back_to_work_time = htmlspecialchars($_POST['decline_back_to_work_time']);
        $decline_task = htmlspecialchars($_POST['decline_task']);
        $decline_logout_time = htmlspecialchars($_POST['decline_logout_time']);

        // Define your update query to set the status to 'declined' and update other fields
        $sql = "UPDATE time_logs 
                SET status = 'approved', 
                    login_time = :login_time, 
                    break_time = :break_time, 
                    back_to_work_time = :back_to_work_time, 
                    task = :task, 
                    logout_time = :logout_time
                WHERE internID = :internID 
                AND id = :id 
                AND status = 'pending'"; // Ensure that only 'pending' logs are updated for the correct row

        // Prepare and execute the query using PDO
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Bind the log_id parameter
            $stmt->bindParam(':login_time', $decline_login_time, PDO::PARAM_STR);
            $stmt->bindParam(':break_time', $decline_break_time, PDO::PARAM_STR);
            $stmt->bindParam(':back_to_work_time', $decline_back_to_work_time, PDO::PARAM_STR);
            $stmt->bindParam(':task', $decline_task, PDO::PARAM_STR);
            $stmt->bindParam(':logout_time', $decline_logout_time, PDO::PARAM_STR);
            $stmt->execute();

            // Set success message in the session
            $_SESSION['alertMessage'] = "Status updated to 'Approved' and data updated for Intern ID: $internID";
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
SELECT i.internID, p.first_name, i.login_time, i.break_time, i.back_to_work_time, i.logout_time,
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
WHERE i.faciID = :faciID
AND (
    DATE(i.login_time) = CURDATE() OR
    DATE(i.break_time) = CURDATE() OR
    DATE(i.back_to_work_time) = CURDATE() OR
    DATE(i.logout_time) = CURDATE()
)
";


// Prepare and execute the query for interns
$stmt = $conn->prepare($query);
$stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);
$stmt->execute();
$interns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the count of active interns
$activeInterns = $stmt->fetch(PDO::FETCH_ASSOC);

// SQL query to count active interns who have logged today

$query = "
SELECT COUNT(DISTINCT i.internID) AS active_intern_count
FROM time_logs i
JOIN profile_information p ON i.internID = p.internID
WHERE i.faciID = :faciID
AND (
    DATE(i.login_time) = CURDATE() OR
    DATE(i.break_time) = CURDATE() OR
    DATE(i.back_to_work_time) = CURDATE() OR
    DATE(i.logout_time) = CURDATE()
)
";

// Prepare and execute the query to get the count of active interns
$stmt = $conn->prepare($query);
$stmt->bindParam(':faciID', $faciID, PDO::PARAM_INT);
$stmt->execute();
$activeInternCount = $stmt->fetchColumn();  // Get the count of active interns for today
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
        echo "<button class='close-btn' onclick='this.parentElement.style.display=\"none\"'>×</button>";
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
                <img src="image/cot-removebg-preview.png" alt="Toggle Sidebar" class="user-icon">
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
            <button class="logout-btn" onclick="logout()">
                <img src="image/logout.png" alt="logout icon" class="logout-icon">
                | LOG OUT
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-section" id="dashboard">
    <div class="main-content">
        <div class="announcement-board">
            <h2>ANNOUNCEMENT BOARD</h2>
           
                  

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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($interns as $intern): ?>
                                    <tr class="<?php echo strtolower(str_replace(" ", "-", $intern['status'])); ?>">
                    <td><?php echo htmlspecialchars($intern['internID']); ?></td>
                    <td><?php echo htmlspecialchars($intern['first_name']); ?></td>
                    
                    <td><?php echo htmlspecialchars($intern['status']); ?></td>
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

                                    <button type="button" class="action-button decline" id="declineBtn<?php echo $log['id']; ?>" onclick="openModal(<?php echo $log['id']; ?>)">Decline</button>
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
