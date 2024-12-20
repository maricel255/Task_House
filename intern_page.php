<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Start the session
require('db_Taskhouse/Admin_connection.php');




// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start Kyle
$currentProfileImage = null;
if (isset($_SESSION['internID'])) {
    $sql = "SELECT profile_image FROM intacc WHERE internID = :internID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':internID', $_SESSION['internID']);
    $stmt->execute();
    $currentProfileImage = $stmt->fetchColumn();
}
// End Kyle

// Add the new code here ↓
$profileData = null;
if (isset($_SESSION['internID'])) {
    // Fetch existing profile data
    $fetchProfileSql = "SELECT * FROM profile_information WHERE internID = :internID";
    $fetchStmt = $conn->prepare($fetchProfileSql);
    $fetchStmt->bindParam(':internID', $_SESSION['internID']);
    $fetchStmt->execute();
    $profileData = $fetchStmt->fetch(PDO::FETCH_ASSOC);
}

// Get adminID from intacc table
$internID = $_SESSION['internID'];
$sqlFetchAdminID = "SELECT adminID FROM intacc WHERE internID = :internID";
$stmtFetch = $conn->prepare($sqlFetchAdminID);
$stmtFetch->bindParam(':internID', $internID);
$stmtFetch->execute();
$adminID = $stmtFetch->fetchColumn();

// For debugging
error_log("InternID: " . $internID);
error_log("AdminID: " . $adminID);

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

    $query = "SELECT faciID FROM facacc WHERE adminID = :adminID";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':adminID', $adminID, PDO::PARAM_INT);
    $stmt->execute();
    $faciIDs = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows
// First, retrieve the faciID based on the internID (assuming internID is available)
$sqlGetFaciID = "SELECT faciID FROM profile_information WHERE internID = :internID LIMIT 1";
$stmtGetFaciID = $conn->prepare($sqlGetFaciID);
$stmtGetFaciID->bindParam(':internID', $internID, PDO::PARAM_INT);
$stmtGetFaciID->execute();

// Fetch the faciID from the profile_information table
$faciID = $stmtGetFaciID->fetchColumn();

// Handle image upload separately
// Start Kyle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    try {
        $uploadDir = 'uploaded_files/';
        
        // Check if the upload directory exists, if not, create it
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['profile_image'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate the file type
        if (!in_array($fileExtension, $allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        // Create a unique file name
        $fileName = 'profile_' . $_SESSION['internID'] . '_' . time() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Update the database with the new image path
            $sql = "UPDATE intacc SET profile_image = :profile_image WHERE internID = :internID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':profile_image', $fileName);
            $stmt->bindParam(':internID', $_SESSION['internID']);
            
            // Execute the statement and check for success
            if ($stmt->execute()) {
                $_SESSION['upload_status'] = 'success'; // Set session variable for success
            } else {
                throw new Exception('Database update failed');
            }
        } else {
            throw new Exception('Failed to move uploaded file');
        }
    } catch (Exception $e) {
        $_SESSION['upload_status'] = 'error'; // Set session variable for error
        $_SESSION['upload_message'] = $e->getMessage(); // Store error message
    }
    
    // Redirect back to the original page
    header("Location: intern_page.php");
    exit; // Ensure no further output is sent
}

// Fetch the current profile image to display
$currentProfileImage = null;
if (isset($_SESSION['internID'])) {
    $sql = "SELECT profile_image FROM intacc WHERE internID = :internID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':internID', $_SESSION['internID']);
    $stmt->execute();
    $currentProfileImage = $stmt->fetchColumn();
}



// Keep your existing password update code separate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_credentials'])) {
    $internID = $_SESSION['internID'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    try {
        // First verify the current password
        $sql = "SELECT internPass FROM intacc WHERE internID = :internID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':internID', $internID);
        $stmt->execute();
        $storedPassword = $stmt->fetchColumn();

        // Direct comparison since passwords might not be hashed in your database
        if ($currentPassword !== $storedPassword) {
            $alertMessage = "Current password is incorrect";
        }
        // Verify new password requirements
        else if (strlen($newPassword) < 6) {
            $alertMessage = "Password must be at least 6 characters long";
        }
        else if ($newPassword !== $confirmPassword) {
            $alertMessage = "New passwords do not match";
        }
        else {
            // Update the password (without hashing since your DB uses plain text)
            $updateSql = "UPDATE intacc SET internPass = :password WHERE internID = :internID";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':password', $newPassword);
            $updateStmt->bindParam(':internID', $internID);
            
            if ($updateStmt->execute()) {
                $alertMessage = "Password updated successfully!";
                echo "<script>
                    setTimeout(function() {
                        document.getElementById('credentialsModal').style.display = 'none';
                    }, 2000);
                </script>";
            } else {
                $alertMessage = "Failed to update password";
            }
        }

    } catch (PDOException $e) {
        $alertMessage = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $alertMessage = $e->getMessage();
    }
}
// End Kyle

// Handle form submissions for logging in, break, back to work, and logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alertMessage = ''; // Initialize variable for alert message

    date_default_timezone_set('Asia/Manila');
    $currentDate = date('Y-m-d');
    $currentTime = date('Y-m-d H:i:s');
    $task = isset($_POST['task']) ? trim($_POST['task']) : '';

    // Fetch the faciID for the intern based on internID
    $sqlGetFaciID = "SELECT faciID FROM profile_information WHERE internID = :internID LIMIT 1";
    $stmtGetFaciID = $conn->prepare($sqlGetFaciID);
    $stmtGetFaciID->bindParam(':internID', $internID, PDO::PARAM_INT);
    $stmtGetFaciID->execute();
    $faciID = $stmtGetFaciID->fetchColumn();

    // Only perform actions if buttons are clicked
