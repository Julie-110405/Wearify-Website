const HOME_API_ENDPOINT = 'http://localhost/Wearify-Website/api/v1/closet.php';

const DEFAULT_IMAGES = {
    'bg-upper': 'upper.png',
    'bg-lower': 'lower.png',
    'bg-shoes': 'shoes.png',
    'bg-eyewear': 'eyewear.png',
    'bg-bag': 'bag.png',
    'bg-headwear': 'headwear.png',
    'bg-accessory1': 'accessory1.png',
    'bg-accessory2': 'accessory2.png',
    'bg-socks': 'socks.png'
};

function openEditAccount() {
    document.getElementById('editAccountModal').style.display = 'block';
}

function closeEditAccount() {
    document.getElementById('editAccountModal').style.display = 'none';
}

// Close if clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editAccountModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

const HOME_CATEGORY_CONFIG = {
    upper: { containerId: 'home-upper-items', empty: 'No upper items yet. Please add some items!', slotIds: ['bg-upper'] },
    lower: { containerId: 'home-lower-items', empty: 'No lower items yet. Please add some items!', slotIds: ['bg-lower'] },
    shoes: { containerId: 'home-shoes-items', empty: 'No shoes yet. Please add some items!', slotIds: ['bg-shoes'] },
    eyewear: { containerId: 'home-eyewear-items', empty: 'No eyewear yet. Please add some items!', slotIds: ['bg-eyewear'] },
    bag: { containerId: 'home-bag-items', empty: '', slotIds: ['bg-bag'] },
    headwear: { containerId: 'home-headwear-items', empty: 'No headwear yet. Please add some items!', slotIds: ['bg-headwear'] },
    accessory: { containerId: 'home-accessory-items', empty: 'No accessories yet. Please add some items!', slotIds: ['bg-accessory1', 'bg-accessory2'] },
    socks: { containerId: 'home-socks-items', empty: 'No socks yet. Please add some items!', slotIds: ['bg-socks'] }
};

// Store selected items for each slot
let selectedItems = {};

const slides = {
    'rounded-upper': 'slide-upper',
    'rounded-lower': 'slide-lower',
    'rounded-shoes': 'slide-shoes',
    'rounded-eyewear': 'slide-eyewear',
    'rounded-bag': 'slide-bag',
    'rounded-headwear': 'slide-headwear',
    'rounded-accessory': 'slide-accessory',
    'rounded-socks': 'slide-socks'
};

let activeElement = null;
let itemToDelete = null;
const deleteModal = document.getElementById("deleteModal");
const cancelDelete = document.getElementById("cancelDelete");
const confirmDelete = document.getElementById("confirmDelete");

// Patch start
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
// Patch end

function groupItemsByCategory(items) {
    const grouped = items.reduce((acc, item) => {
        const category = normalizeCategory(item.category);
        if (!acc[category]) {
            acc[category] = [];
        }
        acc[category].push(item);
        return acc;
    }, {});
    console.log("Grouped items:", grouped);
    return grouped;
}

// Patch start
function showSlide(category) {
    Object.values(slides).forEach(slideId => {
        const slide = document.getElementById(slideId);
        if (slide) {
            slide.style.display = 'none';
        }
    });
    const activeSlide = document.getElementById(slides[category]);
    if (activeSlide) {
        activeSlide.style.display = 'block';
    }
}

function toggleColor(element) {
    if (activeElement && activeElement !== element) {
        activeElement.classList.remove('active');
    }
    
    if (activeElement !== element) {
        element.classList.add('active');
        activeElement = element;

        // Show the corresponding slide based on clicked tab class
        const classes = Array.from(element.classList);
        const categoryClass = classes.find(cls => Object.keys(slides).some(cat => cls.includes(cat))); 

        if (categoryClass) {
            const normalizedCategory = normalizeCategory(categoryClass);
            if (normalizedCategory) {
                console.log(`Active category changed to: ${normalizedCategory}`);
                showSlide(normalizedCategory);
            }
        }
    }
}

async function loadClosetItemsForHome() {
    try {
        const response = await fetch(HOME_API_ENDPOINT);
        const payload = await response.json();
        console.log("DEBUG: Fetched payload from API in loadClosetItemsForHome:", payload);

        if (!payload.success || !Array.isArray(payload.data)) {
            throw new Error('Unexpected response payload');
        }

        const grouped = groupItemsByCategory(payload.data);
        window.groupedItems = grouped; // Store globally for selection modal
        Object.keys(HOME_CATEGORY_CONFIG).forEach(category => {
            const items = grouped[category] || [];
            renderHomeCategory(category, items);
        });
    } catch (error) {
        console.error('Failed to load closet items for home:', error);
        Object.keys(HOME_CATEGORY_CONFIG).forEach(category => renderHomeCategory(category, []));
    }

}

