console.log('Admin_script.js is connected successfully');

// Function to show a specific content section
function showContent(section) {
    const sections = document.querySelectorAll('.content-section');

    // Hide all sections
    sections.forEach((content) => {
        content.classList.remove('active');
    });

    // Show the selected section
    const activeSection = document.getElementById(section);
    if (activeSection) {
        activeSection.classList.add('active');
    } else {
        console.error(`Section with id ${section} not found.`);
    }
}

// Function to open a modal
function openModal(modalId) {
    // Show the specified modal
    document.getElementById(modalId).style.display = 'block';

    // Show the overlay
    document.getElementById('overlay').style.display = 'block';
}

// Function to close a modal
function closeModal(modalId) {
    // Hide the specified modal
    document.getElementById(modalId).style.display = 'none';

    // Hide the overlay
    document.getElementById('overlay').style.display = 'none';
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    var modals = document.getElementsByClassName('modal');
    for (var i = 0; i < modals.length; i++) {
        if (event.target === modals[i]) {
            closeModal(modals[i].id); // Close the clicked modal
        }
    }

    // Close overlay if clicked
    if (event.target === document.getElementById('overlay')) {
        closeModal('myModal'); // Close the main modal when the overlay is clicked
    }
}

// Logout function
function logout() {
    alert('Logging out...');
    window.location.href = "Admin_registration.php";
}

// Basic client-side form validation
function validateForm() {
    var internID = document.getElementById("internID").value;
    var password = document.getElementById("InternPass").value;

    if (internID === "" || password === "") {
        alert("All fields must be filled out");
        return false;
    }
    return true;
}

    // Annoucement viewrer


    let currentSlide = 0;

    function showSlide(index) {
        const items = document.querySelectorAll('.announcement-item');
        if (index >= items.length) {
            currentSlide = 0; // Loop back to first slide
        } else if (index < 0) {
            currentSlide = items.length - 1; // Loop to last slide
        } else {
            currentSlide = index;
        }
        items.forEach((item, i) => {
            item.classList.remove('active'); // Hide all slides
            if (i === currentSlide) {
                item.classList.add('active'); // Show current slide
            }
        });
    }

    function moveSlide(step) {
        showSlide(currentSlide + step); // Update current slide index
    }

    // Show the first slide
    showSlide(currentSlide);

  // JavaScript function to print the table

    function printDetails() {
        // This function will print the detailed intern profile.
        var printContents = document.querySelector('.intern-details').innerHTML;  // Get only the intern details content
    
        // Create a new window or a new hidden iframe for printing
        var printWindow = window.open('', '', 'height=600,width=800');
        
        printWindow.document.write('<html><head><title>Print Intern Details</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }'); 
        printWindow.document.write('.intern-details { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 20px; }'); // Create a 4-column grid layout
        printWindow.document.write('.profile-image { grid-column: span 1; }');  // Image in the first column, spans 1 grid column
        printWindow.document.write('.profile-image img { width: 150px; height: 150px; }'); // Image size for printing
        printWindow.document.write('table { width: 100%; border-collapse: collapse; grid-column: span 4; margin-top: 20px; }'); // Table spans across all 4 columns
        printWindow.document.write('th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }');
        printWindow.document.write('@page { size: A4; margin: 10mm; }'); // Ensure A4 size for printing
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContents);  // Write the intern details into the new window
        printWindow.document.write('</body></html>');
    
        // Wait for the content to load, then trigger the print dialog
        printWindow.document.close();
        printWindow.document.focus();
        printWindow.print();  // Open the print dialog for the intern details only
        printWindow.close();
    }
    function printTable() {
        // Get the HTML content of the table
        var tableContent = document.getElementById('reportTable').outerHTML;

        // Open a new window for printing
        var printWindow = window.open('', '', 'height=500,width=800');
        
        // Write the table content to the new window
        printWindow.document.write('<html><head><title>Print Table</title>');
        printWindow.document.write('<style>table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid black; padding: 8px; text-align: left;}</style></head><body>');
        printWindow.document.write(tableContent);  // Insert the table content
        printWindow.document.write('</body></html>');
        
        // Close the document to render the page
        printWindow.document.close();

        // Wait until the page content is fully loaded, then print
        printWindow.onload = function() {
            printWindow.print();
        };
    }
