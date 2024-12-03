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
    document.getElementById('facilitator-header').style.display = "none"; // Hide the header row

    // Show the overlay
    document.getElementById('overlay').style.display = 'block';
   
}

// Function to close a modal
function closeModal(modalId) {
    // Hide the specified modal
    document.getElementById(modalId).style.display = 'none';

    // Hide the overlay
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('facilitator-header').style.display = ""; // Show the header row again
}
window.onclick = function(event) {
    const modal = document.getElementById('FaccAccModal');
    if (event.target === modal) {
        modal.style.display = "none";
        document.getElementById('facilitator-header').style.display = ""; // Show the header row again
    }
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

    // Start kyle
document.addEventListener("DOMContentLoaded", function() {
    // Add click event listeners to all "View Details" buttons
    document.querySelectorAll(".view-details-btn").forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            const internID = this.getAttribute("data-intern-id");
            const detailsDiv = document.getElementById("internDetails");
            
            // Show loading state
            detailsDiv.innerHTML = `
                <button class="close-btn" onclick="closeDetails()">&times;</button>
                <div class="loading" style="text-align: center; padding: 20px;">
                    <p>Loading...</p>
                </div>
            `;
            detailsDiv.classList.add("show");
            
            // Fetch intern details
            fetch(window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `fetchDetails=true&internID=${internID}`
            })
            .then(response => response.text())
            .then(data => {
                detailsDiv.innerHTML = `
                    <button class="close-btn" onclick="closeDetails()">&times;</button>
                    <div class="details-content">
                        <h2>Intern Details</h2>
                        ${data}
                        <button onclick="window.print()" class="print-btn">Print Details</button>
                    </div>
                `;
            })
            .catch(error => {
                detailsDiv.innerHTML = `
                    <button class="close-btn" onclick="closeDetails()">&times;</button>
                    <div class="error-message" style="color: red; text-align: center; padding: 20px;">
                        Error loading details. Please try again.
                    </div>
                `;
                console.error("Error:", error);
            });
        });
    });

    // Close details when clicking outside
    document.addEventListener("click", function(e) {
        const detailsDiv = document.getElementById("internDetails");
        if (!detailsDiv) return;
        
        const isClickInside = detailsDiv.contains(e.target);
        const isViewDetailsButton = e.target.closest('.view-details-btn');
        
        if (!isClickInside && !isViewDetailsButton && detailsDiv.classList.contains("show")) {
            closeDetails();
        }
    });
});

// Function to close details panel
function closeDetails() {
    const detailsDiv = document.getElementById("internDetails");
    if (!detailsDiv) return;
    
    detailsDiv.classList.remove("show");
    setTimeout(() => {
        detailsDiv.innerHTML = "";
    }, 300); // Match this with your CSS transition duration
}
//end kyle

// Add this to your existing JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const messageBox = document.getElementById('messageBox');
    if (messageBox && messageBox.textContent.trim() !== '') {
        messageBox.style.display = 'block';
        
        // Hide message after 5 seconds
        setTimeout(function() {
            messageBox.style.opacity = '0';
            setTimeout(function() {
                messageBox.style.display = 'none';
            }, 500);
        }, 5000);
    }
});
function autoUploadImage(input) {
    if (input.files && input.files[0]) {
        var formData = new FormData();
        formData.append('newProfileImage', input.files[0]);

        // Show preview immediately
        var profileImages = document.querySelectorAll('.profile-image img');
        profileImages.forEach(img => {
            img.src = URL.createObjectURL(input.files[0]);
        });

        // Upload to server silently
        fetch('Admin_Admin_1.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Profile image updated successfully');
                // Refresh the page after successful upload
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error uploading image:', error);
        });
    }
}

// Add new function for password update
document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Create FormData object
    const formData = new FormData();
    formData.append('updateType', 'password');
    formData.append('newFirstname', document.getElementById('newFirstname').value);
    formData.append('currentUpass', document.getElementById('currentUpass').value);
    formData.append('newUpass', document.getElementById('newUpass').value);
    formData.append('confirmUpass', document.getElementById('confirmUpass').value);
    
    fetch('Admin_Admin_1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password updated successfully!');
            window.location.reload();
        } else {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.reload();
    });
});

function viewInternDetails(internId) {
    // Prevent the dashboard from showing
    event.preventDefault();
    event.stopPropagation();

    // Get the container and details elements
    const container = document.querySelector('.container');
    const internDetails = document.getElementById('internDetails');
    
    // Hide the dashboard if it's visible
    const dashboard = document.querySelector('.dashboard');
    if (dashboard) {
        dashboard.style.display = 'none';
    }

    // Show the container and details
    container.style.display = 'block';
    internDetails.style.display = 'block';
    internDetails.classList.add('show');

    // Fetch and display intern details
    fetch(`get_intern_details.php?intern_id=${internId}`)
        .then(response => response.json())
        .then(data => {
            internDetails.innerHTML = `
                <button class="close-btn" onclick="closeInternDetails(event)">&times;</button>
                <h2>Intern Details</h2>
                <div class="intern-content">
                    <!-- Add your intern details content here -->
                </div>
            `;
        });
}

function closeInternDetails(event) {
    // Prevent event bubbling
    event.preventDefault();
    event.stopPropagation();

    // Hide the modal
    const container = document.querySelector('.container');
    const internDetails = document.getElementById('internDetails');
    
    internDetails.classList.remove('show');
    container.style.display = 'none';
    internDetails.style.display = 'none';
}


