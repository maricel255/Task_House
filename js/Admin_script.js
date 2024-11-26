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
    document.addEventListener("DOMContentLoaded", () => {
        const closeButton = document.getElementById("closeButton");
        if (closeButton) {
            closeButton.addEventListener("click", function () {
                const detailsDiv = document.getElementById("internDetails");
                if (detailsDiv) {
                    detailsDiv.classList.remove("show"); // Hide the details
                    detailsDiv.innerHTML = ""; // Clear the content
                } else {
                    console.error("Element with ID 'internDetails' not found.");
                }
            });
        } else {
            console.error("Element with ID 'closeButton' not found.");
        }
    });
    

    // Resize handle functionality
    const internDetails = document.querySelector('.intern-details');
    document.addEventListener('DOMContentLoaded', () => {
        const resizeHandle = document.querySelector('.resizeHandle'); // Use class if applicable
        
        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', (e) => {
                isResizing = true;
                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', () => {
                    isResizing = false;
                    document.removeEventListener('mousemove', handleMouseMove);
                });
            });
        } else {
            console.error("resizeHandle element not found.");
        }
    });
        let isResizing = false;


    function handleMouseMove(e) {
        if (isResizing) {
            const newWidth = e.clientX - internDetails.getBoundingClientRect().left;
            internDetails.style.width = `${newWidth}px`;
        }
    }
});


// Function to close the details section
function closeDetails() {
    const detailsDiv = document.getElementById("internDetails");
    detailsDiv.classList.remove("show"); // Hide the details
    detailsDiv.innerHTML = ""; // Clear the content
}
//end kyle

// Add this to your existing js/Admin_script.js file or in a <script> tag at the bottom of the page
function deleteIntern(internID) {
    if (confirm('Are you sure you want to delete this intern account?')) {
        var formData = new FormData();
        formData.append('internID', internID);
        formData.append('action', 'delete');

        fetch('Admin_Admin_1.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Error deleting account');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting account');
        });
    }
}