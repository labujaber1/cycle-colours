<?php

if (isset($_POST['submit_div'], $_POST['cycle_colours_save_div_nonce']) && wp_verify_nonce(sanitize_key($_POST['cycle_colours_save_div_nonce']), 'cycle_colours_save_div')) {
    // Get all the selected colours from all inputs type=color min-2 max-4 saved in the hidden input,
    // and store them in an array.
    // Use the JSON array from the hidden input
    $timestamp = date('d-m-Y H:i:s', time()); // Get the current date and time
    error_log('Processing div form submission. - ' . $timestamp . '.' . PHP_EOL, 3, error_log_file()); // For debugging purposes
    $custom_colours_array = [];
    if (!empty($_POST['custom_colours_json'])) {
        $colours_array = sanitize_text_field(wp_unslash($_POST['custom_colours_json']));
        $custom_colours_array = json_decode(stripslashes($colours_array), true);
        // filter out empty values
        $custom_colours_array = array_filter($custom_colours_array, function ($c) {
            return trim($c) !== '';
        });
        $custom_colours_array = array_values($custom_colours_array);
    }
    if (!is_array($custom_colours_array)) {
        $error_message .= __('Save aborted, colours data has not been prepared as an array.', 'cycle-colours') . PHP_EOL;
        return;
    }
    if (empty($_POST['div_class']) || empty($_POST['div_style'])) {
        $error_message .= __('Save aborted, class and style fields are empty.', 'cycle-colours') . PHP_EOL;
        return;
    }
    if (sizeof($custom_colours_array) < 2 || sizeof($custom_colours_array) > 4) {
        $error_message .= __('Save aborted, number of selected colours must be between 2 and 4.', 'cycle-colours') . PHP_EOL;
    } else {
        if (isset($_POST['div_interval'], $_POST['div_class'], $_POST['div_style'])) {

            error_log('Saving div settings to database in div handler.' . PHP_EOL, 3, error_log_file()); // For debugging purposes
            // Prepare vars for div_array update function
            $div_class = sanitize_text_field(wp_unslash($_POST['div_class']));
            $div_style = sanitize_text_field(wp_unslash($_POST['div_style']));
            $style_uid = uniqid('cycle-colours-style-'); // Generate a unique ID for the div
            $custom_colours_array; //array type
            $div_interval = sanitize_text_field(wp_unslash($_POST['div_interval']));

            // Save the settings in the database
            update_option('cycle_colours_toggle', 'div');
            update_option('cycle_colours_div_interval', $div_interval);
            update_option('cycle_colours_div_class', $div_class);
            update_option('cycle_colours_div_style', $div_style);
            update_option('cycle_colours_style_uid', $style_uid);
            update_option('cycle_colours_custom_colours', $custom_colours_array);

            // update the div array with new div data
            cycle_colours_update_div_array(
                $style_uid,
                $div_class,
                $div_style,
                $custom_colours_array,
                $div_interval,
                $current_colour_index = 0,
                $current_colour = $custom_colours_array[0]
            );
            cycle_colours_rerun_scheduled_events();
            // Clearing temp data
            cycle_colours_delete_div_temp_data();
            $message .= __('Div settings have been saved.', 'cycle-colours') . PHP_EOL;
        }
    }
}
