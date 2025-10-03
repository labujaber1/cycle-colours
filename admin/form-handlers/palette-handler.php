<?php

if (isset($_POST['submit_palettes'], $_POST['palettes'], $_POST['palettes_interval'], $_POST['cycle_colours_palettes_nonce']) && wp_verify_nonce(sanitize_key($_POST['cycle_colours_palettes_nonce']), 'cycle_colours_set_palettes')) {

    // Check length of the selected palettes array for min 2 and max 4
    if (count(wp_unslash($_POST['palettes'])) < 2 || count(wp_unslash($_POST['palettes'])) > 4) {
        $error_message .= __('Number of selected palettes must be between 2 and 4.', 'cycle-colours') . PHP_EOL;
    } else {
        // Create for first time and update the settings from the form data on submit
        update_option('cycle_colours_toggle', 'palettes');
        update_option('cycle_colours_palettes', array_map('sanitize_text_field', wp_unslash($_POST['palettes'])) ?? []);
        update_option('cycle_colours_palettes_interval', sanitize_text_field(wp_unslash($_POST['palettes_interval'])));
        cycle_colours_schedule_event_palettes();
        if (wp_next_scheduled('cycle_colours_palettes_task')) {
            $message .= __('Palettes settings saved.', 'cycle-colours') . PHP_EOL;
        }
    }
}
