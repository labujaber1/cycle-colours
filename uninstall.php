<?php

/**
 * Uninstall the Cycle Colours plugin.
 *
 * This function is called when the plugin is uninstalled.
 * It removes the options and settings created by the plugin.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Removing all options and cron jobs created by Cycle Colours plugin
// No custom tables used in v1.0.0 so no need to drop any tables
try {
    // remove all data saved in options 
    cycle_colours_remove_all_options();
    // stop all cron jobs
    cycle_colours_stop_cron_jobs();
    cycle_colours_intervals_housekeeping();
    set_transient('cycle_colours_uninstall_message', 'cycle-colours', 60);
} catch (\Throwable $th) {
    update_option('cycle_colours_uninstall_error', $th->getMessage());
}
