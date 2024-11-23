<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require('db_Taskhouse/Admin_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Uname = $_POST['Uname'];
    $Upass = $_POST['Upass'];

    try {
      // Prepare the SQL statement
      $sql = "SELECT * FROM users WHERE Uname = :uname"; // Use a prepared statement with a placeholder
      $stmt = $conn->prepare($sql); // Prepare the statement
      
      // Execute the statement with the actual value for the placeholder
      $stmt->execute(['uname' => $Uname]);
  
      // Fetch the user data if it exists
      if ($stmt->rowCount() > 0) {
          $user = $stmt->fetch(); // Fetch the user data
  
          // Check if the password matches
          if ($Upass === $user['Upass']) {
              $_SESSION['Uname'] = $Uname;
              echo "<div class='message-box' id='message-box'>
                      <p>Login successful! Welcome, " . $_SESSION['Uname'] . "!</p>
                    <form action='Admin_Admin_1.php' method='post'>
                    <button type='submit'>OK</button>
                </form>
                    </div>";
          } else {
              echo "<script>alert('Invalid password.');</script>";
          }
      } else {
          echo "<script>alert('Invalid username and password.');</script>";
      }
  } catch (PDOException $e) {
      echo "Error: " . $e->getMessage(); // Handle any errors that may occur
  }
  
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title>TH_ADMIN LOG-IN</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/LoginForms.css"> <!-- Same CSS file -->
</head>
<body style="background-color:linear-gradient(to right, rgb(182, 244, 146), rgb(51, 139, 147));" class="abody">
<section class="vh-100" >
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
        <div class="card" style="border-radius: 1rem;">
          <div class="row g-0">
            <div class="col-md-6 col-lg-5 d-none d-md-block">
              <img src="image/th_logo.png" alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" width="500" height="600" position="center"/>
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">

                <!-- Single form element -->
                <form method="POST" action="">
                  <span class="s-intern">INTERN'S</span>

                  <span class="s-TH">TASKHOUSE <br></span>
                  
                  <h1 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">ADMIN LOG-IN</h1>

                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="text" id="form2Example17" name="Uname" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example17">Username</label>
                  </div>

                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="password" id="form2Example27" name="Upass" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example27">Password</label>
                  </div>

                  <button name="login" data-mdb-button-init data-mdb-ripple-init class="btn btn-green btn-lg btn-block" type="submit">
                Login
              </button>
                  <a href="index.html" class="btn btn-lg btn-block btn-orange">Back to Web</a>


                </form> 

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="Admin_script.js"></script>
</body>
</html>
