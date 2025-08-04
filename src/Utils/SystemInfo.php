<?php
namespace MordenBackup\Utils;

/**
 * SystemInfo - System information utility
 *
 * @package MordenBackup\Utils
 * @version 1.0.0
 */
class SystemInfo
{
    /**
     * Get system information
     */
    public static function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'disk_free_space' => disk_free_space(ABSPATH),
            'wp_upload_dir' => wp_upload_dir()
        ];
    }
}
