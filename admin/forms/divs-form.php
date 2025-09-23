<?php
// Display the div settings 
echo '<div id="div-settings" style="display:' . ($toggle === 'div' ? 'block' : 'none') . ';">';

echo '<div class="create-divs" >';
// Display the custom colours settings
echo '<h2>Create Divs</h2>';
echo '<p>Use this section to target a specific div class or id and change its colour at your chosen interval.</p>';
echo '<button class="cycle-colours-toggle-button" onclick="toggleText(\'moreInfoDivs\')">More info... </button>';
echo '<div id="moreInfoDivs" class="cycle-colours-toggle-text">';
echo '<p>For best results, assign a unique id to your block in the page editor, as most WordPress blocks share classes 
and changes will affect all instances. To target an id, use the format #idName or combine it with a class 
(e.g., #idName.wp-block-button) depending on whether its your own block or an existing wp template block.
To apply multiple styles to the same class or id, enter it again with a different style property.</p>';

echo '<p>To find a class or id on an existing page, right-click the block in your browser and select "Inspect" to view the HTML or 
use the editor to view the source. Use the editor to assign an id to your block.</p>';

echo '<p>Enter any valid CSS style property (e.g., background-color, color, border) followed by a colon. 
The plugin will cycle through your selected colours for that property. 
To add more styles, enter the class or id again with a different property.</p>';

echo '<p>Schedules are set per style and grouped by interval. Each interval type will have its own scheduled event.
An inline CSS style block is generated for each interval type to apply all the associated styles.
The generated inline CSS is also shown at the end of this page for your information.</p>';
echo '</div>'; // End of more info


echo '<form method="post" action="">';
wp_nonce_field('cycle_colours_save_div', 'cycle_colours_save_div_nonce');
echo '<input type="hidden" name="action" value="cycle_colours_save_div">';

echo '<label for="div_class">Enter the div class or id (prefix # for id):</label><br>';
echo '<input type="text" name="div_class" id="div-class" value="' . esc_attr($div_class) . '" placeholder="wp-block-core-button"><br><br>';

// Get the div style name
echo '<label for="div_style">Add the style element:</label><br>';
echo '<input type="text" name="div_style" id="div-style" value="' . esc_attr($div_style) . '" placeholder="background-color: "><br><br>';

// Display the custom colours settings if toggle is set to div
echo '<label for="cycle-colours-custom-colours">Select up to 4 colours:</label><br>';
echo '<div class="cycle-colours-custom-colours">';
for ($i = 0; $i < 4; $i++) {
    $colour = $custom_colours[$i] ?? '#000000';
    echo '<label for="cycle-colours-custom-colours' . esc_attr($i) . '" id="cycle-colours-custom-colours-label-' . esc_attr($i) . '">Colour ' . (esc_attr($i) + 1) . '</label>';
    echo '<input type="color" name="custom_colours[]" id="custom-colour' . esc_attr($i) . '" value="' . esc_attr($colour) . '" style="margin: 0.25rem 0.5rem;"><span id="selected-colour' . esc_attr($i) . '" style="display: none; margin-left: 0.5rem;">selected</span><br>';
}
echo '<button type="button" id="reset-colours-btn" style="margin:0.5rem;">Reset Colours</button>';
echo '<input type="hidden" name="custom_colours_json" id="custom-colours-json" value="">';
echo '</div>'; // End of custom colours``

// Display the interval dropdown
echo '<label>Choose the required interval for the changes to happen:</label><br>';
echo '<select name="div_interval">';
foreach (cycle_colours_display_interval_options() as $value => $label) {
    $is_selected = ($value === $div_interval) ? 'selected' : '';
    echo "<option value='" . esc_attr($value) . "'" . esc_attr($is_selected) . ">" . esc_html($label) . "</option>";
}
echo '</select><br><br>';

// Display the save button
echo '<input type="submit" name="submit_div" value="Save Settings" class="button button-primary">';
echo '</form><br>'; // End of div form
echo '<p>Note: The plugin will generate inline css correctly as entered by you but it is possible that not all CSS properties may work as expected. This could be 
due to theme or block-specific styles taking precedence over inline css styles. If this occurs sadly it is beyond the scope of this plugin to resolve.
</p>'; // Note.'
echo '</div>'; // End of create divs

// Display the manage divs section 
echo '<div class="manage-divs">';
echo '<h2>Manage Divs</h2>';
// Display the classes select dropdown
echo '<form method="post" action="" style="display:inline;">';
wp_nonce_field('cycle_colours_div_remove_class_task', 'cycle_colours_div_remove_class_task_nonce'); // Nonce field for security
echo '<select name="delete_class_select" id="divs-select-class" style="margin-top: 1rem;">';
foreach ($div_array as $div_class  => $styles) {
    $is_selected_class = $value === $div_class ? 'selected' : '';
    echo "<option value='" . esc_attr($div_class) . "'" . esc_attr($is_selected_class) . ">" . esc_html($div_class) . "</option>";
}

echo '</select>';
echo '<input type="submit" name="delete_class_btn" value="Delete Class" onclick="return confirm(\'Are you sure you want to delete this class?\');" class="button button-secondary" style="margin-left:1rem; margin-top: 1rem;background-color: blue; color: white;"><br>';
echo '</form>'; // End of delete class form

echo '<form method="post" action="" style="display:inline;">';
wp_nonce_field('cycle_colours_delete_class_style', 'cycle_colours_delete_class_style_nonce'); // Nonce field for security

// Display the styles select dropdown
echo '<select name="delete_class_style_select" id="divs-select-style" style="margin-top: 1rem;">';
foreach ($div_array as $div_class  => $styles) {
    foreach ($styles as $style => $data) {
        $is_selected_class_style = $value === "$div_class|$style" ? 'selected' : '';
        echo "<option value='" . esc_attr("$div_class|$style") . "'" . esc_attr($is_selected_class_style) . ">" . esc_html("$div_class --> $style") . "</option>";
    }
}
echo '</select>';
echo '<input type="submit" name="delete_class_style_btn" value="Delete Style" onclick="return confirm(\'Are you sure you want to delete this style?\');" class="button button-secondary" style="margin-left:1rem; margin-top: 1rem;background-color: blue; color: white;">';
echo '<input type="submit" name="stop_schedule_event_btn" value="Stop Scheduled Event" onclick="return confirm(\'Are you sure you want to stop the scheduled event?\');" class="button button-secondary" style="margin-left:1rem; margin-top: 1rem;background-color: orange; color: white;"><br>';
echo '</form>'; // End of delete class style form

// Display the reset button for divs
echo '<form method="post" action="" style="display:inline;">';
wp_nonce_field('cycle_colours_div_all_delete_task', 'cycle_colours_div_all_delete_task_nonce'); // Nonce field for security
echo '<br><label for="delete_all_divs">## Delete all div styles and classes to start again. Only if you are sure.</label><br>';
echo '<input type="submit" name="delete_all_divs" value="Delete All Classes and styles" onclick="return confirm(\'Are you sure you want to delete all classes and styles?\');" class="button button-secondary" style="margin: 0.25rem 0.25rem;background-color: red; color: white;"><br>';
echo '</form>'; // End of reset form

echo '<br><note>Note: Deleting classes and styles will also remove their scheduled events.</note><br>';
echo '<note>Scheduled events are tied to styles and not classes. Stopping the scheduled event will actually remove it by setting the interval to 0, so restarting it can be done in the edit schedules section. 
Changing data for a current div will overwrite the existing style data and not duplicate it. Schedules are removed and rescheduled for any changes to the interval. View active schedules below:</note>';
echo '</div>'; // End of manage-divs

echo '<div class="cycle-colours-info">';
echo '<h3>Current Schedules</h3>';
$arr = get_option('cycle_colours_schedule_array', []);
if (empty($arr)) {
    echo '<p>No div schedules created.</p>';
}
if (!empty($arr)) {
    $di_div_array = get_option('cycle_colours_div_array', []);
    echo '<p>Number of div schedules found: ' . count($arr) . '</p>';
    foreach ($arr as $interval => $hook) {
        $is_timestamp = wp_next_scheduled($hook);
        echo '<p>Next Scheduled Task for div_task_' . esc_html($interval) . ' at ' . esc_html(empty($is_timestamp) ? '-> : Not Scheduled.' : gmdate('H:i:s d-m-Y ', esc_html($is_timestamp))) . '</p>';
    }
    echo esc_html(cycle_colours_print_css());
    foreach ($di_div_array as $di_class => $di_styles) {
        foreach ($di_styles as $di_style => $data) {
            echo '<br><p style="text-decoration: underline;"><strong>Div Class: ' . esc_html($di_class) . '</strong></p>';
            echo '<p>Div Style: ' . esc_html($di_style) . '</p>';
            echo '<p>Style UID: ' . esc_html($data['style_uid'] ?? '') . '</p>';
            echo '<p>Interval: ' . esc_html($data['interval'] ?? '') . '</p>';
            echo '<p>Colour index: ' . esc_html($data['current_colour_index'] ?? '') . '</p>';
            echo '<p>Current Colour: ' . esc_html($data['current_colour'] ?? '') . '</p>';
        }
    }
}

echo '</div>'; // End of schedule display
echo '</div>'; // End of div settings