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
    $css_path = plugin_dir_path(__FILE__) . '/assets/css/admin-style.css';
    $css_url = plugin_dir_url(__FILE__) . '/assets/css/admin-style.css';
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'cycle-colours-admin-style',
            $css_url,
            [],
            filemtime($css_path)
        );
    }

    // JS
    $js_path = plugin_dir_path(__FILE__) . '/assets/js/admin.js';
    $js_url = plugin_dir_url(__FILE__) . '/assets/js/admin.js';
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
    // Check if form has been submitted
    if (isset($_POST['submit_palettes'])) {
        require_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/form-handlers/palette-handler.php';
    }

    if (isset($_POST['submit_div'])) {
        require_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/form-handlers/div-handler.php';
    }

    // Check if the reset button has been clicked for palettes or div
    // Reset the settings to default values
    if (isset($_POST['reset_palettes'])) {
        cycle_colours_reset_palettes();
        //$div_array = get_option('cycle_colours_div_array', []);
        $message .= __('Palettes have been reset.', 'cycle-colours') . PHP_EOL;
    }
    // Reset the settings to default values
    if (isset($_POST['delete_all_divs'])) {
        cycle_colours_delete_all_divs();
        //$div_array = get_option('cycle_colours_div_array', []);
        $message .= __('Div settings have been reset.', 'cycle-colours') . PHP_EOL;
    }

    // Process class deletion
    if (isset($_POST['delete_class_btn']) && !empty($_POST['delete_class_select'])) {
        require_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/form-handlers/delete-handler.php';
    }

    // Process style deletion
    if (isset($_POST['delete_class_style_btn']) || isset($_POST['stop_schedule_event_btn']) && !empty($_POST['delete_class_style_select'])) {
        require_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/form-handlers/delete-handler.php';
    }

    // Process palette schedule edit if checkbox selected
    if (
        isset($_POST['schedule_edit_palette'], $_POST['schedule_new_palette_interval'])
    ) {
        require_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/form-handlers/edit-schedule-handler.php';
    }

    // Process div schedule edit if checkbox selected
    if (
        isset($_POST['schedule_edit_div'], $_POST['schedule_new_div_interval']) &&
        is_array($_POST['schedule_edit_div']) &&
        is_array($_POST['schedule_new_div_interval'])
    ) {
        require_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/form-handlers/edit-schedule-handler.php';
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

    include CYCLE_COLOURS_PLUGIN_PATH . '/admin/forms/palettes-form.php';
    include CYCLE_COLOURS_PLUGIN_PATH . '/admin/forms/divs-form.php';
    include CYCLE_COLOURS_PLUGIN_PATH . '/admin/forms/schedules-form.php';

    // Display a debugging block
    if (defined('CYCLE_COLOURS_DEBUG') && CYCLE_COLOURS_DEBUG) {
        echo '<div class="cycle-colours-debugging">';
        display_debug_info($toggle, $palettes, $palettes_interval, $div_class, $div_style, $style_uid, $custom_colours, $div_interval, $div_array);
        echo '</div>'; // End of debugging block

    }
    echo '</div>'; // End of Cycle colours wrap 

}
