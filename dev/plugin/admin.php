<?php


define('CYCLE_COLOURS_DEBUG', true); // Set to false in production

/**
 * Cycle Colours Plugin Admin Page
 *
 * This file contains the code for the admin page of the Cycle Colours plugin.
 * It includes functions to render the settings page, handle form submissions,
 * and manage the cycling of colours or palettes.
 *
 * @package CycleColours
 */
// Admin logic functions for the plugin
require_once __DIR__ . '/helpers.php';
register_deactivation_hook(__FILE__, 'cycle_colours_deactivate_plugin');

if (!defined('ABSPATH')) {
    exit;
}

// Register the admin menu, page title, menu text, capability, menu slug, and function to render the page.
add_action('admin_menu', function () {
    add_menu_page(
        'Cycle Colours',
        'Cycle Colours admin',
        'manage_options',
        'cycle-colours',
        'render_cycle_colours_page'
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_cycle-colours') {
        return;
    }

    // CSS
    $css_path = plugin_dir_path(__FILE__) . 'admin-style.css';
    $css_url = plugin_dir_url(__FILE__) . 'admin-style.css';
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'cycle-colours-admin-style',
            $css_url,
            [],
            filemtime($css_path)
        );
    }

    // JS
    $js_path = plugin_dir_path(__FILE__) . 'admin.js';
    $js_url = plugin_dir_url(__FILE__) . 'admin.js';
    if (file_exists($js_path)) {
        wp_enqueue_script(
            'cycle-colours-admin-js',
            $js_url,
            [],
            filemtime($js_path),
            true
        );
    }
});

/**
 * Function to render the settings page for the Cycle Colours plugin.
 * This function handles the display of the settings form and processes form submissions.
 * Uses functions from functions.php.
 *
 * @return void
 */
