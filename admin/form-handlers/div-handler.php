<?php

if (isset($_POST['submit_div'])) {
    //display popup checking message 


    // Get all the selected colours from all inputs type=color min-2 max-4 saved in the hidden input,
    // and store them in an array.
    // Use the JSON array from the hidden input
    $custom_colours_array = [];
    if (!empty($_POST['custom_colours_json'])) {
        $custom_colours_array = json_decode(stripslashes($_POST['custom_colours_json']), true);
        // filter out empty values
        $custom_colours_array = array_filter($custom_colours_array, function ($c) {
            return trim($c) !== '';
        });
        $custom_colours_array = array_values($custom_colours_array);
    }
    if (sizeof($custom_colours_array) < 2 || sizeof($custom_colours_array) > 4) {
        $error_message .= __('Save aborted, number of selected colours must be between 2 and 4.' . PHP_EOL);
    } else {
        update_option('cycle_colours_toggle', 'div');
        update_option('cycle_colours_div_interval', sanitize_text_field($_POST['div_interval']));
        update_option('cycle_colours_div_class', sanitize_text_field($_POST['div_class']));
        update_option('cycle_colours_div_style', sanitize_text_field($_POST['div_style']));

        update_option('cycle_colours_custom_colours', $custom_colours_array);

        // Prepare vars for div_array update function
        $div_class = sanitize_text_field($_POST['div_class']);
        $div_style = sanitize_text_field($_POST['div_style']);
        $style_uid = uniqid('cycle-colours-style-'); // Generate a unique ID for the div
        $custom_colours_array; //array type
        $div_interval = sanitize_text_field($_POST['div_interval']);
        update_option('cycle_colours_style_uid', $style_uid);
        // update the div array
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
        $message .= __('Div settings saved.' . PHP_EOL);
        // Clearing temp data
        cycle_colours_delete_div_temp_data();
    }
}
