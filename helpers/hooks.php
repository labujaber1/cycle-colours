<?php


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
 * Run the palettes task on every request
 */
add_action('cycle_colours_palettes_task', 'cycle_colours_palettes_task');


/**
 * Add actions for each interval to cycle through the divs.
 * This will create a separate action for each interval defined in the cron schedules.
 */
add_action('init', function () {
    //$timestamp = date('d-m-Y H:i:s', time()); // Get the current date and time
    //error_log('Initializing interval actions (add action). - ' . $timestamp . '.' . PHP_EOL, 3, error_log_file()); // For debugging purposes
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
 * Add inline styles for each interval
 */
add_action('wp_enqueue_scripts', function () {
    $schedules = apply_filters('cron_schedules', []);
    foreach ($schedules as $interval_key => $interval_data) {
        if ($interval_key === '0') continue;
        $css = get_option('cycle_colours_inline_css_' . $interval_key, '');
        if ($css) {
            wp_register_style('cycle-colours-frontend-inline-' . $interval_key, false, [], '1.0');
            wp_enqueue_style('cycle-colours-frontend-inline-' . $interval_key);
            wp_add_inline_style('cycle-colours-frontend-inline-' . $interval_key, $css);
        }
    }
});

/**
 * Display a success message on successful deactivation
 */
add_action('admin_notices', function () {
    $deact_message = get_transient('cycle_colours_deactivation_message');
    if (!empty($deact_message)) {
        // Display success message
        echo '<div class="notice notice-success is-dismissible">
                <p>Plugin deactivated successfully. All data and all scheduled events have been removed. Deleting the plugin will remove the source files so no trace is left that the plugin was ever used..sad but clean.</p>
              </div>';
        // Delete transient to prevent repeated display
        delete_transient('cycle_colours_deactivation_message');
    }
});

/**
 * Display an error message on deactivation
 */
add_action('admin_notices', function () {
    $error = get_option('cycle_colours_deactivation_error');
    if ($error) {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>An unexpected error occurred during deactivation:  </strong> ' . esc_html($error) . '</p>';
        echo '</div>';
        delete_option('cycle_colours_deactivation_error'); // clear after showing
    }
});

/**
 * Display a success message on successful uninstallation
 */
add_action('admin_notices', function () {
    $uninstall_message = get_transient('cycle_colours_uninstall_message');
    if (!empty($uninstall_message)) {
        // Display success message
        echo '<div class="notice notice-success is-dismissible">
                <p>Plugin uninstalled successfully. All data and all scheduled events have been removed. No trace is left that the plugin was ever used..sad but clean.</p>
              </div>';
        // Delete transient to prevent repeated display
        delete_transient('cycle_colours_uninstall_message');
    }
});

/**
 * Display an error message on uninstallation
 */
add_action('admin_notices', function () {
    $error = get_option('cycle_colours_uninstall_error');
    if ($error) {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>An unexpected error occurred during uninstallation:  </strong> ' . esc_html($error) . '</p>';
        echo '</div>';
        delete_option('cycle_colours_uninstall_error'); // clear after showing
    }
});
