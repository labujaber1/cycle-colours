<?php

/**
 * Schedule the task to cycle through the selected colour palettes based on the interval set by the user.
 * 
 * Only schedule if data is present and the interval is not set to 0.
 * There is only ever one scheduled event for the palettes, so it clears the previous one.
 * 
 *
 * @return void
 */
function cycle_colours_schedule_event_palettes()
{
    $sch_p_interval = get_option('cycle_colours_palettes_interval');
    $sch_p_palettes = get_option('cycle_colours_palettes');
    wp_clear_scheduled_hook('cycle_colours_palettes_task');
    if ($sch_p_interval !== '0' && !empty($sch_p_palettes) && ! wp_next_scheduled('cycle_colours_palettes_task')) {
        wp_schedule_event(time(), $sch_p_interval, 'cycle_colours_palettes_task');
    } else {
        wp_clear_scheduled_hook('cycle_colours_palettes_task');
    }
}

/**
 * Schedule events for div tasks based on their interval settings. 
 * Keep number of schedules active to a maximum of the number of different intervals.
 *
 * @param array $interval_groups The array of divs grouped by interval settings.
 * @return void
 */
function cycle_colours_schedule_events_by_interval($interval_groups)
{
    $schedule_array = [];
    foreach ($interval_groups as $interval => $group) {
        if ($interval === '0') continue; // Skip disabled intervals
        $hook = 'cycle_colours_div_task_' . $interval;
        wp_clear_scheduled_hook($hook);
        wp_schedule_event(time(), $interval, $hook);
        $schedule_array[$interval] = $hook;
    }
    update_option('cycle_colours_schedule_array', $schedule_array);
}

/**
 * Checks which intervals are currently in use and removes any schedules and options
 * that are not being used.
 *
 * This function is used for housekeeping to remove any schedules and options that are no longer needed.
 * 
 * @return void
 */
function cycle_colours_intervals_housekeeping()
{
    // Get all intervals currently in use
    $div_array = get_option('cycle_colours_div_array', []);
    $interval_groups = cycle_colours_group_divs_by_interval($div_array);
    $active_intervals = array_keys($interval_groups);
    if (empty($active_intervals)) {
        return;
    }
    // Get all possible intervals from cron_schedules
    $schedules = apply_filters('cron_schedules', []);
    if (empty($schedules)) {
        return;
    }
    foreach ($schedules as $interval_key => $interval_data) {
        //if ($interval_key === '0') continue; // don'tSkip disabled
        $hook = 'cycle_colours_div_task_' . $interval_key;
        // If this interval is not active, clear its schedule and options
        if (!in_array($interval_key, $active_intervals, true)) {
            wp_clear_scheduled_hook($hook);
            if (get_option('cycle_colours_divs_interval_' . $interval_key) !== false) {
                delete_option('cycle_colours_divs_interval_' . $interval_key);
            }

            if (get_option('cycle_colours_inline_css_' . $interval_key) !== false) {
                delete_option('cycle_colours_inline_css_' . $interval_key);
            }
        }
    }
}

/**
 * Resets all scheduled events for div tasks and regenerates inline CSS for each group.
 *
 * This function is used to update the scheduled events and inline CSS when the div array is updated.
 * It groups the divs by their interval settings and saves the groups back to the options.
 * It then schedules events for each group and regenerates the inline CSS for each group.
 * 
 * @return void
 */
function cycle_colours_rerun_scheduled_events()
{
    $div_array = get_option('cycle_colours_div_array', []);
    $interval_groups = cycle_colours_group_divs_by_interval($div_array);
    cycle_colours_save_interval_groups($interval_groups);
    cycle_colours_schedule_events_by_interval($interval_groups);

    // Clear the inline CSS for all intervals
    foreach ($interval_groups as $interval => $group) {
        if ($interval !== '0') {
            $css = cycle_colours_create_inline_css($group);
            update_option('cycle_colours_inline_css_' . $interval, $css);
        }
    }
}