function render_cycle_colours_page()
{
    // Form(s) submission handling
    // Check if the user has the required capability to manage options
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    $message = '';
    $error_message = '';
    // Nonce field for security
    wp_nonce_field('cycle_colours_nonce', 'cycle_colours_nonce');
    // Check if form has been submitted
    if (isset($_POST['submit_palettes'])) {

        // Nonce field for security
        wp_nonce_field('cycle_colours_nonce', 'cycle_colours_nonce');
        if (empty($_POST['palettes'])) {
            $error_message .= __('No palettes selected. Please select at least 2 palettes.', 'cycle-colours') . PHP_EOL;
            continue;
        }
        // Check length of the selected palettes array for min 2 and max 4
        if (count($_POST['palettes']) < 2 || count($_POST['palettes']) > 4) {
            $error_message .= __('Number of selected palettes must be between 2 and 4.', 'cycle-colours') . PHP_EOL;
        } else {
            // Create for first time and update the settings from the form data on submit
            update_option('cycle_colours_toggle', 'palettes');
            update_option('cycle_colours_palettes', $_POST['palettes'] ?? []);
            update_option('cycle_colours_palettes_interval', sanitize_text_field($_POST['palettes_interval']));

            $message .= __('Palettes settings saved.', 'cycle-colours') . PHP_EOL;

            cycle_colours_schedule_event_palettes();
        }
    }

    if (isset($_POST['submit_div'])) {
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
            $error_message .= __('Save aborted, number of selected colours must be between 2 and 4.', 'cycle-colours') . PHP_EOL;
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
            $message .= __('Div settings saved.', 'cycle-colours') . PHP_EOL;
            // Clearing temp data
            cycle_colours_delete_div_temp_data();
        }
    }

    // Check if the reset button has been clicked for palettes or div
    // Reset the settings to default values
    if (isset($_POST['reset_palettes'])) {
        cycle_colours_reset_palettes();
        $div_array = get_option('cycle_colours_div_array', []);
        $message .= __('Palettes have been reset.', 'cycle-colours') . PHP_EOL;
    }
    // Reset the settings to default values
    if (isset($_POST['delete_all_divs'])) {
        cycle_colours_delete_all_divs();
        $div_array = get_option('cycle_colours_div_array', []);
        $message .= __('Div settings have been reset.', 'cycle-colours') . PHP_EOL;
    }

    // Process class deletion
    if (isset($_POST['delete_class_btn']) && !empty($_POST['delete_class_select'])) {
        $div_class = sanitize_text_field($_POST['delete_class_select']);
        $ans_class = cycle_colours_delete_div_class($div_class);
        if ($ans_class) {
            /* translators: %s: div class */
            $message .= sprintf(__('Div class %s and all styles has been deleted.', 'cycle-colours'), esc_html($div_class)) . PHP_EOL;
        } else {
            /* translators: %s: div class */
            $error_message .= sprintf(__('Failed to delete div class %s due to an error.', 'cycle-colours'), esc_html($div_class)) . PHP_EOL;
        }
        cycle_colours_rerun_scheduled_events();
    }

    // Process style deletion
    if (isset($_POST['delete_class_style_btn']) || isset($_POST['stop_schedule_event_btn']) && !empty($_POST['delete_class_style_select'])) {
        $parts = explode('|', $_POST['delete_class_style_select'], 2);
        if (count($parts) === 2) {
            list($div_class, $style) = $parts;
            $class_style_array = [
                'div_class' => $div_class,
                'style' => $style,
            ];

            if (isset($_POST['delete_class_style_btn'])) {
                $ans_style = cycle_colours_delete_div_class_style($class_style_array);
                if ($ans_style) {
                    /* translators: %1$s: style, %2$s: div class */
                    $message .= sprintf(__('The style %1$s for the class %2$s has been deleted.
                    If this is the last style for the class, the class will also be deleted.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
                } else {
                    /* translators: %1$s: style, %2$s: div class */
                    $error_message .= sprintf(__('Failed to delete div class %2$s and style %1$s due to an error.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
                }
            }
            if (isset($_POST['stop_schedule_event_btn'])) {
                $ans_style = cycle_colours_change_div_interval($class_style_array, 0);
                if ($ans_style) {
                    /* translators: %1$s: style, %2$s: div class */
                    $message .= sprintf(__('The interval for the style %1$s for the class %2$s has been stopped.',  'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
                } else {
                    /* translators: %1$s: style, %2$s: div class */
                    $error_message .= sprintf(__('Failed to stop interval for div class %2$s and style %1$s due to an error. Please try again.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
                }
                cycle_colours_rerun_scheduled_events(); // Rerun the scheduled events to update the divs
            }
        } else {
            $error_message .= __('Invalid class and style selection.', 'cycle-colours') . PHP_EOL;
        }
    }

    // Process palette schedule edit if checkbox selected
    if (
        isset($_POST['schedule_edit_palette'], $_POST['schedule_new_palette_interval'])
    ) {
        $new_palette_interval = sanitize_text_field($_POST['schedule_new_palette_interval']);
        update_option('cycle_colours_palettes_interval', $new_palette_interval);
        cycle_colours_schedule_event_palettes();
        update_option('cycle_colours_toggle', 'schedules');
        $message .= __('Palette schedule interval updated.', 'cycle-colours');
    }

    // Process div schedule edit if checkbox selected
    if (
        isset($_POST['schedule_edit_div'], $_POST['schedule_new_div_interval']) &&
        is_array($_POST['schedule_edit_div']) &&
        is_array($_POST['schedule_new_div_interval'])
    ) {
        foreach ($_POST['schedule_edit_div'] as $key => $checked) {
            $div_class = $_POST['div_class'][$key] ?? '';
            $div_style = $_POST['div_style'][$key] ?? '';
            $new_interval = sanitize_text_field($_POST['schedule_new_div_interval'][$key] ?? '');
            if ($div_class && $div_style && $new_interval !== '') {
                $class_style_array = [
                    'div_class' => $div_class,
                    'style' => $div_style,
                ];
                $result = cycle_colours_change_div_interval($class_style_array, $new_interval);
                if ($result) {
                    /* translators: %1$s: div class, %2$s: style, %3$s: interval */
                    $message .= sprintf(__('Interval updated for %1$s / %2$s with interval: %3$s.', 'cycle-colours'), esc_html($div_class), esc_html($div_style), esc_html($new_interval)) . PHP_EOL;
                } else {
                    /* translators: %1$s: div class, %2$s: style */
                    $error_message .= sprintf(__('Failed to update interval for %1$s / %2$s.', 'cycle-colours'), esc_html($div_class), esc_html($div_style)) . PHP_EOL;
                }
            }
        }
        update_option('cycle_colours_toggle', 'schedules');
        cycle_colours_rerun_scheduled_events();
    }

    // Get the current settings from the database
    // for palettes and divs
    $toggle = get_option('cycle_colours_toggle');
    // get user chosen palette id/index number
    $palettes = get_option('cycle_colours_palettes', []);
    // get interval time in minutes
    $palettes_interval = get_option('cycle_colours_palettes_interval', '0');
    // get user chosen div interval time in minutes
    $div_interval = get_option('cycle_colours_div_interval', '0');
    // get user chosen div class or id for specific div to change colour
    $div_class = get_option('cycle_colours_div_class', '');
    // get the div style to change
    $div_style = get_option('cycle_colours_div_style', '');
    // get the unique style id for the div
    $style_uid = get_option('cycle_colours_style_uid', '');
    // get user custom colours chosen for specific div to change colour
    $custom_colours = get_option('cycle_colours_custom_colours', []);
    // div array
    $div_array = get_option('cycle_colours_div_array', []);


    // Start the HTML for the settings page
    echo '<div class="cycle-colours-wrap">';

    echo '<div class="cycle-theme-palettes">';
    echo '<h1>Cycle Colours Plugin</h1>';

    // Dropdown toggle for mode selection
    echo '<label for="cycle-colours-toggle">Select mode:</label><br> ';
    echo '<select name="toggle" id="cycle-colours-toggle">';
    echo '<option value="palettes"' . selected($toggle, 'palettes', false) . '>Colour Palettes</option>';
    echo '<option value="div"' . selected($toggle, 'div', false) . '>Specific Div</option>';
    echo '<option value="schedules"' . selected($toggle, 'schedules', false) . '>Edit Schedules</option>';
    echo '</select><br><br>';
    // Display messages
    echo '<div class="cycle-colours-info">';
    if (!empty($message)) {
        echo '<div class="cycle-colours-messages"><p>' . esc_html($message) . '</p></div>';
    }
    if (!empty($error_message)) {
        echo '<div class="cycle-colours-errors"><p>' . esc_html($error_message) . '</p></div>';
    }
    echo '</div>'; // end of cycle-colours-info


    //include __DIR__ . '/forms/palettes-form.php';
    //include __DIR__ . '/forms/divs-form.php';
    //include __DIR__ . '/forms/settings-form.php';


    // Display the palettes settings
    echo '<div id="palettes-settings" style="display:' . ($toggle === 'palettes' ? 'block' : 'none') . ';">';

    echo '<form method="post" action="">';
    echo '<input type="hidden" name="action" value="cycle_colours_palettes_task">';
    echo '<h3>Select the theme palettes you want to cycle through and the desired interval for this to happen.</h3>';
    echo '<p>The plugin will create a scheduled event to cycle through the selected palettes at the specified interval and keep doing it until the scheduled event is manually stopped. 
    Refresh the page on the frontend to see the changes if it is open, as it is executed on page load. Resetting palettes removes data from the database 
    but does not delete the files.</p>';
    echo '<label for="palettes-select">Select Palettes (min 2, max 4):</label><br>';

    // Prepare arrays from theme files 
    $theme_palettes = cycle_colours_get_theme_style_palettes(); // contains all theme palette arrays in full
    $palette_titles = cycle_colours_get_theme_style_titles();

    if (empty($theme_palettes)) {
        $error_message .= __('No colour palettes found in the themes styles or styles/colour. Please ensure your "palette.json" files are in these folders for the plugin to find.', 'cycle-colours') . PHP_EOL;
    } else {
        // Display the palette names in a dropdown list    
        echo '<select name="palettes[]" id ="palette-select" multiple="multiple" size="10" title="Hold Ctrl to select multiple">';
        // Get the palette names and display them in a dropdown list
        foreach ($palette_titles as $slug => $name) {
            $is_selected = in_array($slug, $palettes) ? 'selected' : '';
            echo "<option value='" . esc_attr($slug) . "'" . esc_attr($is_selected) . ">" . esc_html($name) . "</option>";
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
    echo '<select name="palettes_interval">';
    foreach (cycle_colours_display_interval_options() as $value => $label) {
        $is_selected = ($value === $palettes_interval) ? 'selected' : '';
        echo "<option value='" . esc_attr($value) . "'" . esc_attr($is_selected) . ">" . esc_html($label) . "</option>";
    }
    echo '</select><br><br>';
    // Nonce field for security
    wp_nonce_field('cycle_colours_palettes_task', 'cycle_colours_task_nonce');

    // Display the save button
    echo '<input type="submit" name="submit_palettes" value="Save Settings" class="button button-primary">';
    echo '<input type="submit" name="reset_palettes" value="Reset Palettes" class="button button-secondary" style="margin-left:1rem;"><br>';

    // Display the next scheduled task time
    echo '<h3>Palette Scheduled Task</h3>';
    $palette_timestamp = wp_next_scheduled('cycle_colours_palettes_task');
    echo '<p>Next Scheduled Palettes Task: ' . esc_html(empty($palette_timestamp) ? 'None' : gmdate('H:i:s d-m-Y ', $palette_timestamp)) . '</p>';

    echo '</form>'; // End of palettes form 
    echo '</div>'; // End of palettes settings

    // Display the div settings 
    echo '<div id="div-settings" style="display:' . ($toggle === 'div' ? 'block' : 'none') . ';">';

    echo '<form method="post" action="">';
    echo '<input type="hidden" name="action" value="cycle_colours_div_task">';
    // Display the custom colours settings
    echo '<h3>Enter the details of the specific div you want to target.</h3>';
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
        echo '<label for="cycle-colours-custom-colours' . esc_attr($i) . '">Colour ' . (esc_attr($i) + 1) . '</label>';
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

    wp_nonce_field('cycle_colours_div_task', 'cycle_colours_task_nonce'); // Nonce field for security
    // Display the save button
    echo '<input type="submit" name="submit_div" value="Save Settings" class="button button-primary">';
    echo '</form>'; // End of div form

    echo '<h3>Manage Divs</h3>';
    // Display the reset button for divs
    echo '<form method="post" action="" style="display:inline;">';
    wp_nonce_field('cycle_colours_div_task', 'cycle_colours_task_nonce'); // Nonce field for security
    echo '<label for="delete_all_divs">## Delete all div styles and classes to start again. Only if you are sure.</label><br>';
    echo '<input type="submit" name="delete_all_divs" value="Delete All Classes and styles" class="button button-secondary" style="margin: 0.25rem 0.25rem;background-color: orange; color: white;"><br>';
    echo '</form>'; // End of reset form

    // Display the classes select dropdown
    echo '<form method="post" action="" style="display:inline;">';
    wp_nonce_field('cycle_colours_div_task', 'cycle_colours_task_nonce'); // Nonce field for security
    echo '<select name="delete_class_select" id="divs-select-class" style="margin-top: 1rem;">';
    foreach ($div_array as $div_class  => $styles) {
        $selected_class = $value === $div_class ? 'selected' : '';
        echo "<option value='" . esc_attr($div_class) . "'" . esc_attr($selected_class) . ">" . esc_html($div_class) . "</option>";
    }

    echo '</select>';
    echo '<input type="submit" name="delete_class_btn" value="Delete Class" class="button button-secondary" style="margin-left:1rem; margin-top: 1rem;background-color: blue; color: white;"><br>';
    echo '</form>'; // End of delete class form


    echo '<form method="post" action="" style="display:inline;">';
    wp_nonce_field('cycle_colours_div_task', 'cycle_colours_task_nonce'); // Nonce field for security

    // Display the styles select dropdown
    echo '<select name="delete_class_style_select" id="divs-select-style" style="margin-top: 1rem;">';
    foreach ($div_array as $div_class  => $styles) {
        foreach ($styles as $style => $data) {
            $selected_class_style = $value === "$div_class|$style" ? 'selected' : '';
            echo "<option value='" . esc_attr("$div_class|$style") . "'" . esc_attr($selected_class_style) . ">" . esc_html("$div_class --> $style") . "</option>";
        }
    }
    echo '</select>';
    echo '<input type="submit" name="delete_class_style_btn" value="Delete Style" class="button button-secondary" style="margin-left:1rem; margin-top: 1rem;background-color: blue; color: white;">';
    echo '<input type="submit" name="stop_schedule_event_btn" value="Stop Scheduled Event" class="button button-secondary" style="margin-left:1rem; margin-top: 1rem;background-color: red; color: white;"><br>';
    echo '<br><note>Note: Deleting classes and styles will also remove their scheduled events.</note><br>';
    echo '<note>Scheduled events are tied to styles and not classes. Stopping the scheduled event will actually remove it by setting the interval to 0, so restarting it can be done in the edit schedules section. 
        Changing data for a current div will overwrite the existing style data and not duplicate it. Schedules are removed and rescheduled for any changes to the interval. View active schedules below:</note>';
    echo '</form>'; // End of delete class style form
    echo '<div class="cycle-colours-schedule-display">';
    echo '<h3>Current Schedules</h3>';
    $arr = get_option('cycle_colours_schedule_array', []);
    if (!empty($arr)) {
        echo '<p>Number of div schedules found: ' . count($arr) . '</p>';
    }
    foreach ($arr as $interval => $hook) {
        $timestamp = wp_next_scheduled($hook);
        echo '<p>Next Scheduled Task for div_task_' . esc_html($interval) . ' at ' . esc_html(empty($timestamp) ? '-> : Not Scheduled.' : gmdate('H:i:s d-m-Y ', esc_html($timestamp))) . '</p>';
    }
    echo '</div>'; // End of schedule display
    echo '</div>'; // End of div settings

    // Display edit schedule settings
    echo '<div id="schedules-settings" style="display:' . ($toggle === 'schedules' ? 'block' : 'none') . ';">';

    echo '<h1>Edit Schedules</h1>
    <p>To keep things simple to use the schedules are controlled through the intervals. Select disable to remove a schedule and select any other to change it.
    Changing from daily to weekly will remove the existing schedule event and create a new one.</p>';

    echo '<form method="post" action="" style="display:inline;">';
    wp_nonce_field('cycle_colours_palettes_task', 'cycle_colours_task_nonce'); // Nonce field for security
    $palette_interval = get_option('cycle_colours_palettes_interval', 0);
    $palette_timestamp = wp_next_scheduled('cycle_colours_palettes_task');
    //echo '<p>Next Scheduled Palettes Task: ' . esc_html(empty($palette_timestamp) ? 'Not Scheduled.' : date('H:i:s d-m-Y ', $palette_timestamp)) . '</p>';
    echo '<h2>Palettes</h2>
    <table class="wp-list-table widefat fixed striped">
    <tr>
    <th>Select to change</th>
    <th>Scheduled Task</th>
    <th>Class/Style</th>
    <th>Interval</th>
    <th>New</th>
    <th>Scheduled Time</th>
    </tr>
    <tr>
    <td><input type="checkbox" name="schedule_edit_palette" value="cycle_colours_palettes_task" style="scale:0.75;"></td>
    <td>cycle_colours_palettes_task</td>
    <td>Palettes</td>
    <td>' . esc_html($palette_interval) . '</td>
    <td>
        <select name="schedule_new_palette_interval" style="font-size:0.75rem;">';
    foreach (cycle_colours_display_interval_options() as $option_value => $label) {
        $is_selected = ($palette_interval === $option_value) ? 'selected' : '';
        echo '<option value="' . esc_attr($option_value) . '" ' . esc_attr($is_selected) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>
    </td>
    <td>' . esc_html(empty($palette_timestamp) ? 'Not Scheduled.' : gmdate('H:i:s d-m-Y ', esc_html($palette_timestamp))) . '</td>
    </tr>
    </table>';
    echo '<div class="cycle-colours-buttons-wrap">';
    echo '<input type="submit" name="schedule_new_palette_interval_btn" value="Save" class="button button-secondary " style="background-color: green; color: white;margin: 1rem;">';
    echo '</div>'; // End of buttons
    echo '</form>';

    echo '<br>';

    echo '<form method="post" action="" style="display:inline;">
    <h2>Divs</h2>
    <table class="wp-list-table widefat fixed striped"><tr>
    <th>Select to change</th>
    <th>Scheduled Task</th>
    <th>Class/Style</th>
    <th class= "manager-column">Interval</th>
    <th>New</th>
    <th>Scheduled Time</th>
    </tr>';
    $unsorted_div_array = get_option('cycle_colours_div_array', []);
    $interval_order = array_keys(cycle_colours_display_interval_options());
    // display in a styled wp list table, ordered by the interval
    foreach ($interval_order as $dinterval) {
        foreach ($unsorted_div_array as $class => $styles) {
            foreach ($styles as $style => $data) {
                $div_interval = $data['interval'] ?? '0';
                if ((string)$div_interval !== (string)$dinterval) continue;
                $hook = 'cycle_colours_div_task_' . $div_interval;
                $div_timestamp = wp_next_scheduled($hook);
                $key = $class . '|' . $style;
                echo '<tr>
                    <td><input type="checkbox" name="schedule_edit_div[' . esc_attr($key) . ']" value="1" style="scale:0.75;"></td>
                    <td>' . esc_html($hook) . '</td>
                    <td>' . esc_html($class . '/' . $style) . '</td>
                    <input type="hidden" name="div_class[' . esc_attr($key) . ']" value="' . esc_attr($class) . '">
                    <input type="hidden" name="div_style[' . esc_attr($key) . ']" value="' . esc_attr($style) . '">
                    <td>' . esc_html($div_interval) . '</td>
                    <td><select name="schedule_new_div_interval[' . esc_attr($key) . ']" style="font-size:0.75rem;">';
                foreach (cycle_colours_display_interval_options() as $option_value => $label) {
                    $selected = ($div_interval === $option_value) ? 'selected' : '';
                    echo '<option value="' . esc_attr($option_value) . '" ' . esc_attr($selected) . '>' . esc_html($label) . '</option>';
                }
                echo '</select></td>
                <td>' . esc_html(empty($div_timestamp) ? 'Not Scheduled.' : gmdate('H:i:s d-m-Y ', $div_timestamp)) . '</td>
                </tr>';
            }
        }
    }
    echo '</table>';
    echo '<div class="cycle-colours-buttons-wrap">';
    echo '<input type="submit" name="schedule_new_div_interval_btn" value="Save" class="button button-secondary " style="background-color: green; color: white;margin: 1rem;">';
    echo '</div>'; // End of buttons
    echo '</div>'; // End schedules settings
    echo '</form>';

    // Display a debugging block
    if (defined('CYCLE_COLOURS_DEBUG') && CYCLE_COLOURS_DEBUG) {
        echo '<div class="cycle-colours-debugging">';
        display_debug_info($toggle, $palettes, $palettes_interval, $div_class, $div_style, $style_uid, $custom_colours, $div_interval, $div_array);
        echo '</div>'; // End of debugging block
    }
    echo '</div>'; // End of Cycle colours wrap 

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
add_action('cycle_colours_palettes_task', 'cycle_colours_palettes_task');

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
 * Add a custom interval for the cron job.
 */
add_filter('cron_schedules', function ($schedules) {
    $schedules['0'] = [
        'interval' => 0,
        'display'  => __('Disabled', 'cycle-colours'),
    ];
    $schedules['minute'] = [
        'interval' => 60,
        'display'  => __('Minute', 'cycle-colours'),
    ];
    $schedules['hourly'] = [
        'interval' => HOUR_IN_SECONDS,
        'display'  => __('Hourly', 'cycle-colours'),
    ];
    $schedules['daily'] = [
        'interval' => DAY_IN_SECONDS,
        'display'  => __('Daily', 'cycle-colours'),
    ];
    $schedules['weekly'] = [
        'interval' => WEEK_IN_SECONDS,
        'display'  => __('Weekly', 'cycle-colours'),
    ];
    $schedules['monthly'] = [
        'interval' => 30 * DAY_IN_SECONDS,
        'display'  => __('Monthly', 'cycle-colours'),
    ];
    return $schedules;
});


add_action('wp_enqueue_scripts', function () {
    $schedules = apply_filters('cron_schedules', []);
    foreach ($schedules as $interval_key => $interval_data) {
        if ($interval_key === '0') continue;
        $css = get_option('cycle_colours_inline_css_' . $interval_key, '');
        if ($css) {
            wp_register_style('cycle-colours-frontend-inline-' . $interval_key, false);
            wp_enqueue_style('cycle-colours-frontend-inline-' . $interval_key);
            wp_add_inline_style('cycle-colours-frontend-inline-' . $interval_key, $css);
        }
    }
});

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
    wp_clear_scheduled_hook('cycle_colours_palettes_task');
    cycle_colours_delete_all_divs();
    cycle_colours_intervals_housekeeping();
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

// Add actions for each interval to cycle through the divs.
// This will create a separate action for each interval defined in the cron schedules.
add_action('init', function () {
    $schedules = apply_filters('cron_schedules', []);
    foreach ($schedules as $interval_key => $interval_data) {
        if ($interval_key === '0') continue;
        $hook = 'cycle_colours_div_task_' . $interval_key;
        add_action($hook, function () use ($interval_key) {
            cycle_colours_div_task_by_interval($interval_key);
        });
    }
});

/**
 * Task to cycle through the divs based on their interval settings.
 *
 * This function retrieves the divs grouped by their interval settings and cycles through their colours.
 * It updates the current colour index and saves the updated styles back to the database.
 *
 * @param string $interval The interval for which to cycle the divs.
 * @return void
 */
function cycle_colours_div_task_by_interval($interval)
{
    $group = get_option('cycle_colours_divs_interval_' . $interval, []);
    $changed = false;
    foreach ($group as $div_class => &$styles) {
        foreach ($styles as $style => &$data) {
            if ($data['interval'] === '0') continue;
            $colours = $data['custom_colours_array'] ?? [];
            if (!empty($colours)) {
                $data['current_colour_index'] = ($data['current_colour_index'] + 1) % count($colours);
                $data['current_colour'] = $colours[$data['current_colour_index']];
                $changed = true;
            }
        }
    }
    if ($changed) {
        update_option('cycle_colours_divs_interval_' . $interval, $group);
    }
    // Generate and save inline CSS for this interval
    $css = cycle_colours_create_inline_css($group);
    update_option('cycle_colours_inline_css_' . $interval, $css);
}

/**
 * Create inline CSS for the divs based on their styles and current colours.
 *
 * This function generates CSS rules for each div class and style, applying the current colour.
 * It uses the stored divs array or the default one if not provided.
 *
 * @param array|null $divs The array of divs and their styles. If null, it uses the stored option.
 * @return string $inline_css The generated inline CSS string.
 */
function cycle_colours_create_inline_css($divs = null)
{
    if ($divs === null) {
        $divs = get_option('cycle_colours_div_array', []);
    }
    $inline_css = '';
    foreach ($divs as $div_class => $styles) {
        foreach ($styles as $style => $data) {
            if ($data['interval'] === '0') continue; // Skip if the interval is not set to 0 (disabled)
            $colours = $data['custom_colours_array'] ?? [];
            if (empty($colours) || !is_array($colours)) continue;
            $current_colour = $data['current_colour'] ?? '';
            if (!$current_colour) continue;
            $selector = str_starts_with($div_class, '#') ? esc_attr($div_class) : '.' . esc_attr($div_class);
            $inline_css .= sprintf(
                '%s { %s: %s !important; }' . PHP_EOL,
                $selector,
                esc_attr($style),
                esc_attr($current_colour)
            );
        }
    }
    return $inline_css;
}

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
