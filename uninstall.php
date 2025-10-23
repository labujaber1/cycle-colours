<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    die;
    die;
}

// Remove options data, no tables created. 
// Schedules have been removed in deactivation process.

delete_option('cycle_colours_palettes');
delete_option('cycle_colours_palettes_interval');
delete_option('cycle_colours_current_palette_index');
delete_option('cycle_colours_current_palette');

delete_option('cycle_colours_style_uid');
delete_option('cycle_colours_div_class');
delete_option('cycle_colours_div_style');
delete_option('cycle_colours_div_interval');
delete_option('cycle_colours_custom_colours');
delete_option('cycle_colours_div_array');

delete_option('cycle_colours_divs_interval_None');
delete_option('cycle_colours_divs_interval_minute');
delete_option('cycle_colours_divs_interval_hourly');
delete_option('cycle_colours_divs_interval_daily');
delete_option('cycle_colours_divs_interval_weekly');
delete_option('cycle_colours_divs_interval_monthly');
delete_option('cycle_colours_inline_css_Array');
delete_option('cycle_colours_inline_css_None');
delete_option('cycle_colours_inline_css_minute');
delete_option('cycle_colours_inline_css_hourly');
delete_option('cycle_colours_inline_css_daily');
delete_option('cycle_colours_inline_css_weekly');
delete_option('cycle_colours_inline_css_monthly');
delete_option('cycle_colours_schedule_array');
delete_option('cycle_colours_child_files');
delete_option('cycle_colours_parent_files');
delete_option('cycle_colours_style_files');
delete_option('cycle_colours_style_files_data');
delete_option('cycle_colours_toggle');

// then sets message to show on plugins page after deactivation which would have been removed so doesn't get displayed..doh!
set_transient('cycle_colours_deactivation_message', 'cycle-colours', 120);