function renderHomeCategory(category, items) {
    console.log(`Rendering ${items.length} items in category: ${category}`);
    const config = HOME_CATEGORY_CONFIG[category];
    if (!config) return;

    const container = document.getElementById(config.containerId);
    if (!container) return;

    container.innerHTML = '';

    if (!items.length) {
        container.innerHTML = `<p class="home-empty">${config.empty}</p>`;
        return;
    }
    
    items.forEach(item => container.appendChild(createHomeCard(item)));
}

function updatePreviewSlots(slotIds = [], items = []) {
    if (!slotIds.length) return;

    slotIds.forEach((slotId, index) => {
        const slot = document.getElementById(slotId);
        if (!slot) return;

        const previewItem = items[index] || items[0];

        if (previewItem) {
            slot.style.backgroundImage = `url(${previewItem.image_url})`;
        } else {
            const defaultImage = DEFAULT_IMAGES[slotId];
            if (defaultImage) {
                slot.style.backgroundImage = `url(${defaultImage})`;
            } else {
                slot.style.backgroundImage = '';
            }
        }
    });
}

function createHomeCard(item) {
    const imageWrapper = document.createElement("div");
    imageWrapper.style.display = "inline-block";
    imageWrapper.style.margin = "10px";
    imageWrapper.style.position = "relative";
    imageWrapper.style.padding = "0";
    imageWrapper.style.width = "240px";
    imageWrapper.style.height = "240px";
    imageWrapper.style.boxSizing = "border-box";
    imageWrapper.style.verticalAlign = "top";

    imageWrapper.style.border = "none";
    imageWrapper.style.boxShadow = "none";
    imageWrapper.style.cursor = "pointer";

    const img = document.createElement("img");
    img.src = item.image_url;
    img.alt = `${item.category} item`;
    img.style.width = "100%";
    img.style.height = "100%";
    img.style.objectFit = "cover";

    img.style.border = "2px solid #ffffff";
    img.style.borderRadius = "4px";
    img.style.boxShadow = "0 2px 4px rgba(0,0,0,0.2)";

    // Add click event to assign to square
    imageWrapper.addEventListener('click', () => assignItemToSquare(item));

    imageWrapper.appendChild(img);
    return imageWrapper;
}

// Function to open selection modal for a group
function openSelectionModal(groupId) {
    const category = getCategoryFromGroupId(groupId);
    if (!category) return;

    // Get items for this category
    const items = getItemsForCategory(category);
    if (!items.length) {
        alert(`No items available in ${category} category. Please add some items in Edit mode.`);
        return;
    }

    const selectionItems = document.getElementById('selectionItems');
    selectionItems.innerHTML = '';

    items.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.style.display = 'inline-block';
        itemDiv.style.margin = '10px';
        itemDiv.style.cursor = 'pointer';
        itemDiv.style.border = '2px solid #ddd';
        itemDiv.style.borderRadius = '4px';

        const img = document.createElement('img');
        img.src = item.image_url;
        img.alt = `${item.category} item`;
        img.style.width = '100px';
        img.style.height = '100px';
        img.style.objectFit = 'cover';

        itemDiv.appendChild(img);
        itemDiv.addEventListener('click', () => selectItem(groupId, item));
        selectionItems.appendChild(itemDiv);
    });

    document.getElementById('selectionModal').style.display = 'flex';
}

// Function to get category from group ID
function getCategoryFromGroupId(groupId) {
    const mapping = {
        'group-upper': 'upper',
        'group-lower': 'lower',
        'group-shoes': 'shoes',
        'group-eyewear': 'eyewear',
        'group-bag': 'bag',
        'group-headwear': 'headwear',
        'group-accessory1': 'accessory',
        'group-accessory2': 'accessory',
        'group-socks': 'socks'
    };
    return mapping[groupId];
}

// Function to get items for a category
function getItemsForCategory(category) {
    // This assumes we have the grouped items from loadClosetItemsForHome
    // We need to store them globally
    return window.groupedItems ? window.groupedItems[category] || [] : [];
}

