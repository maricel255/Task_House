<?php
require('Admin_connection.php');
session_start(); // Start the session

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $InternID = $_POST['InternID'];
    $InternPass = $_POST['InternPass'];

    // Fetch the user from the database using PDO
    $sql = "SELECT * FROM intacc WHERE InternID = :InternID";
    $stmt = $conn->prepare($sql); // Prepare the statement with PDO
    $stmt->bindParam(':InternID', $InternID); // Bind the parameter using bindParam
    $stmt->execute(); // Execute the statement
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user as an associative array

    // Debugging lines (remove these in production)
    // echo "SQL Query: $sql with InternID: $InternID<br>";
    // echo "User found.<br>"; // Debugging line

    if ($user) { // Check if the user was found
        // Verify password
        if ($InternPass === $user['InternPass']) { // Compare passwords
            // Store user information in session
            $_SESSION['internID'] = $InternID;
            // Redirect to the dashboard or next page
            header("Location: intern_page.php");
            exit();
        } else {
            echo "<script>alert('Password does not match.');</script>"; // Display an alert for password mismatch
        }
    } else {
        echo "<script>alert('No user found with the provided InternID.');</script>"; // Alert for user not found
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intern</title>
</head>
<body>
    

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Task House - Intern Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/LoginForms.css"> <!-- Same CSS file -->
</head>
<body style="background-color:linear-gradient(to right, rgb(182, 244, 146), rgb(51, 139, 147));" class="abody">
<section class="vh-100">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
        <div class="card" style="border-radius: 1rem;">
          <div class="row g-0">
            <div class="col-md-6 col-lg-5 d-none d-md-block">
              <img src="image/th_logo.png" alt="Intern Login" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" width="500" height="600"/>
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">
                <form method="POST" action="">
                  <span class="s-intern">INTERN'S</span>
                  <span class="s-TH">TASK HOUSE</span>
                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">INTERN LOG-IN</h5>
                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="text" id="form2Example17" name="InternID" class="form-control form-control-lg" />
                    <label class="form-label" for="form2Example17">Intern ID</label>
                  </div>
                  <div data-mdb-input-init class="form-outline mb-4">
                    <input type="password" id="form2Example27" name="InternPass" class="form-control form-control-lg" />
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
<script src="Intern_script.js"></script>
</body>
</html>
