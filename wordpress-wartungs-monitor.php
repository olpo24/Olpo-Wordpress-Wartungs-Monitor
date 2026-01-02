<?php
/**
 * Plugin Name: WP Maintenance Monitor
 * Plugin URI: https://github.com/yourusername/wp-maintenance-monitor
 * Description: Zentrales Dashboard zur Verwaltung mehrerer WordPress-Instanzen mit Remote-Update-Funktionen
 * Version: 3.0.0
 * Author: Dein Name
 * License: GPL v3
 * Text Domain: wp-maintenance-monitor
 */

if (!defined('ABSPATH')) exit;

class WP_Maintenance_Monitor {
    
    private $table_sites;
    private $table_logs;
    private $version = '3.0.0';
    
    public function __construct() {
        global $wpdb;
        $this->table_sites = $wpdb->prefix . 'wpmm_sites';
        $this->table_logs = $wpdb->prefix . 'wpmm_logs';
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        add_action('wp_ajax_wpmm_get_status', array($this, 'ajax_get_status'));
        add_action('wp_ajax_wpmm_update_plugin', array($this, 'ajax_update_plugin'));
        add_action('wp_ajax_wpmm_update_theme', array($this, 'ajax_update_theme'));
        add_action('wp_ajax_wpmm_update_core', array($this, 'ajax_update_core'));
        add_action('wp_ajax_wpmm_add_site', array($this, 'ajax_add_site'));
        add_action('wp_ajax_wpmm_update_site', array($this, 'ajax_update_site'));
        add_action('wp_ajax_wpmm_delete_site', array($this, 'ajax_delete_site'));
        add_action('wp_ajax_wpmm_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_wpmm_set_display_mode', array($this, 'ajax_set_display_mode'));
        add_action('wp_ajax_wpmm_download_plugin', array($this, 'ajax_download_plugin'));
    }
    
    public function activate() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        
        $sql_sites = "CREATE TABLE IF NOT EXISTS {$this->table_sites} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            url varchar(255) NOT NULL,
            api_key varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";
        
