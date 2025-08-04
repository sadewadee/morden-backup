<?php
/**
 * Plugin Name: Morden Backup
 * Plugin URI: https://github.com/sadewadee/morden-backup
 * Description: Advanced backup, restore, and migration plugin for WordPress
 * Version: 1.0.0
 * Author: Morden Team
 * Author URI: https://github.com/sadewadee
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: morden-backup
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('MORDEN_BACKUP_VERSION', '1.0.0');
define('MORDEN_BACKUP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MORDEN_BACKUP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MORDEN_BACKUP_PLUGIN_FILE', __FILE__);

// Composer autoloader (if exists)
if (file_exists(MORDEN_BACKUP_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once MORDEN_BACKUP_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize plugin
function morden_backup_init() {
    // Load text domain for translations
    load_plugin_textdomain('morden-backup', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Add admin menu
    add_action('admin_menu', 'morden_backup_admin_menu');

    // Add settings link
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'morden_backup_action_links');

    // Enqueue admin scripts and styles
    add_action('admin_enqueue_scripts', 'morden_backup_admin_assets');
}
add_action('plugins_loaded', 'morden_backup_init');

// Add admin menu
function morden_backup_admin_menu() {
    add_menu_page(
        __('Morden Backup', 'morden-backup'),
        __('Morden Backup', 'morden-backup'),
        'manage_options',
        'morden-backup',
        'morden_backup_admin_page',
        'dashicons-database',
        30
    );

    // Sub-menu pages
    add_submenu_page('morden-backup', __('Backup', 'morden-backup'), __('Backup', 'morden-backup'), 'manage_options', 'morden-backup-backup', 'morden_backup_backup_page');
    add_submenu_page('morden-backup', __('Restore', 'morden-backup'), __('Restore', 'morden-backup'), 'manage_options', 'morden-backup-restore', 'morden_backup_restore_page');
    add_submenu_page('morden-backup', __('Migration', 'morden-backup'), __('Migration', 'morden-backup'), 'manage_options', 'morden-backup-migration', 'morden_backup_migration_page');
    add_submenu_page('morden-backup', __('Settings', 'morden-backup'), __('Settings', 'morden-backup'), 'manage_options', 'morden-backup-settings', 'morden_backup_settings_page');
}

// Enqueue admin assets
function morden_backup_admin_assets($hook) {
    if (strpos($hook, 'morden-backup') !== false) {
        wp_enqueue_style('morden-backup-admin', MORDEN_BACKUP_PLUGIN_URL . 'assets/css/admin.css', [], MORDEN_BACKUP_VERSION);
        wp_enqueue_script('morden-backup-admin', MORDEN_BACKUP_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], MORDEN_BACKUP_VERSION, true);

        // Localize script
        wp_localize_script('morden-backup-admin', 'mordenBackup', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('morden_backup_nonce'),
            'strings' => [
                'backupStarted' => __('Backup started successfully!', 'morden-backup'),
                'restoreStarted' => __('Restore started successfully!', 'morden-backup'),
                'error' => __('An error occurred. Please try again.', 'morden-backup')
            ]
        ]);
    }
}

// Main admin page
function morden_backup_admin_page() {
    ?>
    <div class="wrap morden-backup-admin">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <div class="notice notice-info">
            <p><?php _e('🎉 Morden Backup plugin has been successfully installed and is ready for development!', 'morden-backup'); ?></p>
        </div>

        <div class="morden-backup-card">
            <h2><?php _e('🔧 Plugin Status', 'morden-backup'); ?></h2>
            <table class="widefat fixed striped">
                <tbody>
                    <tr>
                        <td><strong><?php _e('Version', 'morden-backup'); ?>:</strong></td>
                        <td><?php echo MORDEN_BACKUP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Plugin Directory', 'morden-backup'); ?>:</strong></td>
                        <td><code><?php echo MORDEN_BACKUP_PLUGIN_DIR; ?></code></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('PHP Version', 'morden-backup'); ?>:</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('WordPress Version', 'morden-backup'); ?>:</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Memory Limit', 'morden-backup'); ?>:</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="morden-backup-card">
            <h2><?php _e('🚀 Features (Ready for Development)', 'morden-backup'); ?></h2>
            <div class="morden-feature-grid">
                <div class="feature-item">
                    <h3>📦 Backup Engine</h3>
                    <p><?php _e('Manual and scheduled backups with smart file exclusion', 'morden-backup'); ?></p>
                </div>
                <div class="feature-item">
                    <h3>🔄 Restore Engine</h3>
                    <p><?php _e('One-click restore with automatic URL replacement', 'morden-backup'); ?></p>
                </div>
                <div class="feature-item">
                    <h3>🌐 Migration System</h3>
                    <p><?php _e('Token-based migration with zero downtime capabilities', 'morden-backup'); ?></p>
                </div>
                <div class="feature-item">
                    <h3>☁️ Remote Storage</h3>
                    <p><?php _e('SFTP, WebDAV, Backblaze, and pCloud integration', 'morden-backup'); ?></p>
                </div>
            </div>
        </div>

        <div class="morden-backup-card">
            <h2><?php _e('🛠️ Development Tools', 'morden-backup'); ?></h2>
            <ul class="dev-tools-list">
                <li>✅ PSR-4 autoloading structure</li>
                <li>✅ GitHub Actions CI/CD pipeline</li>
                <li>✅ PHPUnit testing framework</li>
                <li>✅ PHPCS code standards</li>
                <li>✅ VS Code SFTP configuration</li>
                <li>✅ WordPress.org compliance</li>
            </ul>
        </div>
    </div>
    <?php
}

// Placeholder pages
function morden_backup_backup_page() {
    echo '<div class="wrap"><h1>Backup</h1><p>Backup functionality will be implemented here.</p></div>';
}

function morden_backup_restore_page() {
    echo '<div class="wrap"><h1>Restore</h1><p>Restore functionality will be implemented here.</p></div>';
}

function morden_backup_migration_page() {
    echo '<div class="wrap"><h1>Migration</h1><p>Migration functionality will be implemented here.</p></div>';
}

function morden_backup_settings_page() {
    echo '<div class="wrap"><h1>Settings</h1><p>Settings page will be implemented here.</p></div>';
}

// Add action links
function morden_backup_action_links($links) {
    $settings_link = '<a href="admin.php?page=morden-backup">' . __('Dashboard', 'morden-backup') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Activation hook
register_activation_hook(__FILE__, 'morden_backup_activate');
function morden_backup_activate() {
    add_option('morden_backup_version', MORDEN_BACKUP_VERSION);
    add_option('morden_backup_settings', [
        'max_execution_time' => 300,
        'memory_limit' => '512M',
        'split_size' => 1024,
        'backup_retention' => 7,
        'log_level' => 'info'
    ]);

    // Create upload directory for backups
    $upload_dir = wp_upload_dir();
    $backup_dir = $upload_dir['basedir'] . '/morden-backup';
    if (!file_exists($backup_dir)) {
        wp_mkdir_p($backup_dir);
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'morden_backup_deactivate');
function morden_backup_deactivate() {
    wp_clear_scheduled_hook('morden_backup_scheduled_backup');
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'morden_backup_uninstall');
function morden_backup_uninstall() {
    delete_option('morden_backup_version');
    delete_option('morden_backup_settings');
}
