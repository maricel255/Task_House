document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar"); // Select the sidebar element
    const userIcon = document.querySelector(".user-icon"); // Select the user icon
    const mainContent = document.querySelector(".main-content");
    const header = document.querySelector(".header");
    const dashContent = document.querySelector(".dash-content");
    const reqContent = document.querySelector(".req-content");
    const repContent = document.querySelector(".rep-content");
    const internStatusDashboard = document.querySelector(".intern-status-dashboard");
    const internStatusTable = document.querySelector(".intern-status-table");

    let sidebarExpanded = false;

    // Toggle sidebar and content visibility on sidebar click
    sidebar.addEventListener("click", function (event) {
        // If the click was on the user icon, toggle the sidebar
        if (event.target.closest(".user-icon")) {
            sidebar.classList.toggle("active");
            mainContent.classList.toggle("shift");
            header.classList.toggle("shifted");
            dashContent.classList.toggle("shifts");
            reqContent.classList.toggle("move");
            repContent.classList.toggle("moves");

            // Toggle inner content visibility
            if (sidebarExpanded) {
                sidebar.classList.add("hide-content");
                sidebar.classList.remove("show-content");
                internStatusDashboard.classList.remove("shifted");
                internStatusTable.classList.remove("shifted");
            } else {
                sidebar.classList.add("show-content");
                sidebar.classList.remove("hide-content");
                internStatusDashboard.classList.add("shifted");
                internStatusTable.classList.add("shifted");
            }
            sidebarExpanded = !sidebarExpanded;
        }
    });

    // Optional: Close sidebar when clicking anywhere outside of it
    document.addEventListener("click", function (event) {
        if (!sidebar.contains(event.target) && !userIcon.contains(event.target)) {
            sidebar.classList.remove("show-content");
            sidebar.classList.add("hide-content");
            sidebarExpanded = false;
        }
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



    // Announcement slider functionality
    let currentIndex = 0; // Initialize the current index

    function moveSlide(direction) {
        const announcementItems = document.querySelectorAll('.announcement-item');
        announcementItems[currentIndex].classList.remove('active'); // Hide current announcement
    
        // Update the index based on direction
        currentIndex += direction;
    
        // Wrap around if necessary
        if (currentIndex < 0) {
            currentIndex = announcementItems.length - 1; // Go to last item
        } else if (currentIndex >= announcementItems.length) {
            currentIndex = 0; // Go back to first item
        }
    
        announcementItems[currentIndex].classList.add('active'); // Show new announcement
    }

      // Function to show the decline reason textarea when "Decline" is clicked
      document.getElementById('declineBtn').addEventListener('click', function() {
        document.getElementById('declineReasonForm').style.display = 'block'; // Show the form
    });
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
function showDeclineForm(logId) {
    // Hide all decline reason forms
    const allForms = document.querySelectorAll('[id^="declineReasonForm"]');
    allForms.forEach(form => form.style.display = 'none');
    
    // Show the form for the clicked button
    const form = document.getElementById('declineReasonForm' + logId);
    form.style.display = 'block';
}



// Open modal function
function openModal(id) {
    var modal = document.getElementById('updateModal' + id);
    modal.style.display = "block";
}

// Close modal function
function closeModal(id) {
    var modal = document.getElementById('updateModal' + id);
    modal.style.display = "none";
}

// Close the modal when clicking outside of it
window.onclick = function(event) {
    var modals = document.getElementsByClassName('modal');
    for (var i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = "none";
        }
    }
}



// Wait for the DOM to be fully loaded before running the script
document.addEventListener("DOMContentLoaded", function() {
    const alertMessage = document.querySelector('.alert');
    
    if (alertMessage) {
      // Set a timeout to trigger the fade-out effect after 5 seconds
      setTimeout(function() {
        alertMessage.classList.add('hide'); // Fade out the alert
      }, 5000); // 5 seconds

      // Set a timeout to remove the alert completely after 6 seconds
      setTimeout(function() {
        alertMessage.style.display = 'none'; // Completely remove the alert
      }, 6000); // 6 seconds (1 second after fade-out)
    }
  });
  
// Annoucement viewrer



  

/*This is for the modal that will appear when the settings button is clicked*/
// Start Kyle
function openCredentialsModal() {
    document.getElementById('credentialsModal').style.display = 'block';
}

/*This is for the close button in the modal*/
function closeCredentialsModal() {
    document.getElementById('credentialsModal').style.display = 'none';
    document.getElementById('credentialsForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const credentialsModal = document.getElementById('credentialsModal');
    const declineModal = document.getElementById('declineModal');
    
    if (event.target === credentialsModal) {
        closeCredentialsModal();
    }
    if (event.target === declineModal) {
        declineModal.style.display = "none";
    }
}





//search for intern reports

function updateFileName(input) {
    const fileName = input.files[0]?.name;
    const fileNameDisplay = input.parentElement.querySelector('.selected-file-name');
    if (fileName) {
        fileNameDisplay.textContent = fileName;
    } else {
        fileNameDisplay.textContent = '';
    }
}

function showDeclineForm(id) {
    document.getElementById('declineModal' + id).style.display = 'block';
}

function closeDeclineModal(id) {
    document.getElementById('declineModal' + id).style.display = 'none';
}

// Update your window.onclick function
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

