<?php
require('Admin_connection.php');

session_start(); // Start the session

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['internID'])) { 
    header("Location: intern_log.php");
    exit();
}

$internID = $_SESSION['internID'];
$sql = "SELECT * FROM intacc WHERE internID = :internID";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch admin ID
$sqlFetchAdminID = "SELECT adminID FROM intacc WHERE internID = :internID";
$stmtFetch = $conn->prepare($sqlFetchAdminID);
$stmtFetch->bindParam(':internID', $internID, PDO::PARAM_INT);
$stmtFetch->execute();
$adminID = $stmtFetch->fetchColumn();

// Handle form submissions for logging in, break, back to work, and logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alertMessage = ''; // Initialize variable for alert message

    date_default_timezone_set('Asia/Manila');
    $currentDate = date('Y-m-d');
    $currentTime = date('Y-m-d H:i:s');
    $task = isset($_POST['task']) ? trim($_POST['task']) : '';

    // Only perform actions if buttons are clicked
    if (isset($_POST['login-btn'])) {
        // Check if there's an existing login record for today
        $sqlCheckLogin = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate";
        $stmtCheckLogin = $conn->prepare($sqlCheckLogin);
        $stmtCheckLogin->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmtCheckLogin->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
        $stmtCheckLogin->execute();

        if ($stmtCheckLogin->rowCount() == 0) {
            // No login record for today, so insert a new one
            $sqlInsert = "INSERT INTO time_logs (internID, adminID, task, login_time) VALUES (:internID, :adminID, :task, :loginTime)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmtInsert->bindParam(':adminID', $adminID, PDO::PARAM_INT);
            $stmtInsert->bindParam(':task', $task, PDO::PARAM_STR);
            $stmtInsert->bindParam(':loginTime', $currentTime, PDO::PARAM_STR);

            if ($stmtInsert->execute()) {
                $alertMessage = "Login time recorded successfully.";
            } else {
                $alertMessage = "Error recording login time.";
            }
        } else {
            $alertMessage = "You have already logged in today.";
        }
    }

    if (isset($_POST['break-btn'])) {
        // Check if there's a login record for today
        $sqlCheckBreak = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate AND break_time IS NULL";
        $stmtCheckBreak = $conn->prepare($sqlCheckBreak);
        $stmtCheckBreak->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmtCheckBreak->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
        $stmtCheckBreak->execute();

        if ($stmtCheckBreak->rowCount() > 0) {
            // Update break time if login exists for today
            $sqlUpdateBreak = "UPDATE time_logs SET break_time = :breakTime WHERE internID = :internID AND DATE(login_time) = :currentDate AND break_time IS NULL";
            $stmtUpdateBreak = $conn->prepare($sqlUpdateBreak);
            $stmtUpdateBreak->bindParam(':breakTime', $currentTime, PDO::PARAM_STR);
            $stmtUpdateBreak->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmtUpdateBreak->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);

            if ($stmtUpdateBreak->execute()) {
                $alertMessage = "Break time recorded successfully.";
            } else {
                $alertMessage = "Error recording break time.";
            }
        } else {
            $alertMessage = "Please log in before taking a break.";
        }
    }

    if (isset($_POST['back-to-work-btn'])) {
        // Check if a break was recorded for today
        $sqlCheckBackToWork = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate AND break_time IS NOT NULL AND back_to_work_time IS NULL";
        $stmtCheckBackToWork = $conn->prepare($sqlCheckBackToWork);
        $stmtCheckBackToWork->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmtCheckBackToWork->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
        $stmtCheckBackToWork->execute();

        if ($stmtCheckBackToWork->rowCount() > 0) {
            // Update back to work time if break exists
            $sqlUpdateBackToWork = "UPDATE time_logs SET back_to_work_time = :backToWorkTime WHERE internID = :internID AND DATE(login_time) = :currentDate AND back_to_work_time IS NULL";
            $stmtUpdateBackToWork = $conn->prepare($sqlUpdateBackToWork);
            $stmtUpdateBackToWork->bindParam(':backToWorkTime', $currentTime, PDO::PARAM_STR);
            $stmtUpdateBackToWork->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmtUpdateBackToWork->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);

            if ($stmtUpdateBackToWork->execute()) {
                $alertMessage = "Back to work time recorded successfully.";
            } else {
                $alertMessage = "Error recording back to work time.";
            }
        } else {
            $alertMessage = "Please take a break before returning to work.";
        }
    }


// Check if the task form is submitted
if (isset($_POST['submitTask'])) {
    // Check if a task is entered
    if (empty($_POST['task'])) {
        $alertMessage = "Please enter your task before logging out.";
    } else {
        // Fetch current time
        $currentTime = date("Y-m-d H:i:s"); // Get the current time
        $task = $_POST['task'];

        // Insert the task into the time_logs table with unique internID (assuming internID is not unique)
        $sqlInsertTask = "INSERT INTO time_logs (internID, task, back_to_work_time, logout_time) 
                          VALUES (:internID, :task, NULL, NULL)";
        
        $stmtInsertTask = $conn->prepare($sqlInsertTask);
        $stmtInsertTask->bindParam(':internID', $internID, PDO::PARAM_INT);  // Ensure internID is correct and unique for this session
        $stmtInsertTask->bindParam(':task', $task, PDO::PARAM_STR);

        // Execute the insert query
        if ($stmtInsertTask->execute()) {
            $alertMessage = "Task recorded successfully.";
        } else {
            $alertMessage = "Error recording task.";
        }
    }
}