//MARICEL START
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.container'); // Select the parent container
    if (container) {
        const newElement = document.createElement('div');
        newElement.id = 'internDetails'; // Set the ID
        newElement.className = 'intern-details'; // Set the class
        newElement.textContent = 'This is the intern-details content.'; // Example content
        container.appendChild(newElement); // Append the new element to the container
        console.log("Element #internDetails added to the DOM.");
    } else {
        console.error("Parent container not found.");
    }
});
//MARICEL END
// Start Kyle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetchDetails'])) {
    $internID = $_POST['internID'];

    // Fetch all details for the selected Intern ID
    $sql = "SELECT pi.*, ia.profile_image, 
            CONCAT(pi.first_name, ' ', COALESCE(pi.middle_name, ''), ' ', pi.last_name) as full_name
            FROM profile_information pi
            LEFT JOIN intacc ia ON pi.internID = ia.internID
            WHERE pi.internID = :internID";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':internID', $internID, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $internDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return the HTML to be injected dynamically
        echo '<button class="close-btn" onclick="closeDetails()">×</button>';
        echo '<h2>Profile Information for ' . htmlspecialchars($internDetails['full_name']) . '</h2>';
        
        // Show profile image
        if (!empty($internDetails['profile_image'])) {
            echo '<div class="profile-image">';
            echo '<img src="uploaded_files/' . htmlspecialchars($internDetails['profile_image']) . '" alt="Profile Image" width="160" height="150">';
            echo '</div>';
        } else {
            echo '<div class="profile-image">';
            echo '<img src="image/USER_ICON.png" alt="Default Image" width="150" height="150">';
            echo '</div>';
        }

        // Group related information into sections
        echo '<div class="details-sections">';
        
        // Personal Information Section
        echo '<div class="detail-section">';
        echo '<h3>Personal Information</h3>';
        echo '<table class="details-table">';
        $personalInfo = [
            'Intern ID' => 'internID',
            'First Name' => 'first_name',
            'Middle Name' => 'middle_name',
            'Last Name' => 'last_name',
            'Course/Year/Section' => 'course_year_sec',
            'Gender' => 'gender',
            'Age' => 'age',
            'Birth Date' => 'birth_date',
            'Birth Place' => 'birth_place',
            'Religion' => 'religion',
            'Civil Status' => 'civil_status',
            'Citizenship' => 'citizenship'
        ];
        foreach ($personalInfo as $label => $field) {
            if (isset($internDetails[$field]) && !empty($internDetails[$field])) {
                echo '<tr><th>' . $label . ':</th><td>' . htmlspecialchars($internDetails[$field]) . '</td></tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        // Contact Information Section
        echo '<div class="detail-section">';
        echo '<h3>Contact Information</h3>';
        echo '<table class="details-table">';
        $contactInfo = [
            'Current Address' => 'current_address',
            'Provincial Address' => 'provincial_address',
            'Telephone No' => 'tel_no',
            'Mobile No' => 'mobile_no',
            'Email' => 'email'
        ];
        foreach ($contactInfo as $label => $field) {
            if (isset($internDetails[$field]) && !empty($internDetails[$field])) {
                echo '<tr><th>' . $label . ':</th><td>' . htmlspecialchars($internDetails[$field]) . '</td></tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        // Company Information Section
        echo '<div class="detail-section">';
        echo '<h3>Company Information</h3>';
        echo '<table class="details-table">';
        $companyInfo = [
            'HR Manager' => 'hr_manager',
            'Facilitator ID' => 'faciID',
            'Company' => 'company',
            'Company Address' => 'company_address'
        ];
        foreach ($companyInfo as $label => $field) {
            if (isset($internDetails[$field]) && !empty($internDetails[$field])) {
                echo '<tr><th>' . $label . ':</th><td>' . htmlspecialchars($internDetails[$field]) . '</td></tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        // Family Information Section
        echo '<div class="detail-section">';
        echo '<h3>Family Information</h3>';
        echo '<table class="details-table">';
        $familyInfo = [
            'Father\'s Name' => 'father_name',
            'Father\'s Occupation' => 'father_occupation',
            'Mother\'s Name' => 'mother_name',
            'Mother\'s Occupation' => 'mother_occupation'
        ];
        foreach ($familyInfo as $label => $field) {
            if (isset($internDetails[$field]) && !empty($internDetails[$field])) {
                echo '<tr><th>' . $label . ':</th><td>' . htmlspecialchars($internDetails[$field]) . '</td></tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        // Medical Information Section
        echo '<div class="detail-section">';
        echo '<h3>Medical Information</h3>';
        echo '<table class="details-table">';
        $medicalInfo = [
            'Blood Type' => 'blood_type',
            'Height' => 'height',
            'Weight' => 'weight',
            'Health Problems' => 'health_problems'
        ];
        foreach ($medicalInfo as $label => $field) {
            if (isset($internDetails[$field]) && !empty($internDetails[$field])) {
                echo '<tr><th>' . $label . ':</th><td>' . htmlspecialchars($internDetails[$field]) . '</td></tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        echo '</div>'; // Close details-sections div
        
        // Add the resize handle
        echo '<div class="intern-details-resize-handle"></div>';
        
    } else {
        echo '<p>No details found for the selected Intern ID.</p>';
    }
    exit; // Stop further processing since this is an AJAX response
}
// End Kyle

// Add this to your existing js/Admin_script.js file or in a <script> tag at the bottom of the page
function deleteIntern(internID) {
    if (confirm('Are you sure you want to delete this intern account?')) {
        // Send AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Admin_Admin_1.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('Intern account deleted successfully!');
                window.location.reload();
            } else {
                alert('Error deleting account. Please try again.');
            }
        };
        xhr.send('internID=' + encodeURIComponent(internID) + '&action=delete');
    }
}

