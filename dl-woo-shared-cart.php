<?php

/**
 * Plugin Name: Share Cart for WooCommerce
 * Description: Comparte el carrito entre usuarios.
 * Version: 0.0.2
 * Author: Daniel Lúcia
 * Author URI: http://www.daniellucia.es
 * textdomain: dl-woo-shared-cart
 * Requires Plugins: dl-ticket-manager
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/src/Plugin.php';

add_action('plugins_loaded', function () {

    load_plugin_textdomain('dl-woo-shared-cart', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $plugin = new DLWOOSharedCartPlugin();
    $plugin->init();
});
