<?php

/**
 * Plugin Name: Cycle Colour
 * Plugin URI: https://github.com/ryba/cycle-colour
 * Description: Alternate between theme colour palettes and/or specific div classes/ids for your website at a scheduled interval.
 * Version: 1.0.0
 * Author: Lawrence Abu-Jaber
 * Author URI: https://github.com/labujaber1
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: cycle-colours
 * Requires at least: 5.0
 * Requires PHP: 8.4.4
 * Tested up to: 6.3
 *  
 */

define('CYCLE_COLOURS_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

// Include admin logic
require_once __DIR__ . '/admin.php';
require_once plugin_dir_path(__FILE__) . 'helpers/palettes.php';
require_once plugin_dir_path(__FILE__) . 'helpers/divs.php';
require_once plugin_dir_path(__FILE__) . 'helpers/get-set-reset.php';
require_once plugin_dir_path(__FILE__) . 'helpers/hooks.php';
require_once plugin_dir_path(__FILE__) . 'helpers/inline-css.php';
require_once plugin_dir_path(__FILE__) . 'helpers/scheduling-events.php';
require_once plugin_dir_path(__FILE__) . 'helpers/debug.php';
