function formatDate() {
    const now = new Date();
    const options = { 
        weekday: "long", 
        year: "numeric", 
        month: "long", 
        day: "numeric" 
    };
    return now.toLocaleDateString("id-ID", options);
}

document.addEventListener("DOMContentLoaded", function () {
    const dateElement = document.querySelector(".current-date");
    if (dateElement) {
        dateElement.textContent = formatDate();
    }
});
