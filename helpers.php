<?php

/**
 * FUNCTIONS TO ASSIST WITH PALETTE PROCESSING.
 */


/**
 * Functions to get colour palette files from the theme styles and styles/colour directories.
 * The files are in json format and contain the colour palette information.
 * The files are merged and duplicates are removed.
 * The cleaned styles are returned as an associative arrays.
 * The data is then displayed on the frontend in a checklist for selection
 * 
 * @return array $style_titles Array of style titles.
 * @return array $style_all_files Array of all style files in json format for cycling colour selection.
 */
/*function cycle_colours_get_style_files()
{
    // get directory path for styles and styles/colors directories
    $get_style_directory = get_template_directory() . '/styles/';
    $get_style_directory_colors = get_template_directory() . '/styles/colors/';

    // search for .json files in each directory (using u.s spelling)
    $style_files = glob($get_style_directory . '*.json');
    $style_files_colors = glob($get_style_directory_colors . '*.json');
    if (empty($style_files) && empty($style_files_colors)) {
        add_action('admin_notices', function () {
            echo '<div class="error notice"><p>No colour palettes found in the theme. Please ensure your theme.json file contains a valid "color.palette" directory.</p></div>';
        });
        return [];
    }
    // merge both arrays of file names
    $style_all_files = array_merge($style_files, $style_files_colors);

    // write file content to a json format array
    $style_all_files = array_map(function ($file) {
        return json_decode(file_get_contents($file), true);
    }, $style_all_files);
    error_log(json_encode($style_all_files));
    return $style_all_files;
}*/


/**
 * Get all .json palette file paths from parent and child theme directories.
 *
 * @return array Associative array of [filename => full path].
 */
function cycle_colours_get_palette_file_paths()
{
    $parent_style_dir = get_template_directory() . '/styles/';
    $parent_colors_dir = get_template_directory() . '/styles/colors/';
    $child_style_dir = get_stylesheet_directory() . '/styles/';
    $child_colors_dir = get_stylesheet_directory() . '/styles/colors/';

    $get_files = function ($dir) {
        $result = [];
        if (is_dir($dir)) {
            foreach (glob($dir . '*.json') as $file) {
                $result[basename($file)] = $file;
            }
        }
        return $result;
    };

    $parent_files = array_merge(
        $get_files($parent_style_dir),
        $get_files($parent_colors_dir)
    );
    $child_files = array_merge(
        $get_files($child_style_dir),
        $get_files($child_colors_dir)
    );

    // Child files overwrite parent files with the same name
    return array_merge($parent_files, $child_files);
}

/**
 * Read and decode all palette files.
 *
 * @param array $file_paths Associative array of [filename => full path].
 * @return array Array of decoded JSON contents.
 */
function cycle_colours_decode_palette_files($file_paths)
{
    return array_map(function ($file) {
        return json_decode(file_get_contents($file), true);
    }, $file_paths);
}

/**
 * Main function to get all palette files as decoded arrays.
 *
 * @return array Array of all style files in json format for cycling colour selection.
 */
function cycle_colours_get_style_files()
{
    $file_paths = cycle_colours_get_palette_file_paths();

    if (empty($file_paths)) {
        add_action('admin_notices', function () {
            echo '<div class="error notice"><p>No colour palettes found in the theme or child theme. Please ensure your theme.json file contains a valid "color.palette" section.</p></div>';
        });
        return [];
    }

    return cycle_colours_decode_palette_files($file_paths);
}

/**
 * function: get parent style files return array
 * function: get child style files return array
 * 
 * function: args two arrays remove duplicates with child files prioritised
 *         call both functions above to an array
 *         for loop to compare files and remove duplicates
 */


/**
 * Deletes duplicates and empty files from the $style_files array
 * and returns a new array with the cleaned styles for palettes.
 *
 * @param array $style_files Array of style files in json format.
 *
 * @return array $cleaned_styles Array of cleaned style files without duplicates and empty files.
 */
function cycle_colours_delete_duplicate_files($style_files)
{
    // clean file, delete duplicates and remove empty files
    $cleaned_styles = [];
    $seen_titles = [];
    // Filter the $style_all_files array
    foreach ($style_files as $file) {
        // Check if the file is an array and validate its necessary keys
        if (is_array($file) && array_key_exists('title', $file) && !empty($file['title'])) {
            // If the title is unique and non-empty, add it to the cleaned styles
            if (!in_array($file['title'], $seen_titles)) {
                // Store it in cleaned styles array
                $cleaned_styles[] = $file;
                // Mark the title as seen to avoid duplicates
                $seen_titles[] = $file['title'];
            }
        }
    }
    return $cleaned_styles;
}


/**
 * Retrieves an array of unique style titles from the styles folder.
 *
 * The function calls internal functions to read the file names in the styles folder and
 * its sub-folders, delete duplicates and empty files, and returns an
 * array of the cleaned file names, referred to as style titles, ready to display on the frontend.
 *
 * @return array Array of cleaned style titles without duplicates and empty files.
 */
