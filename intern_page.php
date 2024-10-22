<?php
require('Admin_connection.php');

session_start(); // Start the session

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully.<br>"; // Debugging line
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Fetch and sanitize input values safely
  $internPass = isset($_POST['internPass']) ? $_POST['internPass'] : ''; // Default to empty if not set
  $profileImage = $_FILES['profileImage']['tmp_name']; // Handle file upload appropriately

  // Check if the file upload was successful and read the image data
  $imageData = file_exists($profileImage) ? file_get_contents($profileImage) : null;

  // Prepare the SQL statement for updating intern data
  $sql = "UPDATE intacc SET 
              InternPass = ?, 
              internName = ?, 
              profileImage = ?, 
              email = ?, 
              requiredHours = ?, 
              dateStarted = ?, 
              dateEnded = ?, 
              dob = ?, 
              facilitatorName = ?, 
              courseSection = ?, 
              facilitatorEmail = ?, 
              gender = ?, 
              companyName = ?, 
              shiftStart = ?, 
              shiftEnd = ?, 
              facilitatorID = ? 
          WHERE InternID = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
      "sssssssssssssssss", 
      $internPass, 
      $internData['internName'], 
      $imageData, 
      $internData['email'], 
      $internData['requiredHours'], 
      $internData['dateStarted'], 
      $internData['dateEnded'], 
      $internData['dob'], 
      $internData['facilitatorName'], 
      $internData['courseSection'], 
      $internData['facilitatorEmail'], 
      $internData['gender'], 
      $internData['companyName'], 
      $internData['shiftStart'], 
      $internData['shiftEnd'], 
      $internData['facilitatorID'], 
      $internID // To specify which record to update
  );

  if ($stmt->execute()) {
      echo "Data updated successfully.";
  } else {
      echo "Error updating data: " . $stmt->error;
  }
  $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expandable Sidebar with User Info</title>
    <link rel="stylesheet" href="intern_styles.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar hide-content">
            <div class="user-info">
                <img src="image/cot.png" alt="Toggle Sidebar" class="user-icon">
                <div class="user-details">
                    <p class="user-name">Last name, First name</p>
                    <p>Intern ID: <?php echo isset($internID) ? htmlspecialchars($internID) : 'No InternID found'; ?></p>

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
                <p class="announcement">This announcement's from CUT COT admin</p>
                <p class="announcement-details">
                    GDSAKFHASDFKNBASDF <br>
                    NFDSKAJFSDFLKNDSLAFLKNVADSLVNLSALVN <br>
                    SDAFDSAPFKJASDLFJASDLDFKMNF
                </p>
            </div>
        </div>
    

    <div class="time-content" >
    <div class="time-clock-container" >
        <h3>Online Time Clock</h3>
        <p id="time-display">Wednesday, March 20, 2024 13:41:51</p>
        <p>Last login at: <span id="last-login-time">3/19/2024 10:15</span></p>
        <div class="tasks">
            <input type="text" id="text">
            <button class="login-btn">Log in</button>
            <button class="logut-butn">Log out</button>
        </div>
    </div>
</div>
</div>

<!-- Modal for Profile Information -->
<div id="profileModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2>Profile Information</h2>
    <form action="#" method="POST" enctype="multipart/form-data">
    <div class="form-container">
  <div class="form-group">
    <label for="internName">Intern's Name:</label>
    <input type="text" id="internName" name="internName" value=" <?php echo isset($internName) ? htmlspecialchars($internName) : 'No internName found'; ?>">
    </div>
  <div class="form-group">
    <label for="profileImage">Upload Profile Image:</label>
    <input type="file" id="profileImage" name="profileImage">
  </div>
  <div class="form-group">
    <label for="shiftStart">Shift Start:</label>
    <input type="time" id="shiftStart" name="shiftStart">
  </div>
  <div class="form-group">
    <label for="shiftEnd">Shift End:</label>
    <input type="time" id="shiftEnd" name="shiftEnd">
  </div>
  <div class="form-group">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" placeholder="example@example.com">
  </div>
  <div class="form-group">
    <label for="requiredHours">Required Hours:</label>
    <input type="number" id="requiredHours" name="requiredHours" placeholder="700">
  </div>
  <div class="form-group">
    <label for="dateStarted">Date Started:</label>
    <input type="date" id="dateStarted" name="dateStarted">
  </div>
  <div class="form-group">
    <label for="dateEnded">Date Ended:</label>
    <input type="date" id="dateEnded" name="dateEnded">
  </div>
  <div class="form-group">
    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob">
  </div>
  <div class="form-group">
    <label for="facilitatorName">Facilitator Name:</label>
    <input type="text" id="facilitatorName" name="facilitatorName" placeholder="Facilitator Name">
  </div>
  <div class="form-group">
    <label for="courseSection">Course & Section:</label>
    <input type="text" id="courseSection" name="courseSection" placeholder="Course and Section">
  </div>
  <div class="form-group">
    <label for="facilitatorEmail">Facilitator Email:</label>
    <input type="email" id="facilitatorEmail" name="facilitatorEmail" placeholder="example@example.com">
  </div>
  <div class="form-group">
    <label for="gender">Gender:</label>
    <select id="gender" name="gender">
      <option value="male">Male</option>
      <option value="female">Female</option>
      <option value="other">Other</option>
    </select>
  </div>
  <div class="form-group">
    <label for="companyName">Company Name:</label>
    <input type="text" id="companyName" name="companyName" placeholder="Company Name">
  </div>
  <div class="form-group">
    <label for="facilitatorID">Facilitator ID:</label>
    <input type="text" id="facilitatorID" name="facilitatorID" placeholder="Facilitator ID">
  </div>
</div>

    </div>
    <div class="modal-footer">
    <button type="submit" class="create-button">Create</button>
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

    

   

    <script src="Intern_script.js"></script>
</body>
</html>