        $sql_logs = "CREATE TABLE IF NOT EXISTS {$this->table_logs} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            site_id mediumint(9),
            action varchar(100) NOT NULL,
            details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY site_id (site_id)
        ) $charset;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_sites);
        dbDelta($sql_logs);
        
        update_option('wpmm_version', $this->version);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'WP Maintenance Monitor',
            'WP Monitor',
            'manage_options',
            'wp-maintenance-monitor',
            array($this, 'render_dashboard_page'),
            'dashicons-update-alt',
            30
        );
        
        add_submenu_page(
            'wp-maintenance-monitor',
            'Activity Logs',
            'Logs',
            'manage_options',
            'wp-maintenance-monitor-logs',
            array($this, 'render_logs_page')
        );
        
        add_submenu_page(
            'wp-maintenance-monitor',
            'Settings',
            'Settings',
            'manage_options',
            'wp-maintenance-monitor-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function enqueue_assets($hook) {
        if (strpos($hook, 'wp-maintenance-monitor') === false) return;
        
        wp_enqueue_style('wpmm-styles', plugins_url('assets/styles.css', __FILE__), array(), $this->version);
        wp_enqueue_script('wpmm-dashboard', plugins_url('assets/dashboard.js', __FILE__), array('jquery'), $this->version, true);
        
        wp_localize_script('wpmm-dashboard', 'wpmmData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpmm_nonce'),
            'display_mode' => get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) ? get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) : 'grid'
        ));
    }
    
    public function render_dashboard_page() {
        global $wpdb;
        $sites = $wpdb->get_results("SELECT * FROM {$this->table_sites} ORDER BY name ASC");
        $display_mode = get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) ? get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) : 'grid';
        
        include plugin_dir_path(__FILE__) . 'templates/dashboard.php';
    }
    
    public function render_logs_page() {
        global $wpdb;
        $logs = $wpdb->get_results(
            "SELECT l.*, s.name as site_name 
             FROM {$this->table_logs} l 
             LEFT JOIN {$this->table_sites} s ON l.site_id = s.id 
             ORDER BY l.created_at DESC 
             LIMIT 100"
        );
        
        include plugin_dir_path(__FILE__) . 'templates/logs.php';
    }
    
    public function render_settings_page() {
        $display_mode = get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) ? get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) : 'grid';
        include plugin_dir_path(__FILE__) . 'templates/settings.php';
    }
    
    public function ajax_get_status() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        $site_id = intval($_GET['id']);
        $site = $this->get_site($site_id);
        
        if (!$site) {
            wp_send_json_error(array('message' => 'Site not found'));
        }
        
        $response = $this->api_request($site->url, '/status', $site->api_key);
        wp_send_json_success($response);
    }
    
    public function ajax_update_plugin() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        $site_id = intval($_POST['id']);
        $slug = sanitize_text_field($_POST['slug']);
        $site = $this->get_site($site_id);
        
        $response = $this->api_request($site->url, '/update-plugin', $site->api_key, array('slug' => $slug));
        
        $status = (isset($response['success']) && $response['success']) ? 'Erfolg' : 'Fehler';
        $this->log_activity($site_id, 'UPDATE_PLUGIN', "$status: $slug");
        
        wp_send_json($response);
    }
    
    public function ajax_update_theme() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        $site_id = intval($_POST['id']);
        $slug = sanitize_text_field($_POST['slug']);
        $site = $this->get_site($site_id);
        
        $response = $this->api_request($site->url, '/update-theme', $site->api_key, array('slug' => $slug));
        
        $status = (isset($response['success']) && $response['success']) ? 'Erfolg' : 'Fehler';
        $this->log_activity($site_id, 'UPDATE_THEME', "$status: $slug");
        
        wp_send_json($response);
    }
    
    public function ajax_update_core() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        $site_id = intval($_POST['id']);
        $site = $this->get_site($site_id);
        
        $response = $this->api_request($site->url, '/update-core', $site->api_key, array('execute' => true));
        
        $status = (isset($response['success']) && $response['success']) ? 'Erfolg' : 'Fehler';
        $this->log_activity($site_id, 'UPDATE_CORE', "$status: WordPress Core Update");
        
        wp_send_json($response);
    }
    
    public function ajax_add_site() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        global $wpdb;
        
        $name = sanitize_text_field($_POST['name']);
        $url = esc_url_raw($_POST['url']);
        $api_key = $this->generate_random_key();
        
        $wpdb->insert($this->table_sites, array(
            'name' => $name,
            'url' => $url,
            'api_key' => $api_key
        ));
        
        wp_send_json_success(array('api_key' => $api_key, 'site_id' => $wpdb->insert_id));
    }
    
    public function ajax_update_site() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        global $wpdb;
        
        $wpdb->update(
            $this->table_sites,
            array(
                'name' => sanitize_text_field($_POST['name']),
                'url' => esc_url_raw($_POST['url'])
            ),
            array('id' => intval($_POST['id']))
        );
        
        wp_send_json_success();
    }
    
    public function ajax_delete_site() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        global $wpdb;
        $wpdb->delete($this->table_sites, array('id' => intval($_POST['id'])));
        
        wp_send_json_success();
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        global $wpdb;
        $wpdb->query("DELETE FROM {$this->table_logs}");
        
        wp_send_json_success();
    }
    
    public function ajax_set_display_mode() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        $mode = sanitize_text_field($_POST['mode']);
        update_user_meta(get_current_user_id(), 'wpmm_display_mode', $mode);
        
        wp_send_json_success();
    }
    
    public function ajax_download_plugin() {
        check_ajax_referer('wpmm_nonce', 'nonce');
        
        $api_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
        
        $template_path = plugin_dir_path(__FILE__) . 'bridge-connector-template.php';
        
        if (!file_exists($template_path)) {
            wp_die('Template-Datei nicht gefunden. Bitte stelle sicher, dass bridge-connector-template.php im Plugin-Verzeichnis existiert.');
        }
        
        $plugin_content = file_get_contents($template_path);
        
        if (!empty($api_key)) {
            $plugin_content = str_replace('{{API_KEY}}', $api_key, $plugin_content);
        }
        
        $zip_filename = 'wp-bridge-connector.zip';
        $zip_path = sys_get_temp_dir() . '/' . $zip_filename;
        
        if (file_exists($zip_path)) {
            unlink($zip_path);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            wp_die('Konnte ZIP-Datei nicht erstellen.');
        }
        
        $zip->addFromString('wordpress-bridge-connector.php', $plugin_content);
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($zip_path);
        unlink($zip_path);
        
        exit;
    }
    
    private function get_site($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_sites} WHERE id = %d", $id));
    }
    
    private function generate_random_key() {
        return bin2hex(random_bytes(16));
    }
    
    private function log_activity($site_id, $action, $details) {
        global $wpdb;
        $wpdb->insert($this->table_logs, array(
            'site_id' => $site_id,
            'action' => $action,
            'details' => $details
        ));
    }
    
    private function api_request($url, $endpoint, $api_key, $post_data = null) {
        $full_url = rtrim($url, '/') . '/wp-json/bridge/v1' . $endpoint;
        
        $args = array(
            'headers' => array(
                'X-Bridge-Key' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30,
            'sslverify' => false
        );
        
        if ($post_data !== null) {
            $args['body'] = json_encode($post_data);
            $response = wp_remote_post($full_url, $args);
        } else {
            $response = wp_remote_get($full_url, $args);
        }
        
        if (is_wp_error($response)) {
            return array('error' => 'request_failed', 'details' => $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return array('error' => 'http_error', 'code' => $code);
        }
        
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}

new WP_Maintenance_Monitor();
