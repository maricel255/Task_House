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
    window.location.href = "intern_log.php";
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

// Initial setup: show the first announcement
document.addEventListener('DOMContentLoaded', function () {
    const announcementItems = document.querySelectorAll('.announcement-item');
    announcementItems.forEach((item, index) => {
        if (index !== 0) {
            item.classList.remove('active'); // Hide all except the first
        }
    });
});


function updateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    };
    document.getElementById('time-display').textContent = now.toLocaleDateString('en-US', options);
}

// Update the time every second
setInterval(updateTime, 1000);

// Initialize the clock
updateTime();

 
function closeModal(event) {
    // Check if the click was outside the modal-content or on the close button
    if (event.target.className === 'modal' || event.target.className === 'close') {
        document.getElementById('taskModal').style.display = 'none';
    }
}

function showCategory(category) {
    // Hide all categories
    var categories = document.querySelectorAll('.profile-category');
    categories.forEach(function (cat) {
      cat.classList.remove('active');
    });
  
    // Show the clicked category
    var categoryToShow = document.getElementById(category);
    categoryToShow.classList.add('active');
  
    // Set the active class on the clicked button
    var buttons = document.querySelectorAll('.profile-sidebar button');
    buttons.forEach(function (btn) {
      btn.classList.remove('active');
    });
  
    var buttonToActivate = document.querySelector(`button[onclick="showCategory('${category}')"]`);
    buttonToActivate.classList.add('active');
  }
  
  // Close the modal
  function closeModal() {
    document.getElementById('profileModal').style.display = 'none';
  }
  
  // Open the modal (for testing, you can call this to show the modal)
  function openModal() {
    document.getElementById('profileModal').style.display = 'block';
  }
  
// Close the modal when clicking outside the modal content
window.onclick = function(event) {
    var modal = document.getElementById("profileModal");
    if (event.target == modal) {
      closeModal();
    }
  }
  
  // Close the modal (function remains the same)
  function closeModal() {
    document.getElementById('profileModal').style.display = 'none';
  }
  
  // Open the modal (for testing, you can call this to show the modal)
  function openModal() {
    document.getElementById('profileModal').style.display = 'block';
  }
  
 // Close the modal (function remains the same)
 function closeModal() {
    document.getElementById('profileModal').style.display = 'none';
  }
  
  // Open the modal (for testing, you can call this to show the modal)
  function openModal() {
    document.getElementById('profileModal').style.display = 'block';
  }
  // Start Kyle
  function openCredentialsModal() {
    document.getElementById('credentialsModal').style.display = 'block';
}

function closeCredentialsModal() {
    document.getElementById('credentialsModal').style.display = 'none';
    document.getElementById('credentialsForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('credentialsModal');
    if (event.target === modal) {
        closeCredentialsModal();
    }
}

// Form validation using your existing showToast function
document.getElementById('credentialsForm')?.addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        showToast('Passwords do not match!', 'error');
        return;
    }

    if (newPassword.length < 6) {
        e.preventDefault();
        showToast('Password must be at least 6 characters long!', 'error');
        return;
    }

    if (currentPassword === newPassword) {
        e.preventDefault();
        showToast('New password must be different from current password!', 'error');
        return;
    }
});

// Keep your existing showToast function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);

    // Hide and remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
function uploadImage() {
    document.getElementById('imageForm').submit(); // Automatically submit the form when a file is selected
}

// End Kyle