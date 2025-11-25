const EDIT_API_ENDPOINT = 'http://localhost/Wearify-Website/api/v1/closet.php';

function normalizeCategory(value = '') {
    let cat = value.toString().trim().toLowerCase();

    // Map common aliases or plural forms to expected keys
    const categoryMap = {
        'uppers': 'upper',
        'upper': 'upper',
        'lowers': 'lower',
        'lower': 'lower',
        'shoe': 'shoes',
        'shoes': 'shoes',
        'bags': 'bag',
        'bag': 'bag',
        'accessories': 'accessory',
        'accessory': 'accessory',
        'sock': 'socks',
        'socks': 'socks',
        'headwear': 'headwear',
        'eyewear': 'eyewear'
    };

    if (categoryMap[cat]) {
        cat = categoryMap[cat];
    } else {
        console.warn(`WARNING: Unknown category '${value}' normalized as '${cat}'`);
    }

    return cat;
}

document.addEventListener("DOMContentLoaded", function() {
    const fileInput = document.getElementById("fileInput");
    const addItemCards = document.querySelectorAll(".add-item-card");
    const deleteModal = document.getElementById("deleteModal");
    const cancelDelete = document.getElementById("cancelDelete");
    const confirmDelete = document.getElementById("confirmDelete");
    let itemToDelete = null; // Track the item to delete

    // Function to toggle active category button color and show corresponding slide
    function toggleColor(button) {
        // Remove active class from all buttons and slides
        document.querySelectorAll('.category-buttons div').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.slide').forEach(slide => slide.classList.remove('active-slide'));

        // Add active class to clicked button and corresponding slide
        button.classList.add('active');
        const targetSlide = document.getElementById(button.getAttribute('data-target'));
        if (targetSlide) {
            targetSlide.classList.add('active-slide');
        }
    }

    // Attach click event to category buttons (already handled in HTML onclick, but ensuring JS consistency)
    document.querySelectorAll('.category-buttons div').forEach(button => {
        button.addEventListener('click', function() {
            toggleColor(this);
        });
    });

    // Function to handle file selection and upload
    async function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) {
            console.error("No file selected");
            return;
        }

        const category = fileInput.getAttribute("data-category");
        if (!category) {
            console.error("No category set for file input");
            return;
        }

        // Immediately display the image using FileReader for instant feedback
        const reader = new FileReader();
        reader.onload = async function(e) {
            const imageUrl = e.target.result;
            // Add image to category with local preview
            const wrapper = addImageToCategory(category, imageUrl, file.name);

            // Now upload to server asynchronously
            const formData = new FormData();
            formData.append('item_image', file);
            formData.append('category', category);

            try {
                const response = await fetch(EDIT_API_ENDPOINT, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // Update to server URL for persistence
                    const img = wrapper.querySelector('img.uploaded-image');
                    img.src = result.image_url;
                    wrapper.setAttribute('data-item-id', result.item_id);
                } else {
                    console.error('Upload failed:', result.message);
                    // Keep local preview, but it won't persist
                }
            } catch (error) {
                console.error('Upload error:', error);
                // Keep local preview
            }
        };
        reader.readAsDataURL(file);
    }

    // Function to add image to the specific category container with remove button
    function addImageToCategory(category, imageUrl, fileName, itemId) {
        const container = document.getElementById(`clothes-${category}`);
        if (!container) {
            console.error(`Container for category ${category} not found`);
            return;
        }
        
        // Count images currently in container to set order
        const currentItems = container.querySelectorAll(".image-wrapper");
        const orderIndex = currentItems.length;

        // Hide "no items yet" message if present
        const noItems = container.querySelector('h2');
        if (noItems && noItems.textContent.includes('No items yet')) {
            noItems.style.display = 'none';
        }

        // Create a wrapper div for the image as a frame
        const imageWrapper = document.createElement("div");
        imageWrapper.className = "image-wrapper";
        // Removed inline-block to prevent layout issues with flex container
        // imageWrapper.style.display = "inline-block";
        // Add margin to control spacing explicitly
        imageWrapper.style.margin = "0"; // reset margin
        imageWrapper.style.position = "relative"; // For positioning the remove button
        imageWrapper.style.border = "1px solid #ddd"; // Gray outline frame similar to home
        imageWrapper.style.padding = "10px"; // Padding inside the frame similar to home
        imageWrapper.style.width = "170px"; // Width like .home-item-card
        imageWrapper.style.height = "220px"; // Height like .home-item-card
        imageWrapper.style.borderRadius = "8px"; // Border radius like home cards
        imageWrapper.style.boxSizing = "border-box"; // Include padding and border in size
        imageWrapper.style.verticalAlign = "top"; // Align frames to top to prevent vertical overlap
        imageWrapper.style.order = orderIndex; // Explicitly set flex order to insertion index
        imageWrapper.setAttribute('data-item-id', itemId);

        // Create the image element to fit inside the frame
        const img = document.createElement("img");
        img.src = imageUrl;
        img.alt = fileName || "Uploaded image";
        img.className = "uploaded-image";
        img.style.maxWidth = "100%"; // Max width like home-item-card img
        img.style.maxHeight = "100%"; // Max height like home-item-card img
        img.style.objectFit = "contain"; // Object fit contain like home
        img.style.imageRendering = "crisp-edges"; // Reduce blurring

        // Create the remove button
        const removeBtn = document.createElement("img");
        removeBtn.src = "remove_icon.png";
        removeBtn.alt = "Remove";
        removeBtn.className = "remove-btn";
        removeBtn.style.position = "absolute";
        removeBtn.style.top = "0";
        removeBtn.style.right = "0";
        removeBtn.style.width = "30px"; // Size scaled by 3 (10px * 3)
        removeBtn.style.height = "30px";
        removeBtn.style.cursor = "pointer";
        removeBtn.style.objectFit = "cover";
        removeBtn.style.zIndex = "10"; // Ensure it's on top
        removeBtn.style.backgroundColor = "rgba(255, 255, 255, 0.8)"; // Semi-transparent background for visibility
        removeBtn.style.borderRadius = "50%"; // Make it circular

        // Add click event to remove button
        removeBtn.addEventListener("click", function() {
            itemToDelete = imageWrapper;
            deleteModal.style.display = "flex";
        });

        // Append elements
        imageWrapper.appendChild(img);
        imageWrapper.appendChild(removeBtn);
        container.appendChild(imageWrapper);

        return imageWrapper; // Return for updating in handleFileSelect
    }

    // Function to load existing closet items for edit page
    async function loadClosetItemsForEdit() {
        try {
            const response = await fetch(EDIT_API_ENDPOINT);
            const payload = await response.json();

            if (payload.success && Array.isArray(payload.data)) {
                // Group items by category
                const grouped = payload.data.reduce((acc, item) => {
                    const category = normalizeCategory(item.category);
                    if (!acc[category]) acc[category] = [];
                    acc[category].push(item);
                    return acc;
                }, {});

                // Render each category
                Object.keys(grouped).forEach(category => {
                    grouped[category].forEach(item => {
                        addImageToCategory(category, item.image_url, item.category + ' item', item.item_id);
                    });
                });
            } else {
                console.error('Failed to load items:', payload.message);
            }
        } catch (error) {
            console.error('Error loading items:', error);
        }
    }

    // Attach event listener to file input
    fileInput.addEventListener("change", handleFileSelect);

    // Attach click event to add item cards to trigger file input
    addItemCards.forEach(card => {
        card.addEventListener("click", function() {
            const category = this.getAttribute("data-category");
            fileInput.setAttribute("data-category", category);
            fileInput.click();
        });
    });

    // Modal event listeners
    cancelDelete.addEventListener("click", function() {
        deleteModal.style.display = "none";
        itemToDelete = null;
    });

    confirmDelete.addEventListener("click", async function() {
        if (itemToDelete) {
            const itemId = itemToDelete.getAttribute('data-item-id');
            if (itemId) {
                try {
                    const response = await fetch(EDIT_API_ENDPOINT, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ item_id: itemId })
                    });
                    const result = await response.json();
                    if (!result.success) {
                        console.error('Delete failed:', result.message);
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                }
            }
            // Always remove locally for immediate feedback
            itemToDelete.remove();
            deleteModal.style.display = "none";
            itemToDelete = null;
        }
    });

    // Load existing items on page load
    loadClosetItemsForEdit();
});
