let activeElement = null;

function toggleColor(element) {
    const activeClass = 'active';

    if (activeElement) {
        activeElement.classList.remove(activeClass);
    }
    
    if (activeElement !== element) {
        element.classList.add(activeClass);
        activeElement = element; 
        console.log(`Category selected: ${element.textContent.trim()}`);
    } else {
        activeElement = null; 
        console.log("Category unselected. Showing all items.");
    }
}

function handleItemDeletion(event) {
    if (event.target.classList.contains('delete-icon')) {
        const itemCard = event.target.closest('.item-card');
        
        if (itemCard) {
            const confirmDeletion = confirm("Are you sure you want to permanently delete this clothing item?");
            
            if (confirmDeletion) {
                itemCard.remove();
                console.log("Item deleted successfully.");
            }
        }
    }
}

function handleAddItemClick() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.click();
    } else {
        console.error("File input not found!");
    }
}

function handleFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        const imageUrl = e.target.result;

        const clothesContainer = document.getElementById('clothes'); 
        
        const newItemCard = document.createElement('div');
        newItemCard.classList.add('item-card');

        const imageContainer = document.createElement('div');
        imageContainer.classList.add('item-image-container');

        const img = document.createElement('img');
        img.src = imageUrl;
        img.alt = "Uploaded outfit item";

        imageContainer.appendChild(img);
        newItemCard.appendChild(imageContainer);
        
        const deleteIcon = document.createElement('span');
        deleteIcon.classList.add('delete-icon');
        deleteIcon.innerHTML = '🗑️'; 
        newItemCard.appendChild(deleteIcon);
        
        if (clothesContainer) {
            clothesContainer.appendChild(newItemCard);
        }
        
        event.target.value = '';
        console.log("Image added to grid.");
    };
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', () => {

    const addItemCard = document.getElementById('addItemCard');
    const fileInput = document.getElementById('fileInput');
    const clothesContainer = document.getElementById('clothes'); 

    if (addItemCard) {
        addItemCard.addEventListener('click', handleAddItemClick);
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', handleFileChange);
    }
    
    if (clothesContainer) {
        clothesContainer.addEventListener('click', handleItemDeletion);
    }

    const defaultActiveButton = document.querySelector('.category-buttons .active');
    if (defaultActiveButton) {
        activeElement = defaultActiveButton;
    }
});