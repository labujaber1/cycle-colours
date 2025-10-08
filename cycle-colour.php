<?php

/**
 * Plugin Name: Cycle Colours
 * Plugin URI: https://github.com/labujaber1/cycle-colours
 * Description: Alternate between theme colour palettes and/or specific div classes/ids for your website at a scheduled interval.
 * Version: 1.0.0
 * Author: Lawrence Abu-Jaber
 * Author URI: https://github.com/labujaber1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cycle-colours
 * Requires at least: 5.0
 * Requires PHP: 8.4.4
 * Tested up to: 6.3
 *  
 */
/*
Cycle Colours is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Cycle Colours is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Cycle Colours. If not, see <https://www.gnu.org/licenses/gpl-2.0.html>.
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
