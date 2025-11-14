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