// Function to select an item for a slot
function selectItem(groupId, item) {
    selectedItems[groupId] = item;
    const bgDiv = document.getElementById('bg-' + groupId.split('-')[1]);
    if (bgDiv) {
        bgDiv.style.backgroundImage = `url(${item.image_url})`;
        bgDiv.style.backgroundSize = 'cover';
        bgDiv.style.backgroundPosition = 'center';
    }
    document.getElementById('selectionModal').style.display = 'none';
}

// Function to switch to category from group click
function switchToCategoryFromGroup(groupId) {
    const categoryMapping = {
        'group-upper': '.rounded-upper',
        'group-lower': '.rounded-lower',
        'group-shoes': '.rounded-shoes',
        'group-eyewear': '.rounded-eyewear',
        'group-bag': '.rounded-bag',
        'group-headwear': '.rounded-headwear',
        'group-accessory1': '.rounded-accessory',
        'group-accessory2': '.rounded-accessory',
        'group-socks': '.rounded-socks'
    };

    const categoryButton = document.querySelector(categoryMapping[groupId]);
    if (categoryButton) {
        toggleColor(categoryButton);
    }
}

// Function to assign item to square based on category
function assignItemToSquare(item) {
    const category = normalizeCategory(item.category);
    const config = HOME_CATEGORY_CONFIG[category];
    if (!config || !config.slotIds.length) return;

    // For accessory, cycle through available slots
    let slotId;
    if (category === 'accessory') {
        // Find the first empty slot or the first one
        slotId = config.slotIds.find(id => !document.getElementById(id).style.backgroundImage) || config.slotIds[0];
    } else {
        slotId = config.slotIds[0];
    }

    const bgDiv = document.getElementById(slotId);
    if (bgDiv) {
        bgDiv.style.backgroundImage = `url(${item.image_url})`;
        bgDiv.style.backgroundSize = 'cover';
        bgDiv.style.backgroundPosition = 'center';
        // Store the selected item
        const groupId = 'group-' + slotId.split('-')[1];
        selectedItems[groupId] = item;
    }
}

// Function to remove item from slot
function removeItemFromSlot(category) {
    const groupId = 'group-' + category;
    delete selectedItems[groupId];
    const bgDiv = document.getElementById('bg-' + category);
    if (bgDiv) {
        const defaultImage = DEFAULT_IMAGES['bg-' + category];
        if (defaultImage) {
            bgDiv.style.backgroundImage = `url(${defaultImage})`;
        } else {
            bgDiv.style.backgroundImage = '';
        }
    }
}

function initializeDefaultImages() {
    Object.keys(DEFAULT_IMAGES).forEach(slotId => {
        const slot = document.getElementById(slotId);
        if (slot && !slot.style.backgroundImage) {
            slot.style.backgroundImage = `url(${DEFAULT_IMAGES[slotId]})`;
            slot.style.backgroundSize = 'cover';
            slot.style.backgroundPosition = 'center';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const defaultElement = document.querySelector('.rounded-upper');
    if (defaultElement) {
        defaultElement.classList.add('active');
        activeElement = defaultElement;
        showSlide('rounded-upper');
    }

    initializeDefaultImages();
    loadClosetItemsForHome();

    // Add event listeners for group divs
    const groupIds = ['group-upper', 'group-lower', 'group-shoes', 'group-eyewear', 'group-bag', 'group-headwear', 'group-accessory1', 'group-accessory2', 'group-socks'];
    groupIds.forEach(groupId => {
        const groupDiv = document.getElementById(groupId);
        if (groupDiv) {
            groupDiv.addEventListener('click', (e) => {
                // Switch to the corresponding category on the right side
                switchToCategoryFromGroup(groupId);
                // Open selection modal
                openSelectionModal(groupId);
            });
        }
    });

    // Add event listeners for remove icons
    const removeClasses = ['remove_upper', 'remove_lower', 'remove_shoes', 'remove_eyewear', 'remove_bag', 'remove_headwear', 'remove_accessory1', 'remove_accessory2', 'remove_socks'];
    removeClasses.forEach(className => {
        const removeIcon = document.querySelector('.' + className);
        if (removeIcon) {
            removeIcon.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent triggering group click
                const category = className.split('_')[1];
                removeItemFromSlot(category);
            });
        }
    });

    // Close selection modal
    const closeSelection = document.getElementById('closeSelection');
    if (closeSelection) {
        closeSelection.addEventListener('click', () => {
            document.getElementById('selectionModal').style.display = 'none';
        });
    }
});
