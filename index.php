<?php
session_start(); // Start a session

// Include your database connection file
include 'Admin_connection.php'; // Ensure this file contains your DB connection code

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
    if ($stmt->rowCount() > 0) {
        // Fetch the password
        $hashedPassword = $stmt->fetchColumn(); // Fetch the first column from the result

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Successful login
            $_SESSION['message'] = "Login successful!"; // Set success message
            echo "<script>alert('Login successful!'); ";
            exit(); // Ensure no further code is executed after redirection
          
        } else {
            // Invalid password
            $_SESSION['message'] = "Invalid username or password.";
            echo "<script>alert('Invalid username or password.');</script>";


        }
    } else {
        // User does not exist
        $_SESSION['message'] = "Invalid username or password.";
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
              when an unknown printefdsafdfafffffffffffffffdafsfadfdafadfr took a galley of type and scrambled it to make a type specimen book.
              It has survived not only five centuries but also the leap into electronic typesetting,
              remaining essentially unchanged.
              Lorem Ipsum is simply dummy text of the printing and typesetting industry.
              Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
              when an unknown printef.
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
    
    <!-- About Us Section -->
    <div id="about-us-section" class="about-us-section my-5">
      <h4 class="text-center">About Us</h4>
      <div class="row">
        <div class="col-md-3 text-center">
          <img src="image/image2.jpg" alt="Description 1"  class="img-fluid" onclick="openModal()" />
          <p>Description for Image 1</p>
        </div>
        <div class="col-md-3 text-center">
          <img src="image/image1.png" alt="Description 2" class="img-fluid" onclick="openModal()" />
          <p>Description for Image 2</p>
       </div>
   

        <div class="col-md-3 text-center">
          <img src="image/image3.jpg" alt="Description 3"  class="img-fluid" onclick="openModal()" />
          <p>Description for Image 3</p>
        </div>
        <div class="col-md-3 text-center">
          <img src="image/image4.jpg" alt="Description 4"  class="img-fluid" onclick="openModal()" />
          <p>Description for Image 4</p>
        </div>
      </div>
    </div>
        <!-- debelopers modal -->

        <div id="imageModal" class="modal" style="display: none;">
          <div class="modal-content">
              <span class="close" onclick="closeModal()">&times;</span>
              <div class="modal-header">
                  <img src="image/442010812_HEADPHONES_AVATAR_3D_400px.gif" alt="Developer" class="profile-image"> <!-- Change src to your image path -->
              </div>
              <h2>Developer Login</h2>
              <form id="loginForm" method="POST" action=" "> <!-- Change action to your login processing script -->
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
    <div class="footer">
      &copy; 2024 Intern's Task House - All Rights Reserved
    </div>

    <!-- Bootstrap JS -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
    ></script>
  </body>
  <script>
    function openModal() {
        document.getElementById('imageModal').style.display = 'block';
        document.body.classList.add('modal-open'); // Disable scrolling

    }

    function closeModal() {
        document.getElementById('imageModal').style.display = 'none';
    }

    // Close the modal when clicking outside of the modal content
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>
</html>
