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


// this is for  PROFILE MODAL



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



<!-- Modal for Profile Information -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <!-- Single form for all categories -->
        <form action=" " method="POST" enctype="multipart/form-data">
            
            <!-- Sidebar for category navigation -->
            <div class="profile-sidebar">
                <button type="button" onclick="showCategory('personalData')">Personal Data</button>
                <button type="button" onclick="showCategory('companyDetails')">Company Details</button> <!-- New button -->   
                <button type="button" onclick="showCategory('familyData')">Family Data</button>
                <button type="button" onclick="showCategory('healthData')">Health Data</button>
                <button type="button" onclick="showCategory('scholasticData')">Scholastic Data</button>
                <button type="button" onclick="showCategory('workExperience')">Work Experience</button>
                <button type="button" onclick="showCategory('specialSkills')">Special Skills</button>
                <button type="button" onclick="showCategory('characterReferences')">Character References</button>
                <button type="button" onclick="showCategory('emergencyContact')">Emergency Contact</button>
            </div>

            <div class="profile-form-container">
                
                <!-- Personal Data -->
                <div id="personalData" class="profile-category">
                    <h3>Personal Data</h3>
                    <div class="form-container">
                        <div class="form-row">
                            <label>First Name:</label>
                            <input type="text" name="firstName"><br>

                            <label>Middle Name:</label>
                            <input type="text" name="middleName"><br>

                            <label>Last Name:</label>
                            <input type="text" name="lastName"><br>

                            <label>Course, Year, Sec.:</label>
                            <input type="text" name="courseYearSec"><br>

                            <label>Gender:</label>
                            <input type="radio" name="gender" value="Male"> Male
                            <input type="radio" name="gender" value="Female"> Female<br>

                            <label>Age:</label>
                            <input type="number" name="age"><br>

                            <label>Current Address:</label>
                            <input type="text" name="currentAddress"><br>
                        </div>

                        <div class="form-row">
                            <label>Provincial Address:</label>
                            <input type="text" name="provincialAddress"><br>

                            <label>Tel. No.:</label>
                            <input type="text" name="telNo"><br>

                            <label>Mobile No.:</label>
                            <input type="text" name="mobileNo"><br>

                            <label>Birth Place:</label>
                            <input type="text" name="birthPlace"><br>

                            <label>Birth Date:</label>
                            <input type="date" name="birthDate"><br>

                            <label>Religion:</label>
                            <input type="text" name="religion"><br>

                            <label>Email Address:</label>
                            <input type="email" name="email"><br>

                            <label>Civil Status:</label>
                            <input type="text" name="civilStatus"><br>

                            <label>Citizenship:</label>
                            <input type="text" name="citizenship"><br>
                        </div>
                    </div>
                </div>

                <!-- Company Details -->
                <div id="companyDetails" class="profile-category">
                    <h3>Company Details</h3>
                    <label>HR/Manager:</label>
                    <input type="text" name="hrManager"><br>
                    <label>faciID:</label>
                    <input type="text" name="faciID"><br>
                    <label>Company:</label>
                    <input type="text" name="company"><br>
                    <label>Company Address:</label>
                    <input type="text" name="companyAddress"><br>
                </div>

                <!-- Family Data -->
                <div id="familyData" class="profile-category">
                    <h3>Family Data</h3>
                    <label>Father's Name:</label>
                    <input type="text" name="fatherName"><br>
                    <label>Occupation:</label>
                    <input type="text" name="fatherOccupation"><br>
                    <label>Mother's Name:</label>
                    <input type="text" name="motherName"><br>
                    <label>Occupation:</label>
                    <input type="text" name="motherOccupation"><br>
                </div>

                <!-- Health Data -->
                <div id="healthData" class="profile-category">
                    <h3>Health Data</h3>
                    <label>Blood Type:</label>
                    <input type="text" name="bloodType"><br>
                    <label>Height:</label>
                    <input type="text" name="height"><br>
                    <label>Weight:</label>
                    <input type="text" name="weight"><br>
                    <label>Health Problems:</label>
                    <input type="text" name="healthProblems"><br>
                </div>

                <!-- Scholastic Data -->
                <div id="scholasticData" class="profile-category">
                    <h3>Scholastic Data</h3>
                    <label>Elementary School:</label>
                    <input type="text" name="elementarySchool"><br>
                    <label>Year Graduated:</label>
                    <input type="text" name="elementaryYearGraduated"><br>
                    <label>Honors/Awards Received:</label>
                    <input type="text" name="elementaryHonors"><br>

                    <label>Secondary School:</label>
                    <input type="text" name="secondarySchool"><br>
                    <label>Year Graduated:</label>
                    <input type="text" name="secondaryYearGraduated"><br>
                    <label>Honors/Awards Received:</label>
                    <input type="text" name="secondaryHonors"><br>

                    <label>College:</label>
                    <input type="text" name="college"><br>
                    <label>Year Graduated:</label>
                    <input type="text" name="collegeYearGraduated"><br>
                    <label>Honors/Awards Received:</label>
                    <input type="text" name="collegeHonors"><br>
                </div>

                <!-- Work Experience -->
                <div id="workExperience" class="profile-category">
                    <h3>Work Experience</h3>
                    <label>Company Name:</label>
                    <input type="text" name="companyName"><br>
                    <label>Position:</label>
                    <input type="text" name="position"><br>
                    <label>Inclusive Date:</label>
                    <input type="text" name="inclusiveDate"><br>
                    <label>Address:</label>
                    <input type="text" name="companyAddress"><br>
                </div>

                <!-- Special Skills -->
                <div id="specialSkills" class="profile-category">
                    <h3>Special Skills</h3>
                    <label>Skills:</label>
                    <input type="text" name="skills"><br>
                </div>

                <!-- Character References -->
                <div id="characterReferences" class="profile-category">
                    <h3>Character References</h3>
                    <label>Name:</label>
                    <input type="text" name="refName"><br>
                    <label>Position:</label>
                    <input type="text" name="refPosition"><br>
                    <label>Address:</label>
                    <input type="text" name="refAddress"><br>
                    <label>Contact No.:</label>
                    <input type="text" name="refContact"><br>
                </div>

                <!-- Emergency Contact -->
                <div id="emergencyContact" class="profile-category">
                    <h3>Emergency Contact</h3>
                    <label>Name:</label>
                    <input type="text" name="emergencyName"><br>
                    <label>Address:</label>
                    <input type="text" name="emergencyAddress"><br>
                    <label>Contact No.:</label>
                    <input type="text" name="emergencyContactNo"><br>
                </div>
            </div>

            <button type="submit" class="insert-btn">Add Information</button>
        </form>

    </div>
</div>



  








  <div class="content-section" id="attendance">
    <div class="attend-content">
    <div class="attendance-container">
        <h1>REQUIRED HOURS: 700 HRS</h1>
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
