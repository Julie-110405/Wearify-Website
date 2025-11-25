const HOME_API_ENDPOINT = 'http://localhost/Wearify-Website/api/v1/closet.php';

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
            slot.style.backgroundImage = '';
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

    const img = document.createElement("img");
    img.src = item.image_url;
    img.alt = `${item.category} item`;
    img.style.width = "100%";
    img.style.height = "100%";
    img.style.objectFit = "cover";

    img.style.border = "2px solid #ffffff"; 
    img.style.borderRadius = "4px"; 
    img.style.boxShadow = "0 2px 4px rgba(0,0,0,0.2)";

    imageWrapper.appendChild(img);
    return imageWrapper;
}

document.addEventListener('DOMContentLoaded', () => {
    const defaultElement = document.querySelector('.rounded-upper');
    if (defaultElement) {
        defaultElement.classList.add('active');
        activeElement = defaultElement;
        showSlide('rounded-upper');
    }

    loadClosetItemsForHome();
});
