document.addEventListener("DOMContentLoaded", () => {
    const toggleButton = document.getElementById("themeToggleButton");
    const body = document.body;
    const icon = document.querySelector(".fa-moon"); // Using querySelector for clarity

    // Check saved theme preference
    if (localStorage.getItem("theme") === "dark") {
        body.classList.add("dark-theme");
        // Update icon initially based on the stored theme
        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
    }

    // Toggle theme on button click
    toggleButton.addEventListener("click", () => {
        body.classList.toggle("dark-theme");

        // Save the theme preference
        if (body.classList.contains("dark-theme")) {
            localStorage.setItem("theme", "dark");
            icon.classList.remove("fa-moon");
            icon.classList.add("fa-sun");
        } else {
            localStorage.setItem("theme", "light");
            icon.classList.remove("fa-sun");
            icon.classList.add("fa-moon");
        }
    });
});
function goBack() {
    window.history.back(); // This will take the user back to the previous page
}
