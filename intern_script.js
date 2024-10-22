document.addEventListener("DOMContentLoaded", function () {
    const userIcon = document.querySelector(".user-icon"); // Select the user icon
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const header = document.querySelector(".header");
    const timeContent = document.querySelector(".time-content");
    const attendanceContent = document.querySelector(".attend-content");
    const reqContent = document.querySelector(".req-content");
    let sidebarExpanded = false;

    // Toggle sidebar and content visibility on image click
    userIcon.addEventListener("click", function () {
        sidebar.classList.toggle("active");
        mainContent.classList.toggle("shift");
        header.classList.toggle("shifted");
        timeContent.classList.toggle("shifts");
        attendanceContent.classList.toggle("move");
        reqContent.classList.toggle("moves");
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
function login() {
    alert('Logging in...');
    window.location.href = "/intern_page.php";
}
function logout() {
    alert('Logging out...');
    window.location.href = "intern_log.html";
}
 // Open Modal and apply blur effect
 function openModal() {
    document.getElementById("profileModal").style.display = "block";
    document.querySelector(".container").classList.add("blur-background"); // Add blur to the container
}

// Close Modal and remove blur effect
function closeModal() {
    document.getElementById("profileModal").style.display = "none";
    document.querySelector(".container").classList.remove("blur-background"); // Remove blur from the container
}

// Function to update the profile (placeholder function)
function updateProfile() {
    alert("Profile updated successfully!");
    closeModal();
}