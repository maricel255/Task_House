<?php
// Handle Deletion
if (isset($_POST['delete']) && isset($_POST['logID'])) {
    $logID = $_POST['logID']; // Get the unique logID

    // Prepare the DELETE SQL statement
    $sql = "DELETE FROM time_logs WHERE id = :logID"; // Use id for deletion
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':logID', $logID, PDO::PARAM_INT); // Bind logID as an integer

    try {
        // Execute the deletion
        if ($stmt->execute()) {
            echo "<script>
                    alert('Time record deleted successfully.');
                    window.location.href = '" . $_SERVER['PHP_SELF'] . "?section=Report';
                  </script>";
        }
    } catch (PDOException $e) {
        // Display a custom error message
        echo "<script>alert('Error deleting record: Cannot delete this record because it is referenced by another record.');</script>";
    }
}
?>
 
<script src="js/Admin_script.js"></script>
</body>
</html>
