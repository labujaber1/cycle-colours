<?php
// Display the palettes settings
echo '<div id="palettes-settings" style="display:' . ($toggle === 'palettes' ? 'block' : 'none') . ';">';

echo '<form method="post" action="">';
wp_nonce_field('cycle_colours_set_palettes', 'cycle_colours_palettes_nonce');
echo '<input type="hidden" name="action" value="cycle_colours_palettes_task">';
echo '<h3>Colour Palettes</h3>';
echo '<p>Select the theme palettes you want to cycle through and the desired interval for this to happen. 
The list of files below have been retrieved from within the styles directory and sub directories in the parent and child themes with duplicate
named files removed. 
All data in the .json files will be included so for themes with other styles in the same files such as font styles, these will be included as well.
Use a child theme to separate colour palettes if this is the case and is your wish or even add styles if you so wish. Give the file the same name as in the parent theme 
since the child takes precedence over the parent theme when removing duplicate named files.
 </p>';
echo '<p>On save the plugin will create a scheduled event to cycle through the selected palettes at the specified interval and keep doing it until the scheduled event is manually stopped. 
    Refresh the page on the frontend to see the changes if it is open, as it is executed on page load. 
    Resetting palettes removes data from the database and deletes the scheduled event but does not delete the theme colour files.</p>';
echo '<label for="palettes-select">Select Palettes (min 2, max 4):</label><br>';

// Prepare arrays from theme files 
$theme_palettes = cycle_colours_get_style_files_data(); // contains all theme palette arrays in full
$palette_titles = cycle_colours_get_theme_style_titles();

if (empty($theme_palettes)) {
    $error_message .= __('No colour palettes found in the themes styles or styles/colour. Please ensure your "palette.json" files are in these folders for the plugin to find.', 'cycle-colours') . PHP_EOL;
} else {
    // Display the palette names in a dropdown list    
    echo '<select name="palettes[]" id ="palette-select" multiple="multiple" size="10" title="Hold Ctrl to select multiple">';
    // Get the palette names and display them in a dropdown list
    foreach ($palette_titles as $slug => $name) {
        $selected = in_array($slug, $palettes) ? 'selected' : '';
        echo "<option value='" . esc_attr($slug) . "'" . esc_attr($selected) . ">" . esc_html($name) . "</option>";
        echo '<div class="cycle-colours-palette-preview" style="display: block;">';
        // Display each palette colours successively in a row block
        foreach ($theme_palettes[$slug]['settings']['color']['palette'] as $colour) {
            echo '<div style="background-color: ' . esc_attr($colour['color']) . '; width: 20px; height: 20px; display: inline-block;"></div>';
        }
        echo '</div>';
    }
    echo '</select><br><br>';
}

// Display the interval dropdown
echo '<label>Choose the required interval for the changes to happen:</label><br>';
echo '<select name="palettes_interval"><optgroup>';
foreach (cycle_colours_display_interval_options() as $value => $label) {
    $selected = $value === $palettes_interval ? 'selected' : '';
    echo "<option value='" . esc_attr($value) . "'" . esc_attr($selected) . ">" . esc_html($label) . "</option>";
}
echo '</optgroup></select><br><br>';

// Display the save button
echo '<input type="submit" name="submit_palettes" value="Save Settings" class="button button-primary">';
echo '<input type="submit" name="reset_palettes" value="Reset Palettes" class="button button-secondary" style="margin-left:1rem;" onclick="return confirm(\'Are you sure you want to reset the palettes?\');"><br>';
echo '<div class="cycle-colours-info">';
// Display the next scheduled task time
echo '<h3>Palette Scheduled Task</h3>';
$current_palette_timestamp = wp_next_scheduled('cycle_colours_palettes_task');
$saved_palettes = get_option('cycle_colours_palettes', []);
$saved_palettes_interval = get_option('cycle_colours_palettes_interval', '0');
echo '<p>Next Scheduled Palettes Task: ' . esc_html(empty($current_palette_timestamp) ? 'None' : gmdate('H:i:s d-m-Y ', $current_palette_timestamp)) . '</p>';
echo '<p>Palettes: ' . esc_html(implode(', ', $saved_palettes)) . '</p>';
echo '<p>Current Palette Index: ' . esc_html(get_option('cycle_colours_current_palette_index', 0)) . '</p>';
echo '<p>Palettes Interval: ' . esc_html($saved_palettes_interval) . '</p>';
echo '</div>'; // end of cycle-colours-info
echo '</form>'; // End of palettes form 
echo '</div>'; // End of palettes settings