function cycle_colours_get_theme_style_titles()
{
    // Get files
    $style_files = cycle_colours_get_style_files();
    // Delete duplicates and empty files
    $cleaned_styles = cycle_colours_delete_duplicate_files($style_files);

    // If no palettes are found, return an empty array
    if (empty($cleaned_styles) || !is_array($cleaned_styles)) {
        add_action('admin_notices', function () {
            echo '<div class="error notice"><p>No colour palettes found.</p></div>';
        });
        return [];
    }
    // Create titles file array
    $style_titles = [];
    // Loop through cleaned styles and get titles
    foreach ($cleaned_styles as $key => $value) {
        // check if value already exists in the array
        if (!in_array($value['title'], $cleaned_styles)) {
            $style_titles[$key] = $value['title'];
        }
    }
    return $style_titles;
}

/**
 * Returns an array of unique style files from the styles folder.
 * The function reads the content of the styles folder and
 * its sub-folders, deletes duplicates and empty files, and returns an
 * array of the cleaned style files.
 *
 * @return array Array of cleaned style files without duplicates and empty files.
 */
function cycle_colours_get_theme_style_palettes()
{
    $style_files = cycle_colours_get_style_files();
    // Delete duplicates and empty files
    $cleaned_styles = cycle_colours_delete_duplicate_files($style_files);

    return $cleaned_styles;
}


/**
 * Retrieves the theme.json file contents.
 *
 * Retrieves the theme.json file contents and returns as an associative array.
 *
 * @return array The theme.json file contents as an associative array.
 */
function cycle_colours_get_theme_json_file_contents()
{
    $theme_json_file = get_template_directory() . '/theme.json';
    if (!file_exists($theme_json_file)) {
        return [];
    }
    $json_content = file_get_contents($theme_json_file);
    $variations_data = json_decode($json_content, true);

    return $variations_data;
}

/**
 * Saves the selected colour palette to the database as an option.
 *
 * @param array $palette The selected colour palette to be saved.
 *
 * @return void
 */
function cycle_colours_save_current_palette_option($palette)
{
    return update_option('cycle_colours_current_palette', $palette);
}

/**
 * Retrieves the theme.json file contents merged with the given colour palette.
 *
 * Retrieves the theme.json file contents as an associative array and merges the given colour palette
 * with it. The resulting array is returned.
 *
 * @param array $palette The colour palette to be merged with the theme's data.
 *
 * @return array The merged theme.json file contents as an associative array.
 */
function cycle_colours_get_merged_theme_json($palette)
{
    $theme_json_array = cycle_colours_get_theme_json_file_contents();
    if (!is_array($theme_json_array) || !is_array($palette)) {
        return $theme_json_array;
    }
    foreach ($palette as $key => $value) {
        if (isset($theme_json_array[$key]) && is_array($theme_json_array[$key]) && is_array($value)) {
            $theme_json_array[$key] = array_replace_recursive($theme_json_array[$key], $value);
        } else {
            $theme_json_array[$key] = $value;
        }
    }
    return $theme_json_array;
}

/**
 * Add the filter on every request to override theme.json with the merged palette using wp inbuilt function.
 */
add_action('after_setup_theme', function () {
    $palette = get_option('cycle_colours_current_palette');
    if (is_array($palette)) {
        $merged = cycle_colours_get_merged_theme_json($palette);
        add_filter('wp_theme_json_data_default', function () use ($merged) {
            return new WP_Theme_JSON($merged);
        }, 20, 1);
    }
});

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

/** PALETTE PROCESSING END */


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
 * Checks which intervals are currently in use and removes any schedules and options
 * that are not being used.
 *
 * This function is called on plugin deactivation or when the plugin is updated.
 * 
 * @return void
 */
function cycle_colours_intervals_housekeeping()
{
    // Get all intervals currently in use
    $div_array = get_option('cycle_colours_div_array', []);
    $interval_groups = cycle_colours_group_divs_by_interval($div_array);
    $active_intervals = array_keys($interval_groups);

    // Get all possible intervals from cron_schedules
    $schedules = apply_filters('cron_schedules', []);
    foreach ($schedules as $interval_key => $interval_data) {
        //if ($interval_key === '0') continue; // Skip disabled
        $hook = 'cycle_colours_div_task_' . $interval_key;
        // If this interval is not active, clear its schedule and options
        if (!in_array($interval_key, $active_intervals, true)) {
            wp_clear_scheduled_hook($hook);
            delete_option('cycle_colours_divs_interval_' . $interval_key);
            delete_option('cycle_colours_inline_css_' . $interval_key);
            delete_option('cycle_colours_schedule_array');
        }
    }
}

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
/** DIV PROCESSING END */

/**
 * MISCELLANEOUS FUNCTIONS.
 */

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
            echo '<p>Inline CSS for each group: Interval ' . $interval . ' minute(s) --> ' . $css . '</p>';
        }
    }
}
/**
 * Outputs debugging information to the page of last created, including the current toggle,
 * palettes and their interval, the current div class and style, the custom
 * colours and their interval, the div array, the current inline css, and the
 * next scheduled tasks. Using globals which are cleared and so expected to be empty.
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
    echo '<p>Next Scheduled Palettes Task: ' . esc_html(empty($di_timestamp_palettes) ? 'None' : date('H:i:s d-m-Y', $di_timestamp_palettes)) . '</p>';
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
