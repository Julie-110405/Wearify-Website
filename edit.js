let activeElement = null;
let itemToDelete = null; 

// Handles category button selection and slide display
function toggleColor(element) {
    const activeClass = 'active';
    const activeSlideClass = 'active-slide';
    const targetId = element.getAttribute('data-target');
    const targetSlide = document.getElementById(targetId);

    // Remove active state from previous element
    if (activeElement) {
        activeElement.classList.remove(activeClass);
        const prevTargetId = activeElement.getAttribute('data-target');
        const prevSlide = document.getElementById(prevTargetId);
        if (prevSlide) {
            prevSlide.classList.remove(activeSlideClass);
        }
    }
    
    // Toggle active state for current element
    if (activeElement !== element) {
        element.classList.add(activeClass);
        activeElement = element; 
        if (targetSlide) {
            targetSlide.classList.add(activeSlideClass);
        }
        console.log(`Category selected: ${element.textContent.trim()}`);
    } else {
        activeElement = null; 
        console.log("Category unselected. No slide visible.");
    }
}

// Opens modal to confirm deletion
function openDeleteModal(card) {
    itemToDelete = card; // Store the item to delete if confirmed
    document.getElementById("deleteModal").style.display = "flex";
}

// Closes deletion modal without removing item
function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
    itemToDelete = null; // Reset
}

// Handles click on delete icon
function handleItemDeletion(event) {
    if (event.target.classList.contains('delete-icon')) {
        const itemCard = event.target.closest('.item-card');
        openDeleteModal(itemCard); // Open confirmation modal
    }
}

// Triggers file input when add item card is clicked
function handleAddItemClick() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) fileInput.click();
}

// Handles adding image to the UI
function handleFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    const activeSlide = document.querySelector('.active-slide');
    const clothesContainer = activeSlide ? activeSlide.querySelector('.clothes-container') : null;
    
    if (!clothesContainer) {
        console.error("No active clothes container found to add item.");
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const imageUrl = e.target.result; // Image preview

        const newItemCard = document.createElement('div');
        newItemCard.classList.add('item-card');

        const imageContainer = document.createElement('div');
        imageContainer.classList.add('item-image-container');

        const img = document.createElement('img');
        img.src = imageUrl; // This is front-end only preview
        img.alt = "Uploaded outfit item";

        imageContainer.appendChild(img);
        newItemCard.appendChild(imageContainer);
        
        // Create delete icon
        const deleteImg = document.createElement('img');
        deleteImg.classList.add('delete-icon');
        deleteImg.src = 'delete.png';
        deleteImg.alt = 'Delete Item';
        
        newItemCard.appendChild(deleteImg);
        
        clothesContainer.appendChild(newItemCard);
        
        const noItemsMessage = clothesContainer.querySelector('h2');
        if (noItemsMessage) noItemsMessage.remove();

        event.target.value = ''; // Reset file input
        console.log(`Image added to ${activeSlide.id}.`);
    };
    
    reader.readAsDataURL(file); // Read image for preview
}

document.addEventListener('DOMContentLoaded', () => {

    const fileInput = document.getElementById('fileInput');
    const scrollContainer = document.getElementById('scrollContainer');
    
    if (fileInput) fileInput.addEventListener('change', handleFileChange);

    // Add click listener to all add-item cards
    document.querySelectorAll('.add-item-card')
        .forEach(card => card.addEventListener('click', handleAddItemClick));
    
    // Listen for delete clicks
    if (scrollContainer) scrollContainer.addEventListener('click', handleItemDeletion);

    // Set default active button and slide
    const defaultActiveButton = document.querySelector('.category-buttons .active');
    const defaultActiveSlide = document.querySelector('.slide.active-slide');

    if (defaultActiveButton) activeElement = defaultActiveButton;
    
    if (!defaultActiveSlide && defaultActiveButton) {
        const targetId = defaultActiveButton.getAttribute('data-target');
        const targetSlide = document.getElementById(targetId);
        if (targetSlide) targetSlide.classList.add('active-slide');
    }

    // Delete modal buttons
    document.getElementById("cancelDelete").addEventListener("click", closeDeleteModal);

    document.getElementById("confirmDelete").addEventListener("click", () => {
        if (itemToDelete) itemToDelete.remove();  
        // Backend integration point: call API here to delete item from database
        closeDeleteModal();
    });
});
