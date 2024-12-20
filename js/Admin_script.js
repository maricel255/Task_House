console.log('Admin_script.js is connected successfully');

// Function to show a specific content section
function showContent(section) {
    const sections = document.querySelectorAll('.content-section');

    // Hide all sections
    sections.forEach((content) => {
        content.classList.remove('active');
        content.style.display = 'none'; // Ensure all are hidden initially
    });

    // Show the selected section
    const activeSection = document.getElementById(section);
    if (activeSection) {
        activeSection.classList.add('active');
        activeSection.style.display = 'block'; // Show the active section
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
    const currentPass = document.getElementById('currentUpass').value;
    const newPass = document.getElementById('newUpass').value;
    const confirmPass = document.getElementById('confirmUpass').value;

    // If any password field is filled, all password fields must be filled
    if (currentPass || newPass || confirmPass) {
        if (!currentPass) {
            alert('Current password is required when changing password');
            return false;
        }
        if (!newPass) {
            alert('New password is required when changing password');
            return false;
        }
        if (!confirmPass) {
            alert('Password confirmation is required when changing password');
            return false;
        }
        if (newPass !== confirmPass) {
            alert('New passwords do not match');
            return false;
        }
        if (newPass.length < 6) {
            alert('New password must be at least 6 characters long');
            return false;
        }
    }}

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
document.addEventListener("DOMContentLoaded", function () {
    // Attach event listeners to all View Details buttons
    document.querySelectorAll(".view-details-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const internID = this.getAttribute("data-intern-id");
            const detailsDiv = document.getElementById("internDetails");

            // Fetch and show details
            fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    fetchDetails: true,
                    internID: internID
                }),
            })
            .then((response) => response.text())
            .then((data) => {
                detailsDiv.innerHTML = data; // Inject the fetched data
                detailsDiv.classList.add("show"); // Show the details
            })
            .catch((error) => {
                console.error("Error fetching details:", error);
            });
        });
    });

    // Close button functionality
    const closeButton = document.querySelector(".close-btn");
    closeButton.addEventListener("click", function () {
        const detailsDiv = document.getElementById("internDetails");
        detailsDiv.classList.remove("show"); // Hide the details
        detailsDiv.innerHTML = ""; // Clear the content
    });
});

// Function to close the details section
function closeDetails() {
    const detailsDiv = document.getElementById("internDetails");
    detailsDiv.classList.remove("show"); // Hide the details
    detailsDiv.innerHTML = ""; // Clear the content
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

function confirmDelete(internID) {
    // Fetch the intern details before confirming deletion
    fetch("", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            fetchDetails: true,
            internID: internID
        }),
    })
    .then((response) => response.json()) // Expecting JSON response
    .then((data) => {
        if (data.success) {
            // Display the intern details in a confirmation dialog
            const details = `
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete the following intern?</p>
                <p><strong>Intern ID:</strong> ${data.internID}</p>
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <!-- Add more fields as necessary -->
            `;
            if (confirm(details)) {
                // If confirmed, create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Submit to the current page

                // Create hidden inputs for action and internID
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'internID';
                idInput.value = internID;

                // Append inputs to the form
                form.appendChild(actionInput);
                form.appendChild(idInput);

                // Append the form to the body and submit it
                document.body.appendChild(form);
                form.submit();
            }
        } else {
            alert("Error fetching intern details.");
        }
    })
    .catch((error) => {
        console.error("Error fetching details:", error);
    });
}