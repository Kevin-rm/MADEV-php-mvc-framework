/*
 * ==================================================
 * JS pour la fonction display_var
 * ==================================================
 */

document.addEventListener("DOMContentLoaded", function () {
    const toggleContainers = document.querySelectorAll('.display-var-container .toggle-container');

    toggleContainers.forEach(function (toggleContainer) {
        const toggleContainersChildren = Array.from(toggleContainer.children);

        const toggleButton              = toggleContainersChildren[0];
        const pre                       = toggleContainersChildren[1];

        if (pre.textContent.length > 1000) {
            pre.style.display        = "none";
            toggleButton.textContent = "Show";
        } else toggleButton.textContent = "Hide";

        toggleButton.addEventListener("click", () => {
            togglePre(pre, toggleButton);
        });
    });
});

/**
 * Bascule l'affichage du contenu <pre> et met à jour le texte du bouton de bascule.
 *
 * @param   {HTMLElement} pre          - L'élément <pre> dont l'affichage doit être basculé.
 * @param   {HTMLElement} toggleButton - Le bouton de bascule associé à l'élément <pre>.
 * @returns {void}
 */
function togglePre(pre, toggleButton) {
    if (pre.style.display              === "none") {
        pre.style.display               = "block";
        toggleButton.textContent        = "Hide";
        toggleButton.style.marginBottom = "10px";
    } else {
        pre.style.display               = "none";
        toggleButton.textContent        = "Show";
        toggleButton.style.marginBottom = "0";
    }
}