if (isset($_POST['login-btn'])) {
    // Check if faciID is provided
    if (empty($faciID)) {
        $alertMessage = "Please select a Company ID from your profile information.";
    } else {
        // Check if there's an existing login record for today
        $sqlCheckLogin = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate";
        $stmtCheckLogin = $conn->prepare($sqlCheckLogin);
        $stmtCheckLogin->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmtCheckLogin->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
        $stmtCheckLogin->execute();

        if ($stmtCheckLogin->rowCount() == 0) {
            // No login record for today, so insert a new one
            $sqlInsert = "INSERT INTO time_logs (internID, adminID, faciID, task, login_time) VALUES (:internID, :adminID, :faciID, :task, :loginTime)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bindParam(':internID', $internID, PDO::PARAM_INT);
            $stmtInsert->bindParam(':adminID', $adminID, PDO::PARAM_INT);
            $stmtInsert->bindParam(':faciID', $faciID, PDO::PARAM_INT); // Bind faciID
            $stmtInsert->bindParam(':task', $task, PDO::PARAM_STR);
            $stmtInsert->bindParam(':loginTime', $currentTime, PDO::PARAM_STR);

            if ($stmtInsert->execute()) {
                $alertMessage = "Login time recorded successfully.";
            } else {
                $alertMessage = "Error recording login time.";
            }
        } else {
            $alertMessage = "You have already timed in today.";
        }
    }
}


if (isset($_POST['break-btn'])) {
    // Check if there's a login record for today
    $sqlCheckBreak = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate AND break_time IS NULL";
    $stmtCheckBreak = $conn->prepare($sqlCheckBreak);
    $stmtCheckBreak->bindParam(':internID', $internID, PDO::PARAM_INT);
    $stmtCheckBreak->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
    $stmtCheckBreak->execute();

    // Check if the status is declined
    $sqlCheckStatus = "SELECT status FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate";
    $stmtCheckStatus = $conn->prepare($sqlCheckStatus);
    $stmtCheckStatus->bindParam(':internID', $internID, PDO::PARAM_INT);
    $stmtCheckStatus->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
    $stmtCheckStatus->execute();
    $status = $stmtCheckStatus->fetchColumn();

    // Only block if status is explicitly "Declined"
    if ($status === 'Declined') {
        $alertMessage = "Your request has been declined, you can't click this button.";
    } else if ($stmtCheckBreak->rowCount() == 0) {
        $alertMessage = "Please log in first before taking a break.";
    } else {
        // Proceed with recording the break time
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
    }
}

