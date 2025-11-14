// Store the currently active element
let activeElement = null;

// Function to handle click event on shapes
function toggleColor(element) {
    if (activeElement && activeElement !== element) {
        activeElement.classList.remove("active"); // remove active from previous
    }

    // Toggle active state
    if (activeElement !== element) {
        element.classList.add("active");
        activeElement = element;
    }
}

// Run after the page fully loads
document.addEventListener("DOMContentLoaded", () => {
    // Select your default shape here
    const defaultElement = document.querySelector(".rounded-upper");

    if (defaultElement) {
        defaultElement.classList.add("active");
        activeElement = defaultElement;
    } else {
        console.warn("Warning: '.rounded-upper' was not found in the HTML.");
    }
});
