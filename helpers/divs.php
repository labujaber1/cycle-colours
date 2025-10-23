<?php


/**
 * FUNCTIONS TO ASSIST WITH SINGLE DIV PROCESSING.
 */

/**
 * Checks if the required options for the div are set.
 *
 * Checks if the options for the div class, style and custom colours are set.
 * If any of them are empty, it returns false, otherwise true.
 *
 * @return bool True if all options are set, false otherwise.
 */
function cycle_colours_check_divs_have_data()
{
    $style_uid = get_option('cycle_colours_style_uid', '');
    $div_class = get_option('cycle_colours_div_class', '');
    $div_style = get_option('cycle_colours_div_style', '');
    $custom_colours = get_option('cycle_colours_custom_colours', []);
    if (empty($style_uid) || empty($div_class) || empty($div_style) || empty($custom_colours)) {
        return false;
    }
    return true;
}

/**
 * Updates the div array with the current class, style, and colour options, current colour index and current colour to use for the inline css.
 *
 * This function retrieves the current div class, style, colour array, 
 * current colour index, and the current colour from the 
 * options and updates the div array accordingly. If the class does not exist 
 * in the array, it adds a new entry with the specified style and colour. If 
 * the class exists, it updates the existing style or adds a new style with 
 * the current colour. The updated div array is then saved back to the options.
 *
 * @return array The updated div array containing classes, styles, and colours.
 */
function cycle_colours_update_div_array($style_uid, $div_class, $div_style, $colours_array, $interval, $current_colour_index, $current_colour)
{
    $div_array = get_option('cycle_colours_div_array', []);
    if (!isset($div_array[$div_class]) || !is_array($div_array[$div_class])) {
        // If the class does not exist, create a new entry
        $div_array[$div_class] = [];
    }
    $div_array[$div_class][$div_style] = [
        'style_uid' => $style_uid,
        'custom_colours_array' => $colours_array,
        'interval' => $interval,
        'current_colour_index' => $current_colour_index,
        'current_colour' => $current_colour,
    ];

    // Save the updated div array to the options
    update_option('cycle_colours_div_array', $div_array);
}



/**
 * Group divs by their interval settings for better scheduled event management and display.
 *
 * @param array $div_array The array of divs and their styles to be grouped.
 * @return array $interval_groups The array of divs grouped by interval settings.
 */
function cycle_colours_group_divs_by_interval($div_array)
{
    $interval_groups = [];
    foreach ($div_array as $div_class => $styles) {
        foreach ($styles as $style => $data) {
            $interval = $data['interval'] ?? '0';
            if (empty($interval) || $interval === '0') continue; // Skip disabled
            if (!isset($interval_groups[$interval])) {
                $interval_groups[$interval] = [];
            }
            $interval_groups[$interval][$div_class][$style] = $data;
        }
    }
    return $interval_groups;
}



/**
 * Processes the div colour cycling task for a specific interval.
 *
 * This function retrieves the divs associated with the specified interval
 * and the main div array, updates their colour indexes, and saves the changes.
 * It also generates and saves the inline CSS for the updated divs.
 *
 * @param string $interval The interval to process.
 * @return void
 */
function cycle_colours_div_task_by_interval($interval)
{

    $interval_group = get_option('cycle_colours_divs_interval_' . $interval, []);
    $main_array = get_option('cycle_colours_div_array', []);
    if (empty($interval_group) || empty($main_array)) {
        return;
    }
    $group = cycle_colours_amend_colour_index_in_array($interval_group, $interval);
    $array = cycle_colours_amend_colour_index_in_array($main_array, $interval);

    // exit early before updates if no changes made
    if ($group === false || $array === false) {
        return;
    } else {
        update_option('cycle_colours_divs_interval_' . $interval, $group);
        update_option('cycle_colours_div_array', $array);

        // Generate and save inline CSS for this interval
        $css = cycle_colours_create_inline_css($group);
        update_option('cycle_colours_inline_css_' . $interval, $css);
    }
}

/**
 * Amends the colour index in the given array for the specified interval.
 *
 * This function iterates through the provided array and updates the current colour index
 * for each style that matches the specified interval. If a style's interval is '0', it is skipped.
 * The function returns the updated array if any changes were made, otherwise it returns false.
 *
 * @param array $array The array of divs and their styles to be updated.
 * @param string $interval The interval to match for updating the colour index.
 * @return array|false The updated array if changes were made, false otherwise.
 */
function cycle_colours_amend_colour_index_in_array($array, $interval)
{
    $new_array = [];
    $new_array = $array;
    $changed = false;
    foreach ($new_array as $div_class => &$styles) {
        foreach ($styles as $style => &$data) {
            if ($data['interval'] === '0') continue;
            $colours = $data['custom_colours_array'] ?? [];
            if (!empty($colours) && $data['interval'] === $interval) {
                $data['current_colour_index'] = ($data['current_colour_index'] + 1) % count($colours);
                $data['current_colour'] = $colours[$data['current_colour_index']];
                $changed = true;
            }
        }
    }
    if ($changed) {
        return $new_array;
    } else {
        return false;
    }
}
/**
 * FUNCTIONS TO ASSIST WITH EDIT SCHEDULE PROCESSING.
 */

/**
 * Changes the interval for a given div class and style in the div array.
 *
 * This function checks if the given div class and style exists in the div array.
 * If found, it updates the interval for that class and style. If not found, it returns false.
 * The updated div array is then saved back to the options.
 *
 * @param array $class_style_array An associative array containing 'div_class' and 'style' keys,
 *                                 representing the class and style to change the interval for.
 * @param string $interval The new interval to set for the given class and style.
 *
 * @return bool True if the interval was updated, false otherwise.
 */
function cycle_colours_change_div_interval($class_style_array, $interval)
{
    $div_array = get_option('cycle_colours_div_array', []);
    if (isset($div_array[$class_style_array['div_class']][$class_style_array['style']])) {
        $div_array[$class_style_array['div_class']][$class_style_array['style']]['interval'] = $interval;
        update_option('cycle_colours_div_array', $div_array);
        return true;
    } else {
        return false;
    }
}