if (isset($_POST['back-to-work-btn'])) {
    // Check if the status is declined
    $sqlCheckStatus = "SELECT status FROM time_logs WHERE internID = :internID AND DATE(login_time) = :currentDate";
    $stmtCheckStatus = $conn->prepare($sqlCheckStatus);
    $stmtCheckStatus->bindParam(':internID', $internID, PDO::PARAM_INT);
    $stmtCheckStatus->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
    $stmtCheckStatus->execute();
    $status = $stmtCheckStatus->fetchColumn();

    // Only block if status is explicitly "Declined"
    if ($status === 'Declined') {
        $alertMessage = "Your request has been declined, you can't click this button.";
    } else {
        // Proceed with back to work functionality
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
}

// Check if the task form is submitted
if (isset($_POST['submitTask'])) {
    $internID = $_POST['internID'];

    // Validate the tasks input
    if (!isset($_POST['tasks']) || count($_POST['tasks']) < 1 || count($_POST['tasks']) > 10) {
        $alertMessage = "Please input a Task before logging out.";
    } else {
        $selectedTasks = $_POST['tasks'];
        $todayDate = date('Y-m-d'); // Get today's date

        // Check if the status is declined
        $sqlCheckStatus = "SELECT status FROM time_logs WHERE internID = :internID AND DATE(login_time) = :todayDate";
        $stmtCheckStatus = $conn->prepare($sqlCheckStatus);
        $stmtCheckStatus->bindParam(':internID', $internID, PDO::PARAM_STR);
        $stmtCheckStatus->bindParam(':todayDate', $todayDate, PDO::PARAM_STR);
        $stmtCheckStatus->execute();
        $status = $stmtCheckStatus->fetchColumn();

        if ($status == 'Declined') {
            $alertMessage = "Your request has been declined, you can't click this button.";
        } else {
            // Proceed with task logging functionality
            $sqlCheckLoginTime = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(login_time) = :todayDate AND (back_to_work_time IS NOT NULL OR login_time IS NOT NULL)";
            $stmtCheckLoginTime = $conn->prepare($sqlCheckLoginTime);
            $stmtCheckLoginTime->bindParam(':internID', $internID, PDO::PARAM_STR);
            $stmtCheckLoginTime->bindParam(':todayDate', $todayDate, PDO::PARAM_STR);
            $stmtCheckLoginTime->execute();

            if ($stmtCheckLoginTime->rowCount() == 0) {
                $alertMessage = "Cannot proceed to log out. You did not log in or return to work today. Contact your HR/Manager to update your time_logs";
            } else {
                // Check if a record exists for the intern for today with logout time already set
                $sqlCheckTask = "SELECT * FROM time_logs WHERE internID = :internID AND DATE(logout_time) = :todayDate";
                $stmtCheckTask = $conn->prepare($sqlCheckTask);
                $stmtCheckTask->bindParam(':internID', $internID, PDO::PARAM_STR);
                $stmtCheckTask->bindParam(':todayDate', $todayDate, PDO::PARAM_STR);
                $stmtCheckTask->execute();

                if ($stmtCheckTask->rowCount() > 0) {
                    $alertMessage = "Already recorded the task and logout for today.";
                } else {
                    // Check for an existing task without logout_time for today
                    $sqlCheckExistingTask = "SELECT * FROM time_logs WHERE internID = :internID AND logout_time IS NULL AND DATE(back_to_work_time) = :todayDate";
                    $stmtCheckExistingTask = $conn->prepare($sqlCheckExistingTask);
                    $stmtCheckExistingTask->bindParam(':internID', $internID, PDO::PARAM_STR);
                    $stmtCheckExistingTask->bindParam(':todayDate', $todayDate, PDO::PARAM_STR);
                    $stmtCheckExistingTask->execute();

                    if ($stmtCheckExistingTask->rowCount() > 0) {
                        $currentTime = date('Y-m-d H:i:s'); // Get the current time
                        $taskString = implode(", ", $selectedTasks); // Convert array to string

                        $sqlUpdateTask = "UPDATE time_logs SET task = :task, logout_time = :logoutTime WHERE internID = :internID AND logout_time IS NULL AND DATE(back_to_work_time) = :todayDate";
                        $stmtUpdateTask = $conn->prepare($sqlUpdateTask);
                        $stmtUpdateTask->bindParam(':internID', $internID, PDO::PARAM_STR);
                        $stmtUpdateTask->bindParam(':task', $taskString, PDO::PARAM_STR);
                        $stmtUpdateTask->bindParam(':logoutTime', $currentTime, PDO::PARAM_STR);
                        $stmtUpdateTask->bindParam(':todayDate', $todayDate, PDO::PARAM_STR);

                        if ($stmtUpdateTask->execute()) {
                            $alertMessage = "Task recorded successfully and logout time captured.";
                        } else {
                            $alertMessage = "Error updating task.";
                        }
                    } else {
                        $currentTime = date('Y-m-d H:i:s');
                        $taskString = implode(", ", $selectedTasks);

                        $sqlInsertTask = "INSERT INTO time_logs (internID, task, back_to_work_time, logout_time, faciID) VALUES (:internID, :task, :backToWorkTime, :logoutTime, :faciID)";
                        $stmtInsertTask = $conn->prepare($sqlInsertTask);
                        $stmtInsertTask->bindParam(':internID', $internID, PDO::PARAM_STR);
                        $stmtInsertTask->bindParam(':task', $taskString, PDO::PARAM_STR);
                        $stmtInsertTask->bindParam(':backToWorkTime', $todayDate, PDO::PARAM_STR); // Record today's date as back_to_work_time
                        $stmtInsertTask->bindParam(':logoutTime', $currentTime, PDO::PARAM_STR);
                        $stmtInsertTask->bindParam(':faciID', $faciID, PDO::PARAM_INT);

                        if ($stmtInsertTask->execute()) {
                            $alertMessage = "Task recorded successfully and logout time captured.";
                        } else {
                            $alertMessage = "Error recording task.";
                        }
                    }
                }
            }
        }
    }
}




  
// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert-btn'])) {
    try {
        // Get adminID from intacc table
        $sqlFetchAdminID = "SELECT adminID FROM intacc WHERE internID = :internID";
        $stmtFetch = $conn->prepare($sqlFetchAdminID);
        $stmtFetch->bindParam(':internID', $_SESSION['internID']);
        $stmtFetch->execute();
        $adminID = $stmtFetch->fetchColumn();

        // Insert new profile
        $sql = "INSERT INTO profile_information (
            internID, adminID, first_name, middle_name, last_name, 
            course_year_sec, school, gender, age, current_address, provincial_address,
            tel_no, mobile_no, birth_place, birth_date, religion,
            email, civil_status, citizenship, hr_manager, faciID, 
           start_shift, end_shift, required_hours, date_start, date_end,
            company, company_address, father_name, father_occupation,
            mother_name, mother_occupation, blood_type, height,
            weight, health_problems, elementary_school, elementary_year_graduated,
            elementary_honors, secondary_school, secondary_year_graduated,
            secondary_honors, college, college_year_graduated, college_honors,
            company_name, position, inclusive_date, company_address_work_experience,
            skills, ref_name, ref_position, ref_address, ref_contact,
            emergency_name, emergency_relationship, emergency_address, emergency_contact_no
        ) VALUES (
            :internID, :adminID, :firstName, :middleName, :lastName,
            :courseYearSec, :school, :gender, :age, :currentAddress, :provincialAddress,
            :telNo, :mobileNo, :birthPlace, :birthDate, :religion,
            :email, :civilStatus, :citizenship, :hrManager, :faciID,
             :startShift, :endShift, :reqHrs, :dateStart, :dateEnd,
            :company, :companyAddress, :fatherName, :fatherOccupation,
            :motherName, :motherOccupation, :bloodType, :height,
            :weight, :healthProblems, :elementarySchool, :elementaryYearGraduated,
            :elementaryHonors, :secondarySchool, :secondaryYearGraduated,
            :secondaryHonors, :college, :collegeYearGraduated, :collegeHonors,
            :companyName, :position, :inclusiveDate, :companyAddressWorkExperience,
            :skills, :refName, :refPosition, :refAddress, :refContact,
            :emergencyName, :emergencyRelationship, :emergencyAddress, :emergencyContactNo
        )";

        $stmt = $conn->prepare($sql);
        
        // Bind all parameters
        $stmt->bindParam(':internID', $_SESSION['internID']);
        $stmt->bindParam(':adminID', $adminID);
        $stmt->bindParam(':firstName', $_POST['firstName']);
        $stmt->bindParam(':middleName', $_POST['middleName']);
        $stmt->bindParam(':lastName', $_POST['lastName']);
        $stmt->bindParam(':courseYearSec', $_POST['courseYearSec']);
        $stmt->bindParam(':school', $_POST['school']);
        $stmt->bindParam(':gender', $_POST['gender']);
        $stmt->bindParam(':age', $_POST['age']);
        $stmt->bindParam(':currentAddress', $_POST['currentAddress']);
        $stmt->bindParam(':provincialAddress', $_POST['provincialAddress']);
        $stmt->bindParam(':telNo', $_POST['telNo']);
        $stmt->bindParam(':mobileNo', $_POST['mobileNo']);
        $stmt->bindParam(':birthPlace', $_POST['birthPlace']);
        $stmt->bindParam(':birthDate', $_POST['birthDate']);
        $stmt->bindParam(':religion', $_POST['religion']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':civilStatus', $_POST['civilStatus']);
        $stmt->bindParam(':citizenship', $_POST['citizenship']);
        $stmt->bindParam(':hrManager', $_POST['hrManager']);
        $stmt->bindParam(':faciID', $_POST['faciID']);
        
        $stmt->bindParam(':startShift', $_POST['startShift']);
        $stmt->bindParam(':endShift', $_POST['endShift']);
        $stmt->bindParam(':reqHrs', $_POST['reqHrs']);
        $stmt->bindParam(':dateStart', $_POST['dateStart']);
        $stmt->bindParam(':dateEnd', $_POST['dateEnd']);
        $stmt->bindParam(':company', $_POST['company']);
        $stmt->bindParam(':companyAddress', $_POST['companyAddress']);
        $stmt->bindParam(':fatherName', $_POST['fatherName']);
        $stmt->bindParam(':fatherOccupation', $_POST['fatherOccupation']);
        $stmt->bindParam(':motherName', $_POST['motherName']);
        $stmt->bindParam(':motherOccupation', $_POST['motherOccupation']);
        $stmt->bindParam(':bloodType', $_POST['bloodType']);
        $stmt->bindParam(':height', $_POST['height']);
        $stmt->bindParam(':weight', $_POST['weight']);
        $stmt->bindParam(':healthProblems', $_POST['healthProblems']);
        $stmt->bindParam(':elementarySchool', $_POST['elementarySchool']);
        $stmt->bindParam(':elementaryYearGraduated', $_POST['elementaryYearGraduated']);
        $stmt->bindParam(':elementaryHonors', $_POST['elementaryHonors']);
        $stmt->bindParam(':secondarySchool', $_POST['secondarySchool']);
        $stmt->bindParam(':secondaryYearGraduated', $_POST['secondaryYearGraduated']);
        $stmt->bindParam(':secondaryHonors', $_POST['secondaryHonors']);
        $stmt->bindParam(':college', $_POST['college']);
        $stmt->bindParam(':collegeYearGraduated', $_POST['collegeYearGraduated']);
        $stmt->bindParam(':collegeHonors', $_POST['collegeHonors']);
        $stmt->bindParam(':companyName', $_POST['companyName']);
        $stmt->bindParam(':position', $_POST['position']);
        $stmt->bindParam(':inclusiveDate', $_POST['inclusiveDate']);
        $stmt->bindParam(':companyAddressWorkExperience', $_POST['companyAddressWorkExperience']);
        $stmt->bindParam(':skills', $_POST['skills']);
        $stmt->bindParam(':refName', $_POST['refName']);
        $stmt->bindParam(':refPosition', $_POST['refPosition']);
        $stmt->bindParam(':refAddress', $_POST['refAddress']);
        $stmt->bindParam(':refContact', $_POST['refContact']);
        $stmt->bindParam(':emergencyName', $_POST['emergencyName']);
        $stmt->bindParam(':emergencyRelationship', $_POST['emergencyRelationship']);
        $stmt->bindParam(':emergencyAddress', $_POST['emergencyAddress']);
        $stmt->bindParam(':emergencyContactNo', $_POST['emergencyContactNo']);

        if ($stmt->execute()) {
            // Use the existing alert box styling
            echo "<div class='alert-box alert-success'>";
            echo "<span>Profile information saved successfully!</span>";
            echo "<button class='close-btn' onclick='this.parentElement.style.display=\"none\"'>×</button>";
            echo "</div>";
            
            // Refresh the page after a short delay
            echo "<script>
                setTimeout(function() {
                    window.location.href = '" . $_SERVER['PHP_SELF'] . "';
                }, 2000);
            </script>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert-box alert-error'>";
        echo "<span>Error: " . $e->getMessage() . "</span>";
        echo "<button class='close-btn' onclick='this.parentElement.style.display=\"none\"'>×</button>";
        echo "</div>";
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


// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_credentials'])) {
    $internID = $_SESSION['internID'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    try {
        // Handle image upload if a file was submitted
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploaded_files/profile_images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');
            }

            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['profile_image']['size'] > $maxFileSize) {
                throw new Exception('File size too large. Maximum size is 5MB.');
            }

            $fileName = 'profile_' . $internID . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                // Update database with new image path
                $updateImageSql = "UPDATE intacc SET profile_image = :profile_image WHERE internID = :internID";
                $updateImageStmt = $conn->prepare($updateImageSql);
                $updateImageStmt->bindParam(':profile_image', $targetPath);
                $updateImageStmt->bindParam(':internID', $internID);
                $updateImageStmt->execute();
            }
        }

        // First verify the current password
        $sql = "SELECT internPass FROM intacc WHERE internID = :internID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':internID', $internID);
        $stmt->execute();
        $storedPassword = $stmt->fetchColumn();

        // Direct comparison since passwords might not be hashed in your database
        if ($currentPassword !== $storedPassword) {
            $alertMessage = "Current password is incorrect";
        }
        // Verify new password requirements
        else if (strlen($newPassword) < 6) {
            $alertMessage = "Password must be at least 6 characters long";
        }
        else if ($newPassword !== $confirmPassword) {
            $alertMessage = "New passwords do not match";
        }
        else {
            // Update the password (without hashing since your DB uses plain text)
            $updateSql = "UPDATE intacc SET internPass = :password WHERE internID = :internID";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':password', $newPassword);
            $updateStmt->bindParam(':internID', $internID);
            
            if ($updateStmt->execute()) {
                $alertMessage = "Password updated successfully!";
                echo "<script>
                    setTimeout(function() {
                        document.getElementById('credentialsModal').style.display = 'none';
                    }, 2000);
                </script>";
            } else {
                $alertMessage = "Failed to update password";
            }
        }

    } catch (PDOException $e) {
        $alertMessage = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $alertMessage = $e->getMessage();
    }
}


    // Query to get time logs for the current intern
    $stmt = $conn->prepare("SELECT * FROM time_logs WHERE internID = :internID");
    $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all the time logs
    $timeLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the required hours from the profile data
    $requiredHours = $profileData['required_hours'] ?? 0; // Default to 0 if no value is found

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intern</title>
    <link rel="stylesheet" href="css/Intern_styles.css">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    
