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

use MordenBackup\Core\BackupEngine;
use MordenBackup\Core\RestoreEngine;
use MordenBackup\Migration\MigrationEngine;
use MordenBackup\Utils\Updater;

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

    // AJAX handlers
    add_action('wp_ajax_morden_backup_create', 'morden_backup_create_ajax_handler');
    add_action('wp_ajax_morden_backup_restore', 'morden_backup_restore_ajax_handler');
    add_action('wp_ajax_morden_backup_export', 'morden_backup_export_ajax_handler');
    add_action('wp_ajax_morden_backup_import', 'morden_backup_import_ajax_handler');
    add_action('wp_ajax_morden_backup_download_migration', 'morden_backup_download_migration_ajax_handler');
    add_action('wp_ajax_morden_backup_download_backup', 'morden_backup_download_backup_ajax_handler');
    add_action('wp_ajax_morden_backup_delete_backup', 'morden_backup_delete_backup_ajax_handler');

    // Initialize updater
    new Updater(MORDEN_BACKUP_PLUGIN_FILE);

    // Scheduled backup hook
    add_action('morden_backup_scheduled_backup', 'morden_backup_do_scheduled_backup');
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

// Backup page
function morden_backup_backup_page() {
    $backup_dir = wp_upload_dir()['basedir'] . '/morden-backup';
    $backups = file_exists($backup_dir) ? array_diff(scandir($backup_dir), ['..', '.']) : [];
    ?>
    <div class="wrap morden-backup-admin">
        <h1><?php _e('Backup', 'morden-backup'); ?></h1>
        <button id="morden-backup-create" class="button button-primary"><?php _e('Create Backup', 'morden-backup'); ?></button>
        <hr>
        <h2><?php _e('Existing Backups', 'morden-backup'); ?></h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Backup File', 'morden-backup'); ?></th>
                    <th><?php _e('Size', 'morden-backup'); ?></th>
                    <th><?php _e('Date', 'morden-backup'); ?></th>
                    <th><?php _e('Actions', 'morden-backup'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)) : ?>
                    <tr>
                        <td colspan="4"><?php _e('No backups found.', 'morden-backup'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($backups as $backup) :
                        $backup_path = $backup_dir . '/' . $backup;
                        ?>
                        <tr>
                            <td><?php echo esc_html($backup); ?></td>
                            <td><?php echo size_format(filesize($backup_path)); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', filemtime($backup_path)); ?></td>
                            <td>
                                <button class="button morden-restore-btn" data-backup-path="<?php echo esc_attr($backup_path); ?>"><?php _e('Restore', 'morden-backup'); ?></button>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=morden_backup_download_backup&backup=' . urlencode($backup)), 'morden_backup_download')); ?>" class="button"><?php _e('Download', 'morden-backup'); ?></a>
                                <button class="button button-danger morden-delete-btn" data-backup-path="<?php echo esc_attr($backup_path); ?>"><?php _e('Delete', 'morden-backup'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function morden_backup_restore_page() {
    echo '<div class="wrap"><h1>Restore</h1><p>Please go to the Backup page to restore a backup.</p></div>';
}

function morden_backup_migration_page() {
    echo '<div class="wrap"><h1>Migration</h1><button id="morden-backup-export" class="button button-primary">Export Site</button><hr><input type="text" id="morden-backup-token" placeholder="Migration Token"><button id="morden-backup-import" class="button button-primary">Import Site</button></div>';
}

function morden_backup_settings_page() {
    if (isset($_POST['morden_backup_save_settings'])) {
        check_admin_referer('morden_backup_settings_nonce');
        $old_settings = get_option('morden_backup_settings');
        $settings = [
            'max_execution_time' => intval($_POST['max_execution_time']),
            'memory_limit' => sanitize_text_field($_POST['memory_limit']),
            'split_size' => intval($_POST['split_size']),
            'backup_retention' => intval($_POST['backup_retention']),
            'log_level' => sanitize_text_field($_POST['log_level']),
            'exclude_paths' => sanitize_textarea_field($_POST['exclude_paths']),
            'sftp_host' => sanitize_text_field($_POST['sftp_host']),
            'sftp_user' => sanitize_text_field($_POST['sftp_user']),
            'sftp_pass' => sanitize_text_field($_POST['sftp_pass']),
            'sftp_path' => sanitize_text_field($_POST['sftp_path']),
            'schedule' => sanitize_text_field($_POST['schedule']),
            'b2_key_id' => sanitize_text_field($_POST['b2_key_id']),
            'b2_application_key' => sanitize_text_field($_POST['b2_application_key']),
            'b2_bucket_id' => sanitize_text_field($_POST['b2_bucket_id']),
            'b2_bucket_name' => sanitize_text_field($_POST['b2_bucket_name']),
        ];
        update_option('morden_backup_settings', $settings);

        if ($old_settings['schedule'] !== $settings['schedule']) {
            wp_clear_scheduled_hook('morden_backup_scheduled_backup');
            if ($settings['schedule'] !== 'none') {
                wp_schedule_event(time(), $settings['schedule'], 'morden_backup_scheduled_backup');
            }
        }

        echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
    }

    $settings = get_option('morden_backup_settings');
    $settings = wp_parse_args($settings, [
        'sftp_host' => '',
        'sftp_user' => '',
        'sftp_pass' => '',
        'sftp_path' => '',
        'schedule' => 'none',
        'b2_key_id' => '',
        'b2_application_key' => '',
        'b2_bucket_id' => '',
        'b2_bucket_name' => '',
    ]);
    ?>
    <div class="wrap morden-backup-admin">
        <h1><?php _e('Settings', 'morden-backup'); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field('morden_backup_settings_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Max Execution Time', 'morden-backup'); ?></th>
                    <td><input type="number" name="max_execution_time" value="<?php echo esc_attr($settings['max_execution_time']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Memory Limit', 'morden-backup'); ?></th>
                    <td><input type="text" name="memory_limit" value="<?php echo esc_attr($settings['memory_limit']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Split Size (MB)', 'morden-backup'); ?></th>
                    <td><input type="number" name="split_size" value="<?php echo esc_attr($settings['split_size']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Backup Retention (days)', 'morden-backup'); ?></th>
                    <td><input type="number" name="backup_retention" value="<?php echo esc_attr($settings['backup_retention']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Log Level', 'morden-backup'); ?></th>
                    <td>
                        <select name="log_level">
                            <option value="info" <?php selected($settings['log_level'], 'info'); ?>>Info</option>
                            <option value="debug" <?php selected($settings['log_level'], 'debug'); ?>>Debug</option>
                            <option value="error" <?php selected($settings['log_level'], 'error'); ?>>Error</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Exclude Paths', 'morden-backup'); ?></th>
                    <td><textarea name="exclude_paths" rows="5" cols="50" class="large-text"><?php echo esc_textarea($settings['exclude_paths']); ?></textarea></td>
                </tr>
            </table>

            <h2><?php _e('Remote Storage: SFTP', 'morden-backup'); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Host', 'morden-backup'); ?></th>
                    <td><input type="text" name="sftp_host" value="<?php echo esc_attr($settings['sftp_host']); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Username', 'morden-backup'); ?></th>
                    <td><input type="text" name="sftp_user" value="<?php echo esc_attr($settings['sftp_user']); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Password', 'morden-backup'); ?></th>
                    <td><input type="password" name="sftp_pass" value="<?php echo esc_attr($settings['sftp_pass']); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Remote Path', 'morden-backup'); ?></th>
                    <td><input type="text" name="sftp_path" value="<?php echo esc_attr($settings['sftp_path']); ?>" class="regular-text" /></td>
                </tr>
            </table>

            <h2><?php _e('Remote Storage: Backblaze B2', 'morden-backup'); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Key ID', 'morden-backup'); ?></th>
                    <td><input type="text" name="b2_key_id" value="<?php echo esc_attr($settings['b2_key_id']); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Application Key', 'morden-backup'); ?></th>
                    <td><input type="password" name="b2_application_key" value="<?php echo esc_attr($settings['b2_application_key']); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Bucket ID', 'morden-backup'); ?></th>
                    <td><input type="text" name="b2_bucket_id" value="<?php echo esc_attr($settings['b2_bucket_id']); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Bucket Name', 'morden-backup'); ?></th>
                    <td><input type="text" name="b2_bucket_name" value="<?php echo esc_attr($settings['b2_bucket_name']); ?>" class="regular-text" /></td>
                </tr>
            </table>

            <h2><?php _e('Scheduled Backups', 'morden-backup'); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Schedule', 'morden-backup'); ?></th>
                    <td>
                        <select name="schedule">
                            <option value="none" <?php selected($settings['schedule'], 'none'); ?>><?php _e('None', 'morden-backup'); ?></option>
                            <option value="daily" <?php selected($settings['schedule'], 'daily'); ?>><?php _e('Daily', 'morden-backup'); ?></option>
                            <option value="weekly" <?php selected($settings['schedule'], 'weekly'); ?>><?php _e('Weekly', 'morden-backup'); ?></option>
                            <option value="monthly" <?php selected($settings['schedule'], 'monthly'); ?>><?php _e('Monthly', 'morden-backup'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Settings', 'primary', 'morden_backup_save_settings'); ?>
        </form>
    </div>
    <?php
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

// AJAX Handlers
function morden_backup_create_ajax_handler() {
    check_ajax_referer('morden_backup_nonce');
    $settings = get_option('morden_backup_settings');
    $destination = morden_backup_get_destination_from_settings($settings);

    $backup_engine = new BackupEngine();
    $result = $backup_engine->executeBackup([], $destination);
    wp_send_json($result);
}

function morden_backup_restore_ajax_handler() {
    check_ajax_referer('morden_backup_nonce');
    $restore_engine = new RestoreEngine();
    $result = $restore_engine->executeRestore($_POST['backup_path']);
    wp_send_json($result);
}

function morden_backup_export_ajax_handler() {
    check_ajax_referer('morden_backup_nonce');
    $migration_engine = new MigrationEngine();
    $result = $migration_engine->exportSite();
    wp_send_json($result);
}

function morden_backup_import_ajax_handler() {
    check_ajax_referer('morden_backup_nonce');
    $migration_engine = new MigrationEngine();
    $result = $migration_engine->importSite($_POST['token']);
    wp_send_json($result);
}

function morden_backup_download_migration_ajax_handler() {
    $token = $_GET['token'];
    $token_manager = new \MordenBackup\Migration\TokenManager();
    $backup_path = $token_manager->get_backup_path($token);

    if (!$backup_path || !file_exists($backup_path)) {
        wp_die('Invalid or expired token, or file not found.');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($backup_path) . '"');
    header('Content-Length: ' . filesize($backup_path));
    readfile($backup_path);
    exit;
}

function morden_backup_download_backup_ajax_handler() {
    check_ajax_referer('morden_backup_download');
    $backup_file = urldecode($_GET['backup']);
    $backup_dir = wp_upload_dir()['basedir'] . '/morden-backup';
    $backup_path = $backup_dir . '/' . $backup_file;

    if (!file_exists($backup_path)) {
        wp_die('File not found.');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($backup_path) . '"');
    header('Content-Length: ' . filesize($backup_path));
    readfile($backup_path);
    exit;
}

function morden_backup_delete_backup_ajax_handler() {
    check_ajax_referer('morden_backup_nonce');
    $backup_path = $_POST['backup_path'];

    if (file_exists($backup_path)) {
        unlink($backup_path);
        wp_send_json_success(['message' => 'Backup deleted successfully.']);
    } else {
        wp_send_json_error(['message' => 'File not found.']);
    }
}

// Scheduled backup handler
function morden_backup_do_scheduled_backup() {
    $settings = get_option('morden_backup_settings');
    $destination = morden_backup_get_destination_from_settings($settings);

    $backup_engine = new BackupEngine();
    $backup_engine->executeBackup([], $destination);
}

function morden_backup_get_destination_from_settings(array $settings) {
    if (!empty($settings['sftp_host'])) {
        return new MordenBackup\Adapters\SFTPAdapter([
            'hostname' => $settings['sftp_host'],
            'username' => $settings['sftp_user'],
            'password' => $settings['sftp_pass'],
            'path'     => $settings['sftp_path'],
        ]);
    }

    if (!empty($settings['b2_key_id'])) {
        return new MordenBackup\Adapters\BackblazeAdapter([
            'key_id' => $settings['b2_key_id'],
            'application_key' => $settings['b2_application_key'],
            'bucket_id' => $settings['b2_bucket_id'],
            'bucket_name' => $settings['b2_bucket_name'],
        ]);
    }

    return null;
}
