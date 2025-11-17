

document.addEventListener('DOMContentLoaded', function() {
    const profileContainer = document.querySelector('.profile-container');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const editLink = document.getElementById('edit-account-link');
    const editPanel = document.getElementById('edit-account-panel');

    // 1. Toggle the dropdown when the profile area is clicked
    profileContainer.addEventListener('click', function(e) {
        // Prevent click inside dropdown from bubbling up and immediately closing it
        if (e.target.closest('.dropdown-menu')) return; 

        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // 2. Handle the "Edit Account" link click
    editLink.addEventListener('click', function(e) {
        e.preventDefault(); // Stop the link from navigating anywhere
        
        // Hide the dropdown menu
        dropdownMenu.style.display = 'none';
        
        // Toggle the visibility of the Edit Account panel
        editPanel.style.display = editPanel.style.display === 'block' ? 'none' : 'block';
    });

    // Close the dropdown if the user clicks outside of it
    document.addEventListener('click', function(event) {
        if (!profileContainer.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});

