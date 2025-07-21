<?php

if (isset($_POST['submit_palettes']) && wp_verify_nonce($_POST['cycle_colours_palettes_nonce'], 'cycle_colours_set_palettes')) {

    // Check length of the selected palettes array for min 2 and max 4
    if (count($_POST['palettes']) < 2 || count($_POST['palettes']) > 4) {
        $error_message .= __('Number of selected palettes must be between 2 and 4.', 'cycle-colours') . PHP_EOL;
    } else {
        // Create for first time and update the settings from the form data on submit
        update_option('cycle_colours_toggle', 'palettes');
        update_option('cycle_colours_palettes', $_POST['palettes'] ?? []);
        update_option('cycle_colours_palettes_interval', sanitize_text_field($_POST['palettes_interval']));

        $message .= __('Palettes settings saved.', 'cycle-colours') . PHP_EOL;

        cycle_colours_schedule_event_palettes();
    }
}