// Check if logout button is clicked
if (isset($_POST['logout-btn'])) {
    // Check if a task is entered before logging out
    if (empty($_POST['task'])) {
        $alertMessage = "Please enter your task before logging out.";
    } else {
        // Check if there's a back-to-work record for today

        $sqlCheckLogout = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate AND back_to_work_time IS NOT NULL AND logout_time IS NULL";
        $stmtCheckLogout = $conn->prepare($sqlCheckLogout);
        $stmtCheckLogout->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmtCheckLogout->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
        $stmtCheckLogout->execute();

        if ($stmtCheckLogout->rowCount() > 0) {
            // Update logout time if back to work exists
            $sqlUpdateLogout = "UPDATE time_logs SET logout_time = :logoutTime WHERE internID = :internID AND DATE(login_time) = :currentDate AND logout_time IS NULL";
            $stmtUpdateLogout = $conn->prepare($sqlUpdateLogout);
            $stmtUpdateLogout->bindParam(':logoutTime', $currentTime, PDO::PARAM_STR);
            $stmtUpdateLogout->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmtUpdateLogout->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);

            if ($stmtUpdateLogout->execute()) {
                $alertMessage = "Logout time recorded successfully.";
            } else {
                $alertMessage = "Error recording logout time.";
            }
        } else {
            $alertMessage = "Please return to work before logging out.";
        }
    }
}


    // Store the alert message in the session
    if ($alertMessage) {
        $_SESSION['alertMessage'] = $alertMessage;
    }

    // Redirect after form submission to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// Check if there is an alert message to display
