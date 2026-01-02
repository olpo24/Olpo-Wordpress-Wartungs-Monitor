<?php
/*
Plugin Name: WP Bridge Connector
Description: Connector für WP Maintenance Monitor.
Version: 1.0.0
*/

$api_key = 'YOUR_API_KEY_HERE';

add_action('rest_api_init', function () {
    register_rest_route('bridge/v1', '/status', [
        'methods' => 'GET',
        'callback' => function() {
            return [
                'version' => get_bloginfo('version'),
                'updates' => [
                    'counts' => ['plugins' => count(get_plugin_updates()), 'themes' => count(get_theme_updates())],
                    'plugin_names' => array_keys(get_plugin_updates())
                ]
            ];
        },
        'permission_callback' => 'wpbc_check'
    ]);

    register_rest_route('bridge/v1', '/get-login-url', [
        'methods' => 'GET',
        'callback' => function() {
            $admin = get_users(['role' => 'administrator'])[0];
            $token = bin2hex(random_bytes(20));
            update_option('wpbc_sso_' . $token, $admin->ID, false);
            return ['success' => true, 'login_url' => add_query_arg('bridge_sso', $token, admin_url())];
        },
        'permission_callback' => 'wpbc_check'
    ]);
});

add_action('init', function() {
    if(isset($_GET['bridge_sso'])) {
        $token = $_GET['bridge_sso'];
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
    return $request->get_header('X-Bridge-Key') === $api_key;
}
