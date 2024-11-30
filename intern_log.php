<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Start the session
require('db_Taskhouse/Admin_connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $InternID = $_POST['InternID'];
  $InternPass = $_POST['InternPass'];

  // Fetch the user from the database using PDO
  $sql = "SELECT * FROM intacc WHERE internID = :InternID";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':InternID', $InternID);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) { // Check if the user was found
      // Verify password
      if ($InternPass === $user['InternPass']) {
          // Store user information in session
          $_SESSION['internID'] = $InternID; // Ensure this matches your session key
          header("Location: intern_page.php");
          exit();
      } else {
        echo '<div class="custom-alert alert-error">Password does not match.</div>';
      }
  } else {
     echo '<div class="custom-alert alert-error">No user found with the provided InternID.</div>';
  }
}

?>





<!DOCTYPE html>
<html lang="en">
<head>
  <title>TH_INTERN LOG-IN</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/loginForms.css">
</head>
<body>
<div class="login-container">
  <div class="login-content">
    <div class="card">
      <div class="card-body p-4 p-lg-5 text-black">
        <form method="POST" action="">
          <span class="s-intern">INTERN'S</span>
          <span class="s-TH">TASK HOUSE</span>
          <h1 class="fw-normal mb-3 pb-3">INTERN LOG-IN</h1>
          <div class="form-outline mb-4">
            <input type="text" id="form2Example17" name="InternID" class="form-control form-control-lg" />
            <label class="form-label" for="form2Example17">Intern ID</label>
          </div>
          <div class="form-outline mb-4">
            <input type="password" id="form2Example27" name="InternPass" class="form-control form-control-lg" />
            <label class="form-label" for="form2Example27">Password</label>
          </div>
          <button name="login" class="btn btn-green btn-lg btn-block" type="submit">Login</button>
          <a href="index.html" class="btn btn-lg btn-block btn-orange">Back to Web</a>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Character image positioned on the left -->
  <div class="character-image">
    <img src="image/3d-casual-life-young-man-pointing-at-smartphone.png" alt="Character" />
  </div>
  
  <!-- Logo at bottom right -->
  <div class="logo-container">
    <img src="image/maninlogo__1_-removebg-preview.png" alt="Logo" class="document-logo"/>
  </div>
</div>
</body>
</html>
