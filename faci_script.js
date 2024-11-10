document.addEventListener("DOMContentLoaded", function () {
    const userIcon = document.querySelector(".user-icon"); // Select the user icon
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const header = document.querySelector(".header");
    const dashContent = document.querySelector(".dash-content");
    const reqContent = document.querySelector(".req-content");
    const repContent = document.querySelector(".rep-content");
    let sidebarExpanded = false;

    // Toggle sidebar and content visibility on image click
    userIcon.addEventListener("click", function () {
        sidebar.classList.toggle("active");
        mainContent.classList.toggle("shift")   ;
        header.classList.toggle("shifted");
        dashContent.classList.toggle("shifts");
        reqContent.classList.toggle("move");
        repContent.classList.toggle("moves");
        // Toggle inner content visibility
        if (sidebarExpanded) {
            sidebar.classList.add("hide-content");
            sidebar.classList.remove("show-content");
        } else {
            sidebar.classList.add("show-content");
            sidebar.classList.remove("hide-content");
        }
        sidebarExpanded = !sidebarExpanded;
    });
});

function showContent(sectionId) {
    // Hide all content sections
    const contentSections = document.querySelectorAll('.content-section');
    contentSections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the selected section
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';
    }
}

// Get the modal
var modal = document.getElementById("declineModal");

var openModalBtn = document.getElementById("openModalBtn");


// Get the button that opens the modal
// Add your button element with id="openModalBtn" in HTML
var btn = document.getElementById("openModalBtn");


// Get the <span> element that closes the modal
var span = document.getElementById("closeModalBtn");

// When the user clicks on the button, open the modal
btn.onclick = function() {
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}



function logout() {
    alert('Logging out...');
    window.location.href = "faci_log_in.php";
}

// Function to open the modal
function openModal(logId) {
    // Show the modal by targeting the modal with the specific log ID
    var modal = document.getElementById('declineModal');
    modal.style.display = "block";

    // Set the action URL dynamically if needed
    var form = modal.querySelector('form');
    form.action = "faci_facilitator.php?id=" + logId;  // Change URL if necessary
}

// Function to close the modal when the user clicks the "×" button
var closeBtn = document.getElementsByClassName('close-btn')[0];  // Get the close button
closeBtn.onclick = function() {
    var modal = document.getElementById('declineModal');
    modal.style.display = "none";  // Close the modal
}

// Function to close the modal
function closeModal() {
    document.getElementById("declineModal").style.display = "none";
}

// Close the modal when clicking outside the modal content
window.onclick = function(event) {
    var modal = document.getElementById("declineModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
// Close the modal if the user clicks outside of the modal content
window.onclick = function(event) {
    var modal = document.getElementById('declineModal');
    if (event.target == modal) {
        modal.style.display = "none";  // Close the modal
    }
}

// Wait for the page to load
window.onload = function() {
    var alertBox = document.getElementById('alertBox');

    // Check if the alert box exists on the page
    if (alertBox) {
        // Set a timeout of 5 seconds to hide the alert and show it again
        setTimeout(function() {
            alertBox.style.display = 'none'; // Hide the alert after 5 seconds

            setTimeout(function() {
                alertBox.style.display = 'block'; // Show the alert again after another 500ms
            }, 500); // A slight delay before showing it again

        }, 5000); // 5000ms = 5 seconds
    }
};
// Annoucement viewrer

document.addEventListener("DOMContentLoaded", function() {
    let currentIndex = 0; // Declare currentIndex

    // Get the prev and next buttons
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');

    if (prevButton && nextButton) {
        // Attach the moveSlide function to the prev and next buttons
        prevButton.onclick = function() {
            moveSlide(-1); // Move to the previous slide
        };
        nextButton.onclick = function() {
            moveSlide(1); // Move to the next slide
        };
    } else {
        console.error("Prev and/or Next buttons not found.");
    }

    // Define the moveSlide function
    function moveSlide(step) {
        console.log("Current Index before move:", currentIndex);
        
        currentIndex += step; // Adjust the currentIndex value

        // Ensure we don't go out of bounds
        const slides = document.querySelectorAll('.announcement-item');
        if (currentIndex >= slides.length) {
            currentIndex = 0; // Loop back to the first slide
        } else if (currentIndex < 0) {
            currentIndex = slides.length - 1; // Loop to the last slide
        }

        console.log("Current Index after move:", currentIndex);

        // Move the slide
        slides.forEach((slide, index) => {
            slide.style.display = (index === currentIndex) ? 'block' : 'none'; // Show only the active slide
        });
    }
});