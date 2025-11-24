const HOME_API_ENDPOINT = 'http://localhost/Wearify-Website/api/v1/closet.php';

const HOME_CATEGORY_CONFIG = {
    upper: { containerId: 'home-upper-items', empty: 'No upper items yet. Please add some items!', slotIds: ['bg-upper'] },
    lower: { containerId: 'home-lower-items', empty: 'No lower items yet. Please add some items!', slotIds: ['bg-lower'] },
    shoes: { containerId: 'home-shoes-items', empty: 'No shoes yet. Please add some items!', slotIds: ['bg-shoes'] },
    eyewear: { containerId: 'home-eyewear-items', empty: 'No eyewear yet. Please add some items!', slotIds: ['bg-eyewear'] },
    bag: { containerId: 'home-bag-items', empty: 'No bags yet. Please add some items!', slotIds: ['bg-bag'] },
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

function normalizeCategory(value = '') {
    return value.toString().trim().toLowerCase();
}

function showSlide(className) {
    document.querySelectorAll('.slide').forEach(slide => {
        slide.classList.remove('active-slide');
    });

    const slideId = slides[className];
    if (slideId) {
        document.getElementById(slideId).classList.add('active-slide');
    }
}

function toggleColor(element) {
    if (activeElement && activeElement !== element) {
        activeElement.classList.remove('active');
    }

    if (activeElement !== element) {
        element.classList.add('active');
        activeElement = element;
        showSlide(element.classList[0]);
    }
}

async function loadClosetItemsForHome() {
    try {
        const response = await fetch(HOME_API_ENDPOINT);
        const payload = await response.json();

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

function groupItemsByCategory(items) {
    return items.reduce((acc, item) => {
        const category = normalizeCategory(item.category);
        if (!acc[category]) acc[category] = [];
        acc[category].push(item);
        return acc;
    }, {});
}

function renderHomeCategory(category, items) {
    const config = HOME_CATEGORY_CONFIG[category];
    if (!config) return;

    const container = document.getElementById(config.containerId);
    if (!container) return;

    container.innerHTML = '';

    const sortedItems = items
        .slice()
        .sort((a, b) => (Number(b.item_id) || 0) - (Number(a.item_id) || 0));

    if (!sortedItems.length) {
        container.innerHTML = `<p class="home-empty">${config.empty}</p>`;
        return;
    }

    sortedItems.forEach(item => container.appendChild(createHomeCard(item)));
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

    async function loadUploadedPhotosRightSide() {
    try {
        const response = await fetch(HOME_API_ENDPOINT);
        const payload = await response.json();

        if (payload.success && Array.isArray(payload.data)) {
            const container = document.getElementById('uploadedPhotosList');
            if (!container) return;

            container.innerHTML = '';

            payload.data.slice().reverse().forEach(item => {
                const imageWrapper = document.createElement('div');
                imageWrapper.style.display = 'inline-block';
                imageWrapper.style.margin = '10px';
                imageWrapper.style.position = 'relative';
                imageWrapper.style.width = '240px';
                imageWrapper.style.height = '240px';
                imageWrapper.style.boxSizing = 'border-box';
                imageWrapper.style.verticalAlign = 'top';
                imageWrapper.style.border = 'none';
                imageWrapper.style.boxShadow = 'none';
                imageWrapper.style.padding = '0'; // remove padding

                const img = document.createElement('img');
                img.src = item.image_url;
                img.alt = item.category + ' item';
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                img.style.border = '2px solid #ffffff';  // add border here
                img.style.borderRadius = '4px';
                img.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)'; // subtle shadow
                img.style.imageRendering = 'crisp-edges';

                imageWrapper.appendChild(img);
                container.appendChild(imageWrapper);
            });
        } else {
            console.error('Failed to load uploaded photos for right side:', payload.message);
        }
    } catch (error) {
        console.error('Error loading uploaded photos for right side:', error);
    }
}

    loadUploadedPhotosRightSide();
});
