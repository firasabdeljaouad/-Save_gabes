document.addEventListener("DOMContentLoaded", function () {
    const progressBar = document.querySelector(".progress-bar");
    const raisedText = document.querySelector(".balance span:first-child").innerText;
    const goalText = document.querySelector(".balance span:last-child").innerText;

    // Extraire les valeurs en nombres
    const raised = parseFloat(raisedText.replace(/[^0-9.]/g, ""));
    const goal = parseFloat(goalText.replace(/[^0-9.]/g, ""));

    if (goal > 0) {
        const percent = Math.min((raised / goal) * 100, 100); // max 100%

        // Appliquer au style
        progressBar.style.width = percent + "%";
        progressBar.setAttribute("aria-valuenow", percent);

        // Affichage du texte
        progressBar.querySelector(".progres_count").textContent = Math.round(percent) + "%";
    }
});
