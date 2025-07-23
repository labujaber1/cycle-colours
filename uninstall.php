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
// No custom tables used
try {
    cycle_colours_reset_palettes();
    delete_option('cycle_colours_style_files_data');
    delete_option('cycle_colours_style_files');
    delete_option('cycle_colours_style_current_palette');
    wp_clear_scheduled_hook('cycle_colours_palettes_task');
    cycle_colours_delete_all_divs();
    delete_option('cycle_colours_toggle');
    cycle_colours_intervals_housekeeping();
} catch (\Throwable $th) {
    update_option('cycle_colours_uninstall_error', $th->getMessage());
}
