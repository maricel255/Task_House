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