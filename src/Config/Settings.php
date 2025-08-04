<?php
namespace MordenBackup\Config;

/**
 * Settings - Plugin configuration management
 *
 * @package MordenBackup\Config
 * @version 1.0.0
 */
class Settings
{
    public static function getSettings(): array
    {
        return get_option('morden_backup_settings', [
            'max_execution_time' => 300,
            'memory_limit' => '512M',
            'split_size' => 1024,
            'backup_retention' => 7,
            'log_level' => 'info',
            'compression_level' => 6,
            'exclude_patterns' => [
                '.git', '.svn', '.DS_Store', 'Thumbs.db',
                'node_modules', 'vendor', 'cache', 'logs'
            ]
        ]);
    }

    public static function updateSettings(array $settings): bool
    {
        return update_option('morden_backup_settings', $settings);
    }
}
