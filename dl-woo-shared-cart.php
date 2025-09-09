<?php

/**
 * Plugin Name: Share Cart for WooCommerce
 * Description: Share the cart between users for WooCommerce.
 * Version: 0.0.2
 * Author: Daniel Lúcia
 * Author URI: http://www.daniellucia.es
 * textdomain: dl-woo-shared-cart
 * Requires Plugins: woocommerce
 */

use DL\SharedCart\Plugin;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

add_action('plugins_loaded', function () {

    load_plugin_textdomain('dl-woo-shared-cart', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $plugin = new Plugin();
    $plugin->init();
});
