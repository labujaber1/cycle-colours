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

// Removing all options created by Cycle Colours plugin
delete_option('cycle_colours_toggle');
delete_option('cycle_colours_palettes');
delete_option('cycle_colours_palettes_interval');
delete_option('cycle_colours_div_array');
delete_option('cycle_colours_div_class');
delete_option('cycle_colours_div_style');
delete_option('cycle_colours_div_interval');
delete_option('cycle_colours_custom_colours');
delete_option('cycle_colours_current_palette_index');

// Removing the scheduled events
wp_clear_scheduled_hook('cycle_colours_div_task');
wp_clear_scheduled_hook('cycle_colours_palettes_task');
