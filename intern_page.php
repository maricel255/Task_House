<?php
require('Admin_connection.php');

session_start(); // Start the session


// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['internID'])) {  // Ensure this matches the session variable from intern_log.php
  header("Location: intern_log.php");
  exit();
}


// Fetch user data based on the session intern ID
$internID = $_SESSION['internID'];
$sql = "SELECT * FROM intacc WHERE internID = :internID";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':internID', $internID);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);


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
                            <button class="btn break-btn">Break</button>
                            <button class="btn back-to-work-btn">Back to Work</button>
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
</div>



    
<div class="time-content">
    <div class="time-clock-container">
        <h3>Online Time Clock</h3>
        <p id="time-display">
            March 20, 2024 13:41:51<br>
            <span id="day-of-week">Wednesday</span>
        </p>
        <div class="tasks">
            <input type="text" id="text">
            <button class="login-btn">Log in</button>
            <button class   ="logut-butn">Log out</button>
        </div>
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
