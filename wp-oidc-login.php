<?php

/*
 * Plugin Name:       WP OIDC Login
 * Plugin URI:        https://github.com/OTWSystems/wp-oidc-login
 * Description:       SSO via OIDC with optional permission mapping
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Author:            OTW Systems
 * Text Domain:       wp-oidc-login
 * Domain Path:       /languages
 */

declare(strict_types=1);

use OTWSystems\WpOidcLogin\Plugin;

define('WP_OIDC_LOGIN_VERSION', '1.0.0');

// Require loading through WordPress
if (!defined('ABSPATH')) {
    die;
}

// Load dependencies
if (file_exists(plugin_dir_path(__FILE__) . '/vendor/scoper-autoload.php')) {
    require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';
} elseif (file_exists(plugin_dir_path(__FILE__) . '/vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';
}

wpOidcLogin()->run();

/**
 * Return a configured singleton of the WP OIDC login plugin.
 *
 * @param null|Plugin $plugin Optional. An existing instance of OTWsystems\WpOidcLogin\Plugin to use.
 */
function wpOidcLogin(?Plugin $plugin = null): Plugin
{
    static $instance = null;

    $instance ??= $instance ?? $plugin ?? new Plugin();

    return $instance;
}