</head>
<body>
        <!-- Display alert message if exists -->
        <?php
            if (isset($_SESSION['alertMessage'])) {
                $alertClass = isset($_SESSION['alertType']) && $_SESSION['alertType'] === 'error' ? 'alert-error' : 'alert-success';
                echo "<div class='alert-box {$alertClass}'>";
                echo "<span>" . $_SESSION['alertMessage'] . "</span>";
                echo "<button class='close-btn' onclick='this.parentElement.style.display=\"none\"'>×</button>";
                echo "</div>";

                // Clear the alert message
                unset($_SESSION['alertMessage']);
                unset($_SESSION['alertType']);
            }
        ?>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar hide-content">
                    <div class="user-info">
                    <!-- Start Kyle-->
                    <div class="profile-image"> 
                    <img id="sidebarImage" src="<?php echo $currentProfileImage ? 'uploaded_files/' . htmlspecialchars($currentProfileImage) : 'image/USER_ICON.png'; ?>" alt="User Profile" class="user-icon">
                <!-- End Kyle-->
                    </div>
                   
                    <div class="user-details">
                        <p class="user-name"><?php echo htmlspecialchars($profileData['first_name'] ?? ''); ?> <?php echo htmlspecialchars($profileData['middle_name'] ?? ''); ?> <?php echo htmlspecialchars($profileData['last_name'] ?? ''); ?></p>
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
        
                <button onclick="openCredentialsModal()" class="settings-btn">
                    <img src="image/USER_ICON.png" alt="Settings" class="settings-icon">
                </button>
            <button class="logout-btn" onclick="logout()">
                <img src="image/logout.png" alt="logout icon" class="logout-icon">
                | LOG OUT
            </button>
        </div>
    </div>

 <!-- Start Kyle-->
    <div id="credentialsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeCredentialsModal()">&times;</span>
        <h2>My Profile </h2>

        <!-- Add image upload section -->
        <div class="image-upload-container">
            <img id="imagePreview" src="<?php echo $currentProfileImage ? 
            'uploaded_files/' . htmlspecialchars($currentProfileImage) : 
            'image/USER_ICON.png'; ?>" alt="Profile Preview" 
        style="width: 100px; height: 100px;">
            <form id="imageForm" method="POST" enctype="multipart/form-data">
                <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="display: none;" onchange="uploadImage()">
                <button type="button" class="choose-image-btn" onclick="document.getElementById('profileImageInput').click()">
                    Choose Image
                </button>
                
            </form>
        </div>

        <!-- Add this form after the image upload section -->
        <form id="credentialsForm" method="POST">
            <div class="form-group">
                <label>Intern Name:</label>
                <input type="text" id="internName" 
                    value="<?php echo htmlspecialchars($profileData['first_name'] ?? ''); ?> <?php echo htmlspecialchars($profileData['last_name'] ?? ''); ?>"
                    readonly 
                    style="background-color: #f5f5f5; border: 1px solid #ddd;">
            </div>
            <div class="form-group">
                <label>Current Password:</label>
                <input type="password" name="currentPassword" required>
            </div>
            <div class="form-group">
                <label>New Password:</label>
                <input type="password" name="newPassword" required>
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirmPassword" required>
            </div>
            <button type="submit" name="update_credentials">Update Password</button>
        </form>
    </div>
