<?php
/*
Plugin Name: WP Bridge Connector
Description: Connector für WP Maintenance Monitor.
Version: 1.0.3
*/

if (!defined('ABSPATH')) exit;

$api_key = 'YOUR_API_KEY_HERE';

add_action('rest_api_init', function () {
    register_rest_route('bridge/v1', '/status', [
        'methods' => 'GET',
        'callback' => 'wpbc_get_status',
        'permission_callback' => 'wpbc_check'
    ]);
    register_rest_route('bridge/v1', '/get-login-url', [
        'methods' => 'GET',
        'callback' => 'wpbc_get_login',
        'permission_callback' => 'wpbc_check'
    ]);
});

function wpbc_get_status() {
    // WICHTIG: Erforderliche WordPress-Dateien laden, damit Update-Funktionen verfügbar sind
    if ( ! function_exists( 'get_plugin_updates' ) ) {
        require_once ABSPATH . 'wp-admin/includes/update.php';
    }
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return [
        'version' => get_bloginfo('version'),
        'updates' => [
            'counts' => [
                'plugins' => count(get_plugin_updates()), 
                'themes' => count(get_theme_updates())
            ],
            'plugin_names' => array_keys(get_plugin_updates())
        ]
    ];
}

function wpbc_get_login() {
    $admins = get_users(['role' => 'administrator', 'number' => 1]);
    if (empty($admins)) return new WP_Error('no_admin', 'Kein Admin gefunden', ['status' => 404]);
    
    // Sicherstellen, dass random_bytes existiert (PHP 7+)
    $token = bin2hex(openssl_random_pseudo_bytes(20));
    update_option('wpbc_sso_' . $token, $admins[0]->ID, false);
    return ['success' => true, 'login_url' => add_query_arg('bridge_sso', $token, admin_url())];
}

add_action('init', function() {
    if(isset($_GET['bridge_sso'])) {
        $token = sanitize_text_field($_GET['bridge_sso']);
        $user_id = get_option('wpbc_sso_' . $token);
        if($user_id) {
            wp_set_auth_cookie($user_id);
            delete_option('wpbc_sso_' . $token);
            wp_redirect(admin_url());
            exit;
        }
    }
});

function wpbc_check($request) {
    global $api_key;
    $header_key = $request->get_header('X-Bridge-Key');
    return ($header_key && $header_key === $api_key);
}
