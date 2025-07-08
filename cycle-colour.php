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
 * Text Domain: cycle-colour
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Tested up to: 6.3
 *  
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

// Include admin logic
require_once __DIR__ . '/admin.php';
