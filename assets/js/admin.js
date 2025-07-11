// This script handles the visibility of the cycle colours settings based on the selected toggle option.
// It also manages the custom colours selection, allowing users to pick colours and see the word "selected" appear next to each input.

document.addEventListener('DOMContentLoaded', function () {
    // Toggle visibility for palettes/div settings
    var toggle = document.getElementById('cycle-colours-toggle');
    var palettesSettings = document.getElementById('palettes-settings');
    var divSettings = document.getElementById('div-settings');
    var schedulesSettings = document.getElementById('schedules-settings');

    function updateVisibility() {
        if (toggle.value === 'palettes') {
            if (divSettings) divSettings.style.display = 'none';
            if (schedulesSettings) schedulesSettings.style.display = 'none';
            palettesSettings.style.display = 'block';
        }
        if (toggle.value === 'div') {
            if (palettesSettings) palettesSettings.style.display = 'none';
            if (schedulesSettings) schedulesSettings.style.display = 'none';
            divSettings.style.display = 'block';
        }
        if (toggle.value === 'schedules') {
            if (palettesSettings) palettesSettings.style.display = 'none';
            if (divSettings) divSettings.style.display = 'none';
            schedulesSettings.style.display = 'block';

        }
    }

    if (toggle) {
        toggle.addEventListener('change', updateVisibility);
        updateVisibility(); // Set initial state
    }
});

// Handles the div custom colours selection to save only colours selected by the user using input type=color
// and updates a hidden input with the JSON string of selected colours.
// Also manages the "selected" label visibility.
document.addEventListener('DOMContentLoaded', function () {
    const colourInputs = document.querySelectorAll('input[type="color"][name="custom_colours[]"]');
    const hiddenInput = document.getElementById('custom-colours-json');

    // Hide all 'selected' labels on page load
    colourInputs.forEach((input, idx) => {
        const selectedLabel = document.getElementById('selected-colour' + idx);
        if (selectedLabel) {
            selectedLabel.style.display = 'none';
        }
    });

    function updateColours() {
        const colours = [];
        
        colourInputs.forEach((input, idx) => {
            const selectedLabel = document.getElementById('selected-colour' + idx);
            console.log('Input ID:', input.id, 'Selected Label ID:', selectedLabel ? selectedLabel.id : 'None');
            if (selectedLabel.style.display !== 'none') {
            const val = input.value;
            console.log('Selected Colour:', val);
            colours.push(val);
            }
        });
        hiddenInput.value = JSON.stringify(colours);
    }

    colourInputs.forEach((input, idx) => {
        input.addEventListener('input', function () {
            // Show 'selected' when a colour is picked
            const selectedLabel = document.getElementById('selected-colour' + idx);
            if (selectedLabel) {
                selectedLabel.style.display = 'inline';
            }
            updateColours();
        });
    });

    // Initialize on page load
    updateColours();

    // On form submit, ensure hidden input is updated
    const form = hiddenInput.closest('form');
    if (form) {
        form.addEventListener('submit', updateColours);
    }

    // Reset Colours Button
    const resetBtn = document.getElementById('reset-colours-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            for (let i = 0; i < 4; i++) {
                const input = document.getElementById('custom-colour' + i);
                const select = document.getElementById('selected-colour' + i);
                if (input) {
                    input.value = '#000000'; // Set to default black
                    if (select) {
                        select.style.display = 'none'; // Hide the 'selected' text
                    }
                }
            }
            updateColours(); // Update the hidden input with the reset colours
        });
    }
});