if (isset($_SESSION['alertMessage'])) {
    echo "<div class='alert-box'><span>" . $_SESSION['alertMessage'] . "</span><button class='close-btn' onclick='this.parentElement.style.display=\"none\"'>X</button></div>";
    // Clear the alert message after displaying
    unset($_SESSION['alertMessage']);
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expandable Sidebar with User Info</title>
    <link rel="stylesheet" href="css/intern_styles.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar hide-content">
                    <div class="user-info">

                    <div class="profile-image"> 
                        <img src="uploaded_files/<?php echo htmlspecialchars($profileImage, ENT_QUOTES, 'UTF-8'); ?>" class="user-icon" alt="User Profile" >
                    </div>
                   
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($userData['internName']); ?></p>
                        <p>Intern ID: <?php echo htmlspecialchars($internID); ?></p>
                        <p class="role">INTERN</p>
                        <div class="button-container"> <!-- New container for buttons -->
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <button type="submit" name="break-btn" class="btn break-btn">Break</button>
                            <button type="submit" name="back-to-work-btn" class="btn back-to-work-btn">Back to Work</button>
                        </form>
                        </div>
                    </div>
                </div>
            <nav class="navigation">
                <a href="#" class="home-link" onclick="showContent('dashboard')">
                    <i class="fa fa-home"></i><span> Dashboard</span>
                </a>
                <a href="#" onclick="showContent('attendance')">
                    <i class="fa fa-cog"></i><span> Attendance</span>
                </a>
                <a href="#" onclick="showContent('requests')">
                    <i class="fa fa-cog"></i><span> Requests</span>
                </a>
            </nav>
            
            <!-- Profile Button in Sidebar -->
            <a href="#" onclick="openModal()" class="bottom-right-corner profile-button">
                <i class="fas fa-power-off"></i><span>Profile</span>
            </a>
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

            <?php
              if (isset($_SESSION['internID'])) {
                  $internID = $_SESSION['internID'];

                  // Fetch the adminID for the current intern
                  $sql_admin = "SELECT adminID FROM intacc WHERE internID = :internID";
                  $stmt_admin = $conn->prepare($sql_admin);
                  $stmt_admin->bindValue(':internID', $internID, PDO::PARAM_STR);

                  if ($stmt_admin->execute()) {
                      $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

                      if ($admin) {
                          $adminID = $admin['adminID'];

                          // Fetch announcements for the current adminID
                          $sql = "SELECT title, announcementID, imagePath, content 
                                  FROM announcements 
                                  WHERE adminID = :adminID";
                          $stmt = $conn->prepare($sql);
                          $stmt->bindValue(':adminID', $adminID, PDO::PARAM_INT);

                          if ($stmt->execute()) {
                              $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

                              // Display announcements in a simple slider
                              if ($announcements) {
                                  echo '<div class="announcement-slider">';
                                  echo '<div class="slider-container">';

                                  // Create announcement items
                                  foreach ($announcements as $index => $announcement) {
                                      $filePath = htmlspecialchars($announcement['imagePath']);
                                      $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                                      $activeClass = $index === 0 ? 'active' : '';

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
                          echo '<p>Admin information not found for this intern.</p>';
                      }
                  } else {
                      echo '<p>Error fetching admin information.</p>';
                  }
              } else {
                  echo '<p>Intern is not logged in.</p>';
                  exit;
              }
              ?>
                  

        </div>
          <img src="image/announce.png" alt="Announcement Image" class="anno-img">
    </div>




    
        <div class="time-content">
            <div class="time-clock-container">
                <h3>Online Time Clock</h3>
                <p id="time-display">
                    March 20, 2024 13:41:51<br>
                    <span id="day-of-week">Wednesday</span>
                </p>
                <div class="tasks">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    
                    <button type="submit" name="login-btn" class="login-btn">Log in</button>
                    <button type="button" name="logout-btn" class="logout-btn" onclick="document.getElementById('taskModal').style.display='block'">Log out</button>
                    </form>
                      
                 </div>  
            </div>

            </div>
        </div>


 <!-- Modal for entering task -->
<div id="taskModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Enter Task Before Logging Out</h2>
        <form method="POST">
            <label for="task">Task:</label>
            <input type="text" id="task" name="task" required>
            <button type="submit" name="submitTask">Submit Task</button>
        </form>
    </div>
</div>


</div>
</div><!-- Modal for Profile Information -->
<div id="profileModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2>Profile Information</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
    <div class="form-container">
        <div class="form-group">
            <label for="internName">Intern's Name:</label>
            <input type="text" id="internName" name="internName" 
                   value="<?php echo isset($userData['internName']) ? htmlspecialchars($userData['internName']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="profileImage">Upload Profile Image:</label>
            <input type="file" id="profileImage" name="profileImage" accept="image/*">
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="requiredHours">Required Hours:</label>
            <input type="number" id="requiredHours" name="requiredHours" 
                   value="<?php echo isset($userData['requiredHours']) ? htmlspecialchars($userData['requiredHours']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="dateStarted">Date Started:</label>
            <input type="date" id="dateStarted" name="dateStarted" 
                   value="<?php echo isset($userData['dateStarted']) ? htmlspecialchars($userData['dateStarted']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="dateEnded">Date Ended:</label>
            <input type="date" id="dateEnded" name="dateEnded" 
                   value="<?php echo isset($userData['dateEnded']) ? htmlspecialchars($userData['dateEnded']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" 
                   value="<?php echo isset($userData['dob']) ? htmlspecialchars($userData['dob']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="facilitatorName">Facilitator Name:</label>
            <input type="text" id="facilitatorName" name="facilitatorName" 
                   value="<?php echo isset($userData['facilitatorName']) ? htmlspecialchars($userData['facilitatorName']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="courseSection">Course & Section:</label>
            <input type="text" id="courseSection" name="courseSection" 
                   value="<?php echo isset($userData['courseSection']) ? htmlspecialchars($userData['courseSection']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="facilitatorEmail">Facilitator Email:</label>
            <input type="email" id="facilitatorEmail" name="facilitatorEmail" 
                   value="<?php echo isset($userData['facilitatorEmail']) ? htmlspecialchars($userData['facilitatorEmail']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male" <?php echo (isset($userData['gender']) && $userData['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo (isset($userData['gender']) && $userData['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo (isset($userData['gender']) && $userData['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="companyName">Company Name:</label>
            <input type="text" id="companyName" name="companyName" 
                   value="<?php echo isset($userData['companyName']) ? htmlspecialchars($userData['companyName']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="shiftStart">Shift Start:</label>
            <input type="time" id="shiftStart" name="shiftStart" 
                   value="<?php echo isset($userData['shiftStart']) ? htmlspecialchars($userData['shiftStart']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="shiftEnd">Shift End:</label>
            <input type="time" id="shiftEnd" name="shiftEnd" 
                   value="<?php echo isset($userData['shiftEnd']) ? htmlspecialchars($userData['shiftEnd']) : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="facilitatorID">Facilitator ID:</label>
            <input type="text" id="facilitatorID" name="facilitatorID" 
                   value="<?php echo isset($userData['facilitatorID']) ? htmlspecialchars($userData['facilitatorID']) : ''; ?>" 
                   required>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="submit" class="create-button">
            <?php echo isset($_SESSION['InternID']) ? 'Update Profile' : 'Create Profile'; ?>
        </button>
    </div>
</form>
  </div>
</div>

<div class="content-section" id="attendance">
    <div class="attend-content">
    <div class="attendance-container">
        <h1>REQUIRED HOURS: 700 HRS</h1>
    </div>
</div>
    </div>

    <div class="content-section" id="requests">
        <div class="req-content">
            <div class="wrapper">
            <button class="styled-button">Print</button>
        </div>
        </div>
    </div>

    

   

    <script src="js/Intern_script.js"></script>
</body>
</html>
