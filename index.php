<?php
session_start(); // Start a session

// Include your database connection file
include 'Admin_connection.php'; // Ensure this file contains your DB connection code

$message = ''; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare an SQL statement to select the user
    $stmt = $conn->prepare("SELECT password FROM developers WHERE username = :username");
    
    // Bind parameters
    $stmt->bindValue(':username', $username); // Use bindValue for PDO
    $stmt->execute(); // Execute the statement

    // Check if the user exists
    // Check if the user exists
if ($stmt->rowCount() > 0) {
  // Fetch the password
  $storedPassword = $stmt->fetchColumn(); // Fetch the first column from the result

  // Check if the provided password matches the stored password
  if ($password === $storedPassword) {
      // Successful login
      $_SESSION['message'] = "Login successful!"; // Set success message
      $_SESSION['message_type'] = "success"; // Set message type for success
      // Redirect or perform actions after successful login
    
  } else {
      // Invalid password
      $_SESSION['message'] = "Invalid username or password.";
      $_SESSION['message_type'] = "danger"; // Set message type for error
  }
} else {
  // User does not exist
  $_SESSION['message'] = "Invalid username or password.";
  $_SESSION['message_type'] = "danger"; // Set message type for error
}

    $stmt->closeCursor(); // Close the cursor to free up the connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="css/web_style.css" />

    <title>Intern's Task House</title>
</head>

<body>
    <!-- Alert Box for messages -->
    <?php if (isset($_SESSION['message'])): ?>
    <div id="alertMessage" class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show position-fixed bottom-0 end-0 m-3" role="alert">
        <?= $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); // Clear the message after displaying ?>
    <?php unset($_SESSION['message_type']); // Clear the message type after displaying ?>
<?php endif; ?>
    <!-- Header (Green Bar) -->
    <div class="header-container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <img src="image/th_logo.png" alt="logo" style="width: 50px; height: 50px;" />
            <div class="logotext ms-2" style="font-size: 20px;">INTERN'S TASK HOUSE</div>
        </div>

        <!-- Navigation with buttons aligned in one row -->
        <nav class="nav-buttons">
            <a href="#main-section" class="btn btn-primary">Home</a>
            <a href="#documents-section" class="btn btn-primary">Documents</a>
            <a href="#about-us-section" class="btn btn-primary">About Us</a>

            <!-- Dropdown for Login -->
            <div class="dropdown">
                <button
                    class="btn btn-secondary dropdown-toggle"
                    type="button"
                    id="dropdownMenuButton"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    Login
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="Admin_registration.php">Admin</a></li>
                    <li><a class="dropdown-item" href="faci_log_in.php">Facilitator</a></li>
                    <li><a class="dropdown-item" href="intern_log.php">Interns</a></li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Main Section -->
    <div id="main-section" class="container-fluid d-flex align-items-center justify-content-center my-5">
        <div class="col-md-3 d-flex align-items-center justify-content-center" style="margin-left: 10%;">
            <img src="image/th_logo.png" alt="logo" class="img-fluid" style="width: 300%; height: auto;" />
        </div>

        <div class="col-md-6 d-flex flex-column justify-content-center" style="margin-left: 5%;">
            <p class="main-text interns">INTERN'S</p>
            <p class="main-text task-house">TASK HOUSE</p>
            <p class="capstone-title">Capstone title</p>
        </div>

        <div class="col-md-3 d-flex align-items-center"> <!-- Combined column for separator and abstract -->
            <div class="col-md-1 d-flex justify-content-center align-items-center" style="margin-right: -0%;">
                <div class="separator"></div>
            </div>

            <div class="col-md-2" style="margin-left: 2px;">
                <p class="abstract-title">ABSTRACT</p>
                <div class="lorem-container">
                    <p>
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                        when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                        It has survived not only five centuries but also the leap into electronic typesetting,
                        remaining essentially unchanged.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div id="documents-section" class="documents-section mt-4">
        <div class="card card-body">
            <div class="line"></div> <!-- Line above the heading -->
            <h4>Documents</h4>

            <ul>
                <li><a href="#">Internship Guidelines</a></li>
                <li><a href="#">Project Templates</a></li>
                <li><a href="#">Monthly Reports</a></li>
                <li><a href="#">Capstone Documentation</a></li>
            </ul>
        </div>
    </div>

    <!-- About Us Section -->
    <div id="about-us-section" class="about-us-section my-5">
        <h4 class="text-center">About Us</h4>
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="image/image2.jpg" alt="Description 1" class="img-fluid" onclick="openModal()" />
                <p>Description for Image 1</p>
            </div>
            <div class="col-md-3 text-center">
                <img src="image/image1.png" alt="Description 2" class="img-fluid" onclick="openModal()" />
                <p>Description for Image 2</p>
            </div>
            <div class="col-md-3 text-center">
                <img src="image/image3.jpg" alt="Description 3" class="img-fluid" onclick="openModal()" />
                <p>Description for Image 3</p>
            </div>
            <div class="col-md-3 text-center">
                <img src="image/image4.jpg" alt="Description 4" class="img-fluid" onclick="openModal()" />
                <p>Description for Image 4</p>
            </div>
        </div>
    </div>

    <!-- Developer Modal -->
    <div id="imageModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <img src="image/442010812_HEADPHONES_AVATAR_3D_400px.gif" alt="Developer" class="profile-image">
            </div>
            <h2>Developer Login</h2>
            <form id="loginForm" method="POST" action=""> <!-- Form action should point to the same script -->
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>  

    <!-- Footer (Green Bar) -->
    <div class="footer-container">
        <p>© 2024 Intern's Task House. All rights reserved.</p>
    </div>

    <script>
        function openModal() {
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
           // Check if the alert message exists
        var alertMessage = document.getElementById('alertMessage');
        if (alertMessage) {
            // Set a timeout to hide the alert after 5 seconds
            setTimeout(function() {
                alertMessage.classList.remove('show'); // Remove the show class to fade out
                alertMessage.classList.add('fade'); // Optionally add fade class
            }, 5000); // 5000 milliseconds = 5 seconds
        }
    </script>
</body>
</html>
