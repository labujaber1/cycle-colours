<?php

/**
 * Uninstall the Cycle Colours plugin.
 *
 * This function is called when the plugin is uninstalled.
 * It removes the options and settings created by the plugin.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Removing all options and cron jobs created by Cycle Colours plugin
// No custom tables used in v1.0.0 so no need to drop any tables

$errors = [];

// Stop cron jobs (defensive: ensure function exists and capture errors)
if (function_exists('cycle_colours_stop_cron_jobs')) {
    error_log('cycle_colours_deactivate_plugin: calling cycle_colours_stop_cron_jobs().' . PHP_EOL, 3, error_log_file());
    $res = call_user_func('cycle_colours_stop_cron_jobs');
    if (is_wp_error($res)) {
        $errors[] = $res->get_error_message();
        error_log('cycle_colours_deactivate_plugin: stop_cron_jobs error - ' . $res->get_error_message() . PHP_EOL, 3, error_log_file());
    } elseif ($res === false) {
        $errors[] = 'Failed to stop cron jobs.';
        error_log('cycle_colours_deactivate_plugin: stop_cron_jobs returned false.' . PHP_EOL, 3, error_log_file());
    }
} else {
    $errors[] = 'Missing cycle_colours_stop_cron_jobs().';
    error_log('cycle_colours_deactivate_plugin: function cycle_colours_stop_cron_jobs not found.' . PHP_EOL, 3, error_log_file());
}

// Housekeeping for intervals (defensive)
if (function_exists('cycle_colours_intervals_housekeeping')) {
    error_log('cycle_colours_deactivate_plugin: calling cycle_colours_intervals_housekeeping().' . PHP_EOL, 3, error_log_file());
    $res = call_user_func('cycle_colours_intervals_housekeeping');
    if (is_wp_error($res)) {
        $errors[] = $res->get_error_message();
        error_log('cycle_colours_deactivate_plugin: intervals_housekeeping error - ' . $res->get_error_message() . PHP_EOL, 3, error_log_file());
    } elseif ($res === false) {
        $errors[] = 'Intervals housekeeping failed.';
        error_log('cycle_colours_deactivate_plugin: cycle_colours_intervals_housekeeping returned false.' . PHP_EOL, 3, error_log_file());
    }
} else {
    $errors[] = 'Missing cycle_colours_intervals_housekeeping().';
    error_log('cycle_colours_deactivate_plugin: function cycle_colours_intervals_housekeeping not found.' . PHP_EOL, 3, error_log_file());
}

// Remove all plugin options (defensive)
if (function_exists('cycle_colours_remove_all_options')) {
    error_log('cycle_colours_deactivate_plugin: calling cycle_colours_remove_all_options().' . PHP_EOL, 3, error_log_file());
    $res = call_user_func('cycle_colours_remove_all_options');
    if (is_wp_error($res)) {
        $errors[] = $res->get_error_message();
        error_log('cycle_colours_deactivate_plugin: remove all options error - ' . $res->get_error_message() . PHP_EOL, 3, error_log_file());
    } elseif ($res === false) {
        $errors[] = 'Failed to remove all options used by Cycle Colours.';
        error_log('cycle_colours_deactivate_plugin: cycle_colours_remove_all_options returned false.' . PHP_EOL, 3, error_log_file());
    }
} else {
    $errors[] = 'Missing cycle_colours_remove_all_options().';
    error_log('cycle_colours_deactivate_plugin: function cycle_colours_remove_all_options not found.' . PHP_EOL, 3, error_log_file());
}

// Set a clear transient for the admin notice. Use a descriptive message and longer expiry.
if (empty($errors)) {
    // success
    $msg = __('Cycle Colours deactivated: cron jobs stopped and all plugin options removed.', 'cycle-colours');
    set_transient('cycle_colours_deactivation_message', $msg, 120);
    error_log('cycle_colours_deactivate_plugin: deactivation successful.' . PHP_EOL, 3, error_log_file());
    return true;
}

// failure: record all errors and set transient for admin notice
$error_text = implode(' | ', $errors);
// Store error as option (persistent) and transient for immediate notice
update_option('cycle_colours_deactivation_error', wp_kses_post($error_text));
set_transient('cycle_colours_deactivation_error_transient', wp_kses_post($error_text), 300);
error_log('cycle_colours_deactivate_plugin: deactivation failed - ' . $error_text . PHP_EOL, 3, error_log_file());
return new WP_Error('deactivation_failed', $error_text);
