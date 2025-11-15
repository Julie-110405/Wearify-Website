let activeElement = null;
function toggleColor(element) {
    if (activeElement && activeElement !== element) {
        activeElement.classList.remove("active");
    }

    if (activeElement !== element) {
        element.classList.add("active");
        activeElement = element;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const defaultElement = document.querySelector(".rounded-upper");

    if (defaultElement) {
        defaultElement.classList.add("active");
        activeElement = defaultElement;
    } else {
        console.warn("Warning: '.rounded-upper' was not found in the HTML.");
    }
});

const slides = {
    "rounded-upper": "slide-upper",
    "rounded-lower": "slide-lower",
    "rounded-shoes": "slide-shoes",
    "rounded-eyewear": "slide-eyewear",
    "rounded-bag": "slide-bag",
    "rounded-headwear": "slide-headwear",
    "rounded-accessory": "slide-accessory",
    "rounded-socks": "slide-socks"
};

function showSlide(className) {
    document.querySelectorAll(".slide").forEach(slide => {
        slide.classList.remove("active-slide");
    });

    const slideId = slides[className];
    if (slideId) {
        document.getElementById(slideId).classList.add("active-slide");
    }
}

function toggleColor(element) {
    if (activeElement && activeElement !== element) {
        activeElement.classList.remove("active");
    }

    if (activeElement !== element) {
        element.classList.add("active");
        activeElement = element;

        showSlide(element.classList[0]);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const defaultElement = document.querySelector(".rounded-upper");

    if (defaultElement) {
        defaultElement.classList.add("active");
        activeElement = defaultElement;

        showSlide("rounded-upper");
    }
});
