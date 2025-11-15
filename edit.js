// =========================================================
// JavaScript for the Wearify Edit Tab
// =========================================================

// Global variable to keep track of the currently active category element
let activeElement = null;

// --- 1. Category Button Toggle Function ---
// This function handles the click event on any category button.
function toggleColor(element) {
    const activeClass = 'active';

    // Check if a category is currently active
    if (activeElement) {
        activeElement.classList.remove(activeClass);
    }
    
    // Check if the clicked element is DIFFERENT from the currently active one
    if (activeElement !== element) {
        // Activate the clicked element
        element.classList.add(activeClass);
        activeElement = element; 
        
        // Console log for functionality testing (optional)
        console.log(`Category selected: ${element.textContent.trim()}`);
        // In a real application, you would call a function here to filter the items grid.
    } else {
        // If the same active element is clicked again (to unselect it)
        activeElement = null; 
        console.log("Category unselected. Showing all items.");
    }
}

// --- 2. Item Deletion Functionality ---
// This function handles clicks on the trash icon (delete-icon)
function handleItemDeletion(event) {
    // Check if the element clicked is the delete icon
    if (event.target.classList.contains('delete-icon')) {
        
        // Find the closest parent element with the class 'item-card'
        const itemCard = event.target.closest('.item-card');
        
        if (itemCard) {
            // Optional: Add a confirmation dialog for user experience
            const confirmDeletion = confirm("Are you sure you want to permanently delete this clothing item?");
            
            if (confirmDeletion) {
                // Remove the item card from the DOM
                itemCard.remove();
                console.log("Item deleted successfully.");
            }
        }
    }
}

// --- 3. Add Item Card Functionality ---
// This function runs when the "Add Item" card is clicked
function handleAddItemClick() {
    console.log("Add Item card clicked. Initiating upload dialog...");
    // In a final application, this would typically open a modal or navigate to an upload page.
    alert("Prepare to upload a new item! (Image upload modal/form placeholder)");
}


// =========================================================
// INITIALIZATION
// =========================================================

document.addEventListener('DOMContentLoaded', () => {

    // --- A. Attach listener for the Add Item Card ---
    const addItemCard = document.querySelector('.add-item-card');
    if (addItemCard) {
        addItemCard.addEventListener('click', handleAddItemClick);
    }
    
    // --- B. Attach listener for the Item Deletion ---
    // Use event delegation on the Item Grid container for efficiency
    const itemGrid = document.querySelector('.item-grid');
    if (itemGrid) {
        itemGrid.addEventListener('click', handleItemDeletion);
    }

    // --- C. Set up listeners for Category Buttons ---
    // Although the HTML uses inline onclick, attaching listeners here is cleaner 
    // and ensures the initial 'activeElement' variable is correctly set up.
    const categoryButtons = document.querySelectorAll(
        '.rounded-upper, .rounded-lower, .rounded-shoes, .rounded-eyewear, ' +
        '.rounded-bag, .rounded-headwear, .rounded-accessory, .rounded-socks'
    );
    
    categoryButtons.forEach(button => {
        // Override the inline onclick for a unified approach if possible, or
        // ensure the inline call to toggleColor(this) is sufficient.
        // Since you provided the inline script, we'll assume it works, but 
        // we'll explicitly run the toggleColor function on load for the default active button.
        
        // Find the default active button (e.g., 'Upper')
        if (button.classList.contains('active')) {
             activeElement = button;
        }
    });

    // You can remove the entire `<script>...</script>` block from your HTML file 
    // and replace it with just: `<script src="edit.js"></script>`
});