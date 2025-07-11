<?php

/**
 * FUNCTIONS TO ASSIST WITH PALETTE PROCESSING.
 */

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
 * Returns an array of interval options for the settings page.
 * The keys are the interval values in minutes, and the values are the display labels.
 *
 * @return array $interval_options Array of interval options.
 */
function cycle_colours_display_interval_options()
{
    return [
        '0' => 'Disabled',
        'minute' => '1 min (test)',
        'hourly' => 'Hourly',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
    ];
}


/**
 * Task to cycle through the selected colour palettes.
 *
 * This function retrieves the current palette index and cycles through the available palettes.
 * It updates the current palette option in the database and logs the success or failure of the operation.
 *
 * This task is typically triggered by a scheduled event to periodically change the colour palette.
 *
 * @return void
 */
function cycle_colours_palettes_task()
{
    $task_palettes = get_option('cycle_colours_palettes', []);
    $task_theme_palettes = cycle_colours_get_theme_style_palettes();
    $task_current_index = (int) get_option('cycle_colours_current_palette_index', 0);
    if (empty($task_palettes)) {
        return;
    }
    // Update the current index to cycle through the palettes
    $next_index = ($task_current_index + 1) % count($task_palettes);
    update_option('cycle_colours_current_palette_index', $next_index);
    $palette_slug = $task_palettes[$next_index];
    if (isset($task_theme_palettes[$palette_slug])) {
        cycle_colours_save_current_palette_option($task_theme_palettes[$palette_slug]);
    }
};