</div>
<!-- end Kyle-->

    <!-- Main Content -->
    <div class="content-section" id="dashboard">
    <div class="main-content">
        <div class="announcement-board">
            <h2>ANNOUNCEMENT BOARD</h2>
            <img src="image/announce.gif" alt="Announcement Image" class="anno-img">

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
                                            $fileName = basename($filePath);
                                            $imageUrl = "/uploaded_files/" . rawurlencode($fileName);
                                            echo '<a href="' . $imageUrl . '" target="_blank" class="pdf-link">View Image</a>';
                                          
                                      } elseif (strtolower($fileExtension) === 'pdf') {
                                          $fileName = basename($filePath);
                                          $pdfPath = "/uploaded_files/" . rawurlencode($fileName);
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
                    
                    <button type="submit" name="login-btn" class="login-btn">Time in</button>
                    <button type="button" name="logut-butn" class="" onclick="document.getElementById('taskModal').style.display='block'">Time out</button>
                    </form>
                      
                 </div>  
            </div>

            </div>
        </div>


<!-- Modal for entering task -->


<div class="modal-overlay" id="modalOverlay"></div>
<div id="taskModal" >
    
    <span class="cls" onclick="closeTaskModal()">&times;</span>

        <h2>Enter Task Before Logging Out</h2>
        <form id="taskForm" method="POST" action="" onsubmit="return validateTask()">
            <input type="hidden" name="internID" value="<?php echo htmlspecialchars($internID); ?>">

            <!-- Dropdown to select an existing task -->
            <label for="task">Select Existing Task:</label>
            <select name="tasks[]" >
                <option value="" disabled selected>Select a Task</option>
                
                <?php 
        // Query to get time logs for the current intern
        $stmt = $conn->prepare("SELECT * FROM time_logs WHERE internID = :internID");
        $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all the time logs
        $timeLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create an array to track unique tasks
        $uniqueTasks = [];

        if (!empty($timeLogs)) {
            foreach ($timeLogs as $log) {
                if (isset($log['task']) && !in_array($log['task'], $uniqueTasks)) { 
                    // Check if the task is unique before adding to the dropdown
                    $uniqueTasks[] = $log['task']; // Add task to the unique array
                    echo '<option value="' . htmlspecialchars($log['task']) . '">' . htmlspecialchars($log['task']) . '</option>';
                }
            }
        } else {
            echo '<option value="">No tasks available</option>';
        }
        ?>
            </select>

            <!-- Input for a new task -->
            <label for="new_task">Or Enter a New Task:</label>
            <input type="text" id="new_task" name="tasks[]" placeholder="Enter a new task">

            <button type="submit" name="submitTask">Submit Task</button>
        </form>

        <div id="taskMessage"><?php echo isset($alertMessage) ? htmlspecialchars($alertMessage) : ''; ?></div>
    
</div>


<!-- Modal for Profile Information -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>

        <!-- Single form for all categories -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="adminID" value="<?php echo htmlspecialchars($adminID); ?>">
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
                    <!-- First row with multiple form fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name:</label>
                            <input type="text" name="firstName" 
                                placeholder="e.g., John" 
                                value="<?php echo htmlspecialchars($profileData['first_name'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Middle Name:</label>
                            <input type="text" name="middleName" 
                                placeholder="e.g., A." 
                                value="<?php echo htmlspecialchars($profileData['middle_name'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?>>
                        </div>

                        <div class="form-group">
                            <label>Last Name:</label>
                            <input type="text" name="lastName" 
                                placeholder="e.g., Doe" 
                                value="<?php echo htmlspecialchars($profileData['last_name'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Course, Year, Sec.:</label>
                            <input type="text" name="courseYearSec" 
                                placeholder="e.g., BSCS 3A" 
                                value="<?php echo htmlspecialchars($profileData['course_year_sec'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>School:</label>
                            <input type="text" name="school" 
                                placeholder="e.g., University of Sample" 
                                value="<?php echo htmlspecialchars($profileData['school'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Gender:</label>
                            <?php if ($profileData): ?>
                                <input type="radio" name="gender" value="Male" 
                                    <?php echo ($profileData['gender'] === 'Male') ? 'checked' : ''; ?> 
                                    disabled> Male
                                <input type="radio" name="gender" value="Female" 
                                    <?php echo ($profileData['gender'] === 'Female') ? 'checked' : ''; ?> 
                                    disabled> Female
                            <?php else: ?>
                                <input type="radio" name="gender" value="Male" required> Male
                                <input type="radio" name="gender" value="Female" required> Female
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Second row with multiple form fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Age:</label>
                            <input type="number" name="age" 
                                placeholder="e.g., 20" 
                                min="0" max="100" 
                                value="<?php echo htmlspecialchars($profileData['age'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Current Address:</label>
                            <input type="text" name="currentAddress" 
                                placeholder="e.g., 123 Sample St., Sample City" 
                                value="<?php echo htmlspecialchars($profileData['current_address'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Provincial Address:</label>
                            <input type="text" name="provincialAddress" 
                                placeholder="e.g., Sample Province, Sample Country" 
                                value="<?php echo htmlspecialchars($profileData['provincial_address'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?>>
                        </div>

                        <div class="form-group">
                            <label>Tel. No.:</label>
                            <input type="text" name="telNo" 
                                placeholder="e.g., (02) 123-4567" 
                                value="<?php echo htmlspecialchars($profileData['tel_no'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?>>
                        </div>

                        <div class="form-group">
                            <label>Mobile No.:</label>
                            <input type="text" name="mobileNo" 
                                placeholder="e.g., 09123456789" 
                                pattern="[0-9]{11}" 
                                title="Enter an 11-digit mobile number" 
                                value="<?php echo htmlspecialchars($profileData['mobile_no'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Birth Place:</label>
                            <input type="text" name="birthPlace" 
                                placeholder="e.g., Sample City, Sample Country" 
                                value="<?php echo htmlspecialchars($profileData['birth_place'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>
                    </div>

                    <!-- Third row with multiple form fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label>Birth Date:</label>
                            <input type="date" name="birthDate" 
                                value="<?php echo htmlspecialchars($profileData['birth_date'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Religion:</label>
                            <input type="text" name="religion" 
                                placeholder="e.g., Catholic" 
                                value="<?php echo htmlspecialchars($profileData['religion'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?>>
                        </div>

                        <div class="form-group">
                            <label>Email Address:</label>
                            <input type="email" name="email" 
                                placeholder="e.g., sample@example.com" 
                                value="<?php echo htmlspecialchars($profileData['email'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Civil Status:</label>
                            <input type="text" name="civilStatus" 
                                placeholder="e.g., Single, Married" 
                                value="<?php echo htmlspecialchars($profileData['civil_status'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>

                        <div class="form-group">
                            <label>Citizenship:</label>
                            <input type="text" name="citizenship" 
                                placeholder="e.g., Filipino" 
                                value="<?php echo htmlspecialchars($profileData['citizenship'] ?? ''); ?>"
                                <?php echo $profileData ? 'readonly' : ''; ?> required>
                        </div>
                    </div>
                </div>
            </div>

               <!-- Company Details -->
<div id="companyDetails" class="profile-category">
    <h3>Company Details</h3>
    <div class="form-container">
        <div class="form-row">
            <div class="form-column">
                <label>HR/Manager:</label>
                <input type="text" name="hrManager" 
                    placeholder="e.g., John Smith"
                    value="<?php echo htmlspecialchars($profileData['hr_manager'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                
                    

                <label>Start Shift:</label>
                <input type="time" name="startShift" 
                    value="<?php echo htmlspecialchars($profileData['start_shift'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                    <label>Date Start:</label>
                <input type="date" name="dateStart" 
                    value="<?php echo htmlspecialchars($profileData['date_start'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                    <label>Company:</label>
                <input type="text" name="company" 
                    placeholder="e.g., Sample Corporation"
                    value="<?php echo htmlspecialchars($profileData['company'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

               
            </div>

            <div class="form-column">
                <label for="faciID">Company ID:</label>
                <select name="faciID" id="faciID" <?php echo $profileData ? 'disabled' : ''; ?>>
                    <option value="">Select Company ID</option>
                    <?php foreach ($faciIDs as $faci): ?>
                        <option value="<?php echo htmlspecialchars($faci['faciID']); ?>"
                            <?php echo (isset($profileData['faciID']) && $profileData['faciID'] == $faci['faciID']) || (isset($_POST['faciID']) && $_POST['faciID'] == $faci['faciID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($faci['faciID']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Required Hours:</label>
                <input type="number" name="reqHrs" 
                    placeholder="e.g., 40"
                    value="<?php echo htmlspecialchars($profileData['required_hours'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>End Shift:</label>
                <input type="time" name="endShift" 
                    value="<?php echo htmlspecialchars($profileData['end_shift'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                    <label>Date End:</label>
                        <input type="date" name="dateEnd" 
                         value="<?php echo htmlspecialchars($profileData['date_end'] ?? ''); ?>"
                         <?php echo $profileData ? 'readonly' : ''; ?>>

                

                    <label>Company Address:</label>
                <input type="text" name="companyAddress" 
                    placeholder="e.g., 123 Business District, City"
                    value="<?php echo htmlspecialchars($profileData['company_address'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>
</div>
                     

               <!-- Family Data -->
    <div id="familyData" class="profile-category">
        <h3>Family Data</h3>
        <div class="form-container">
            <div class="form-row">
                <label>Father's Name:</label>
                <input type="text" name="fatherName" 
                    placeholder="e.g., John Doe"
                    value="<?php echo htmlspecialchars($profileData['father_name'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Occupation:</label>
                <input type="text" name="fatherOccupation" 
                    placeholder="e.g., Engineer"
                    value="<?php echo htmlspecialchars($profileData['father_occupation'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
            <div class="form-row">
                <label>Mother's Name:</label>
                <input type="text" name="motherName" 
                    placeholder="e.g., Jane Doe"
                    value="<?php echo htmlspecialchars($profileData['mother_name'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Occupation:</label>
                <input type="text" name="motherOccupation" 
                    placeholder="e.g., Teacher"
                    value="<?php echo htmlspecialchars($profileData['mother_occupation'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>

    <!-- Health Data -->
    <div id="healthData" class="profile-category">
        <h3>Health Data</h3>
        <div class="form-container">
            <div class="form-row">
                <label>Blood Type:</label>
                <input type="text" name="bloodType" 
                    placeholder="e.g., O+"
                    value="<?php echo htmlspecialchars($profileData['blood_type'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Height:</label>
                <input type="text" name="height" 
                    placeholder="e.g., 5'8\""
                    value="<?php echo htmlspecialchars($profileData['height'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
            <div class="form-row">
                <label>Weight:</label>
                <input type="text" name="weight" 
                    placeholder="e.g., 150 lbs"
                    value="<?php echo htmlspecialchars($profileData['weight'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Health Problems:</label>
                <input type="text" name="healthProblems" 
                    placeholder="e.g., Asthma"
                    value="<?php echo htmlspecialchars($profileData['health_problems'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>

    <!-- Scholastic Data -->
    <div id="scholasticData" class="profile-category">
    <h3>Scholastic Data</h3>
    <div class="form-container">
        <div class="form-row">
            <div class="form-column">
                <label>Elementary School:</label>
                <input type="text" name="elementarySchool" 
                    placeholder="e.g., ABC Elementary"
                    value="<?php echo htmlspecialchars($profileData['elementary_school'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Year Graduated:</label>
                <input type="text" name="elementaryYearGraduated" 
                    placeholder="e.g., 2005"
                    value="<?php echo htmlspecialchars($profileData['elementary_year_graduated'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Honors/Awards Received:</label>
                <input type="text" name="elementaryHonors" 
                    placeholder="e.g., Best in Math"
                    value="<?php echo htmlspecialchars($profileData['elementary_honors'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Secondary School:</label>
                <input type="text" name="secondarySchool" 
                    placeholder="e.g., XYZ High School"
                    value="<?php echo htmlspecialchars($profileData['secondary_school'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>

            <div class="form-column">
                <label>Year Graduated:</label>
                <input type="text" name="secondaryYearGraduated" 
                    placeholder="e.g., 2009"
                    value="<?php echo htmlspecialchars($profileData['secondary_year_graduated'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Honors/Awards Received:</label>
                <input type="text" name="secondaryHonors" 
                    placeholder="e.g., Valedictorian"
                    value="<?php echo htmlspecialchars($profileData['secondary_honors'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>College:</label>
                <input type="text" name="college" 
                    placeholder="e.g., University of ABC"
                    value="<?php echo htmlspecialchars($profileData['college'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Year Graduated:</label>
                <input type="text" name="collegeYearGraduated" 
                    placeholder="e.g., 2013"
                    value="<?php echo htmlspecialchars($profileData['college_year_graduated'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                    <label>Honors/Awards Received:</label>
                <input type="text" name="collegeHonors" 
                    placeholder="e.g., Valedictorian"
                    value="<?php echo htmlspecialchars($profileData['college_honors'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>
</div>


    <!-- Work Experience -->
    <div id="workExperience" class="profile-category">
        <h3>Work Experience</h3>
        <div class="form-container">
            <div class="form-row">
                <label>Company Name:</label>
                <input type="text" name="companyName" 
                    placeholder="e.g., TechCorp"
                    value="<?php echo htmlspecialchars($profileData['company_name'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Position:</label>
                <input type="text" name="position" 
                    placeholder="e.g., Software Engineer"
                    value="<?php echo htmlspecialchars($profileData['position'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
            <div class="form-row">
                <label>Inclusive Date:</label>
                <input type="text" name="inclusiveDate" 
                    placeholder="e.g., Jan 2016 - Dec 2020"
                    value="<?php echo htmlspecialchars($profileData['inclusive_date'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Address:</label>
                <input type="text" name="companyAddressWorkExperience" 
                    placeholder="e.g., 456 Corporate Blvd."
                    value="<?php echo htmlspecialchars($profileData['company_address_work_experience'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>

    <!-- Special Skills -->
    <div id="specialSkills" class="profile-category">
        <h3>Special Skills</h3>
        <div class="form-container">
            <label>Skills:</label>
            <input type="text" name="skills" 
                placeholder="e.g., Web Development, Graphic Design"
                value="<?php echo htmlspecialchars($profileData['skills'] ?? ''); ?>"
                <?php echo $profileData ? 'readonly' : ''; ?>>
        </div>
    </div>

    <!-- Character References -->
    <div id="characterReferences" class="profile-category">
        <h3>Character References</h3>
        <div class="form-container">
            <div class="form-row">
                <label>Name:</label>
                <input type="text" name="refName" 
                    placeholder="e.g., Michael Smith"
                    value="<?php echo htmlspecialchars($profileData['ref_name'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Position:</label>
                <input type="text" name="refPosition" 
                    placeholder="e.g., HR Manager"
                    value="<?php echo htmlspecialchars($profileData['ref_position'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
            <div class="form-row">
                <label>Address:</label>
                <input type="text" name="refAddress" 
                    placeholder="e.g., 123 Reference Rd."
                    value="<?php echo htmlspecialchars($profileData['ref_address'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Contact No.:</label>
                <input type="text" name="refContact" 
                    placeholder="e.g., 09171234567"
                    value="<?php echo htmlspecialchars($profileData['ref_contact'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>

    <!-- Emergency Contact -->
    <div id="emergencyContact" class="profile-category">
        <h3>Emergency Contact</h3>
        <div class="form-container">
            <div class="form-row">
                <label>Name:</label>
                <input type="text" name="emergencyName" 
                    placeholder="e.g., Anna Lee"
                    value="<?php echo htmlspecialchars($profileData['emergency_name'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>

                <label>Relationship:</label>
                <input type="text" name="emergencyRelationship" 
                    placeholder="e.g., Sister"
                    value="<?php echo htmlspecialchars($profileData['emergency_relationship'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
            <div class="form-row">
                <label>Contact No.:</label>
                <input type="text" name="emergencyContactNo" 
                    placeholder="e.g., 09171234567"
                    value="<?php echo htmlspecialchars($profileData['emergency_contact_no'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
                    <div class="form-row">
                <label>Emergency Address:</label>
                <input type="text" name="emergencyAddress" 
                    placeholder="e.g., 123 Emergency Rd."
                    value="<?php echo htmlspecialchars($profileData['emergency_address'] ?? ''); ?>"
                    <?php echo $profileData ? 'readonly' : ''; ?>>
            </div>
        </div>
    </div>
            </div>

        <!-- Profile form buttons -->
        <div class="form-buttons">
            <?php if (!$profileData): ?>
                <button type="submit" name="insert-btn" class="insert-btn style="cursor: pointer;">
                    Add Information
                </button>
            <?php else: ?>
                <p class="profile-status">Profile information already submitted</p>
            <?php endif; ?>
        </div>

        </form>

    </div>
</div>
</div>




  








<div class="content-section" id="attendance">
    <div class="attend-content">
        <div class="attendance-container">
                <h1> Remaining working hours: 
                <?php 
                    $totalWorkedHours = 0; // Initialize a variable to sum up total worked hours

                    // Loop through time logs and check if any status is "Approved"
                    foreach ($timeLogs as $log) {
                        if (strtolower($log['status']) == 'approved') {
                            // Calculate the difference between login_time and logout_time
                            $loginTime = strtotime($log['login_time']);
                            $logoutTime = strtotime($log['logout_time']);

                            // Make sure logout time is after login time to avoid negative time difference
                            if ($logoutTime > $loginTime) {
                                // Calculate the difference in seconds
                                $timeDiff = $logoutTime - $loginTime;

                                // Convert seconds to hours (assuming 1 hour = 3600 seconds)
                                $hoursWorked = $timeDiff / 3600;

                                // Subtract the calculated hours from the required hours
                                $totalWorkedHours += $hoursWorked;
                                $requiredHours -= $hoursWorked;
                            }
                        }
                    }

                    // Ensure the required hours do not become negative
                    $requiredHours = max($requiredHours, 0);

                    // Display the adjusted required hours (remaining hours)
                    echo number_format($requiredHours, 2); // Display the remaining required hours (formatted to 2 decimal places)
                ?> HRS
                </h1>

                <!-- Print Button -->
                <button onclick="printTable()" class="print-btn">Print Table</button>

                <!-- Display the time logs in a table -->
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th> <!-- Add the Count column -->
                            <th>Login Time</th>
                            <th>Break Time</th>
                            <th>Back to Work Time</th>
                            <th>Logout Time</th>
                            <th>Task</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($timeLogs)): ?>
                            <?php $count = 1; // Initialize counter ?>
                            <?php foreach ($timeLogs as $log): ?>
                                <?php 
                // Skip entries with 'pending' status
                $status = strtolower($log['status'] ?? '');
                if ($status === 'pending') continue;
                ?>
                                
                                <tr>
                                    <td><?php echo $count++; ?></td> <!-- Display the current count and increment -->
                                    <td><?php echo htmlspecialchars($log['login_time'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['break_time'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['back_to_work_time'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['logout_time'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['task'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['status'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No time logs found for this intern.</td> <!-- Adjust colspan to 6 -->
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    

<div class="content-section" id="requests">
    <div class="req-content">

    <h1>My Daily Time Record</h1> <!-- Corrected the closing tag -->
    <!-- Print Button -->
    <button onclick="printTable()" class="print-btn">Print Table</button>

        <div class="wrapper">
            <!-- Display the time logs in a table -->
            <table id="timeLogsTable">
                <thead>
                    <tr>
                        <th>#</th> <!-- Add the Count column -->
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Task</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Assuming $conn is your PDO database connection and $internID is already defined
                    $stmt = $conn->prepare("SELECT * FROM time_logs WHERE internID = :internID");
                    $stmt->bindParam(':internID', $internID, PDO::PARAM_INT);
                    $stmt->execute();

                    $timeLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($timeLogs)):
                        $count = 1; // Initialize counter
                        foreach ($timeLogs as $log): ?>
                        <?php
                         $status = strtolower($log['status'] ?? '');
                         if ($status === 'approved' || $status === 'declined') continue;
                         ?>
                            <tr>
                                <td><?php echo $count++; ?></td> <!-- Display the current count and increment -->
                                <td><?php echo htmlspecialchars($log['login_time'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['logout_time'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['task'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($log['status'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No time logs found for this intern.</td> <!-- Adjust colspan to 5 -->
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



    

   

    <script src="js/intern_script.js"></script>
</body>
</html>
