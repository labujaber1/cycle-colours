<?php

/**
 * Plugin Deactivation
 */
/**
 * Called when the plugin is deactivated.
 *
 * Clears all settings related to palettes, the scheduled event for palettes and
 * all divs, and runs the housekeeping function for intervals.
 *
 * @return void
 */
function cycle_colours_deactivate_plugin()
{
    cycle_colours_reset_palettes();
    cycle_colours_delete_div_temp_data();
    // stop all cron jobs
    cycle_colours_stop_cron_jobs();
    //display message
    set_transient('cycle_colours_deactivation_message', true, 30);
}

function cycle_colours_stop_cron_jobs()
{
    wp_clear_scheduled_hook('cycle_colours_palettes_task');
    $sched_arr = get_option('cycle_colours_schedule_array', []);
    if (empty($sched_arr)) return false;
    foreach ($sched_arr as $interval => $hook) {
        wp_clear_scheduled_hook($hook);
    }
    return true;
}
/**
 * PALETTES
 */

/**
 * Resets the plugin settings related to the 'palettes' option.
 *
 * This function will clear any scheduled events related to palettes and
 * reset the plugin options for palettes to their default values.
 *
 * @return void
 */
function cycle_colours_reset_palettes()
{
    // Clear scheduled events
    wp_clear_scheduled_hook('cycle_colours_palettes_task');
    // Reset plugin options for palettes
    delete_option('cycle_colours_palettes');
    delete_option('cycle_colours_palettes_interval');
    delete_option('cycle_colours_current_palette_index');
}

/**
 * DIVS
 * 
 */

/**
 * Retrieves the style UID for a given div class and style.
 *
 * This function checks the stored div array for the specified params class and style,
 * returning the associated style UID if it exists. If not found, it returns false.
 *
 * @param string $div_class The CSS class or ID of the div.
 * @param string $div_style The CSS style to retrieve the UID for.
 *
 * @return mixed The style UID if found, otherwise false.
 */
function cycle_colours_get_style_uid($div_class, $div_style)
{
    $div_array = get_option('cycle_colours_div_array', []);
    if (isset($div_array[$div_class][$div_style]['style_uid'])) {
        return $div_array[$div_class][$div_style]['style_uid'];
    }
    return false;
}

/**
 * Retrieves the grouped divs by their interval settings from the div_array.
 *
 * This function groups the div array by their interval settings and then
 * saves the grouped divs to the database. It returns the grouped array.
 *
 * @return array The grouped array of divs by their interval settings.
 */
function cycle_colours_get_interval_groups_array()
{
    $div_array = get_option('cycle_colours_div_array', []);
    $interval_groups = cycle_colours_group_divs_by_interval($div_array);
    return $interval_groups;
}

/**
 * Resets temporary data used for cycling div colours.
 *
 * Deletes the options for current palette index, current palette, style UID, div class, div style, custom colours, and div interval.
 *
 * @return void
 */
function cycle_colours_delete_div_temp_data()
{
    // Reset the temporary data used for cycling colours
    delete_option('cycle_colours_style_uid');
    delete_option('cycle_colours_div_class');
    delete_option('cycle_colours_div_style');
    delete_option('cycle_colours_custom_colours');
    //delete_option('cycle_colours_div_interval');
}

/**
 * Resets all plugin settings for divs.
 *
 * Deletes the options for div class, style, interval, custom colours, and the div array.
 * Calls cycle_colours_create_inline_css() to remove any existing inline CSS.
 *
 * @return void
 */
function cycle_colours_delete_all_divs()
{
    //wp_clear_scheduled_hook('cycle_colours_div_task');
    delete_option('cycle_colours_style_uid');
    delete_option('cycle_colours_div_class');
    delete_option('cycle_colours_div_style');
    delete_option('cycle_colours_div_interval');
    delete_option('cycle_colours_custom_colours');
    delete_option('cycle_colours_div_array');
    cycle_colours_rerun_scheduled_events();
    cycle_colours_intervals_housekeeping();
    // Ensure the inline CSS is updated
    cycle_colours_create_inline_css();
}

/**
 * Deletes a specified div class from the div array.
 *
 * This function checks if a specified div class exists in the div array.
 * If found, it deletes the div class. If the div class is not found, it returns false.
 *
 * @param string $div_class The CSS class or ID of the div to delete.
 *
 * @return bool True if the div class was successfully deleted, false otherwise.
 */
function cycle_colours_delete_div_class($div_class)
{
    $div_array = get_option('cycle_colours_div_array', []);
    if (isset($div_array[$div_class])) {
        unset($div_array[$div_class]);
        update_option('cycle_colours_div_array', $div_array);
        // no need to remove the schedule event here as it will be removed in the schedule task
        return true;
    }
    return false;
}

/**
 * Deletes a specific style from a given div class in the div array.
 *
 * This function checks if a specified style exists for a given div class in
 * the div array. If found, it deletes the style. If the div class has no
 * styles left after deletion, it removes the entire class from the array.
 * The updated div array is then saved back to the options.
 *
 * @param array $class_style_array An associative array containing 'div_class'
 *                                 and 'style' keys, representing the class 
 *                                 and style to delete.
 *
 * @return bool True if the style was found and deleted, false otherwise.
 */

function cycle_colours_delete_div_class_style($class_style_array)
{
    $div_array = get_option('cycle_colours_div_array', []);

    if (isset($div_array[$class_style_array['div_class']][$class_style_array['style']])) {
        unset($div_array[$class_style_array['div_class']][$class_style_array['style']]);
        // If the class has no styles left, remove the class
        if (empty($div_array[$class_style_array['div_class']])) {
            unset($div_array[$class_style_array['div_class']]);
        }
        update_option('cycle_colours_div_array', $div_array);
        return true;
    }
    return false;
}

/**
 * Sorts the div array by interval order.
 *
 * This function takes the div array and sorts it by interval order, using the
 * interval order from the dropdown options. It returns a flattened array of
 * divs, with each row containing the div class, style, data, and interval 
 * to display in a table.
 *
 * @param array $div_array The div array to sort.
 *
 * @return array The sorted array of divs.
 */
function cycle_colours_sort_divs_by_interval($div_array)
{

    // Get the interval order from the dropdown options
    $interval_order = array_keys(cycle_colours_display_interval_options());
    // Flatten the div array
    $div_rows = [];
    foreach ($div_array as $class => $styles) {
        foreach ($styles as $style => $data) {
            $interval = $data['interval'] ?? '0';
            $div_rows[] = [
                'class'    => $class,
                'style'    => $style,
                'data'     => $data,
                'interval' => $interval,
            ];
        }
    }
    // Sort by interval order
    usort($div_rows, function ($a, $b) use ($interval_order) {
        $a_index = array_search($a['interval'], $interval_order, true);
        $b_index = array_search($b['interval'], $interval_order, true);
        // If not found, push to end
        if ($a_index === false) $a_index = count($interval_order);
        if ($b_index === false) $b_index = count($interval_order);
        return $a_index <=> $b_index;
    });

    return $div_rows;
}

/**
 * Save the grouped divs by their interval settings to the database.
 *
 * @param array $interval_groups The array of divs grouped by interval settings.
 * @return void
 */
function cycle_colours_save_interval_groups($interval_groups)
{
    foreach ($interval_groups as $interval => $group) {
        update_option('cycle_colours_divs_interval_' . $interval, $group);
    }
}
