<?php

/**
 * Function to get the path to the error log file
 * @return string
 */
function error_log_file()
{
    $log_file = plugin_dir_path(__FILE__)  . '/error.log';
    // Check if the log file exists, if not create it
    if (!file_exists($log_file)) {
        file_put_contents($log_file, ''); // Create an empty file
    }
    return $log_file;
}
//error_log('Message to print in the log file.' . PHP_EOL, 3, error_log_file()); // For debugging purposes

/**
 * Prints the inline CSS for each group of divs by their interval settings.
 *
 * This function gets the grouped divs by their interval settings and then
 * prints the inline CSS for each group. It is for debugging purposes only.
 *
 * @return void
 */
function cycle_colours_print_css()
{
    $div_array = get_option('cycle_colours_div_array', []);
    $interval_groups = cycle_colours_group_divs_by_interval($div_array);
    foreach ($interval_groups as $interval => $group) {
        if ($interval !== '0') {
            $css = cycle_colours_create_inline_css($group);
            echo '<p>Inline CSS for each group: Interval ' . esc_html($interval) . ' --> ' . esc_html($css) . '</p>';
        }
    }
}
/**
 * Outputs debugging information to the page of last created, including the current toggle,
 * palettes and their interval, the current div class and style, the custom
 * colours and their interval, the div array, the current inline css, and the
 * next scheduled tasks. Using globals which are cleared and so expected to be empty.
 * 
 * Data now displayed as user info at the bottom of palettes and div pages.
 *
 * @param string $toggle The current toggle value.
 * @param array  $palettes The palettes array.
 * @param int    $palettes_interval The interval between palettes in minutes.
 * @param string $div_class The div class.
 * @param string $div_style The div style.
 * @param array  $custom_colours The custom colours array.
 * @param int    $div_interval The interval between div styles in minutes.
 * @param array  $div_array The div array.
 */
function display_debug_info($di_toggle, $di_palettes, $di_palettes_interval, $di_div_class, $di_div_style, $di_style_uid, $di_custom_colours, $di_div_interval, $di_div_array)
{
    $di_div_array = get_option('cycle_colours_div_array', []);
    echo '<div class="cycle-colours-debugging"><h3>Debugging Information</h3>';
    echo '<p>Debugging information will be displayed here.</p>';
    echo '<h2>Palettes</h2>';
    echo '<p>Current toggle: ' . esc_html($di_toggle) . '</p>';
    echo '<p>Palettes: ' . esc_html(implode(', ', $di_palettes)) . '</p>';
    echo '<p>Current Palette Index: ' . esc_html(get_option('cycle_colours_current_palette_index', 0)) . '</p>';
    echo '<p>Palettes Interval: ' . esc_html($di_palettes_interval) . ' minute(s)</p>';
    echo '<h2>Divs</h2>';
    echo '<p>The vars for the div just created</p>';
    echo '<p>Div Class: ' . esc_html($di_div_class) . '</p>';
    echo '<p>Div Style: ' . esc_html($di_div_style) . '</p>';
    echo '<p>Custom Colours: ' . esc_html(implode(', ', $di_custom_colours)) . '</p>';
    echo '<p>Div Interval: ' . esc_html($di_div_interval) . ' minute(s)</p>';
    echo '<h2>Div array, inline css and schedules</h2>';
    //echo '<pre>' . esc_html(print_r($di_div_array, true)) . '</pre>';
    //$arr = cycle_colours_get_interval_groups_array();
    //echo '<p>Interval groups array: ' . esc_html(print_r($arr, true)) . '</p>';
    echo esc_html(cycle_colours_print_css());
    $di_timestamp_palettes = wp_next_scheduled('cycle_colours_palettes_task');
    echo '<p>Next Scheduled Palettes Task: ' . esc_html(empty($di_timestamp_palettes) ? 'None' : gmdate('H:i:s d-m-Y', $di_timestamp_palettes)) . '</p>';
    foreach ($di_div_array as $di_class => $di_styles) {
        foreach ($di_styles as $di_style => $data) {
            echo '<p style="text-decoration: underline;"><strong>Div Class: ' . esc_html($di_class) . '</strong></p>';
            echo '<p>Div Style: ' . esc_html($di_style) . '</p>';
            echo '<p>Style UID: ' . esc_html($data['style_uid'] ?? '') . '</p>';
            echo '<p>Interval: ' . esc_html($data['interval'] ?? '') . '</p>';
            echo '<p>Colour index: ' . esc_html($data['current_colour_index'] ?? '') . '</p>';
            echo '<p>Current Colour: ' . esc_html($data['current_colour'] ?? '') . '</p>';
        }
    }
    echo '</div>';
}
