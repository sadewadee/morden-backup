<?php
namespace MordenBackup\Core;

use MordenBackup\Utils\Logger;
use MordenBackup\Utils\MB_File_System;
use ZipArchive;

/**
 * RestoreEngine - Main restore processing engine
 *
 * @package MordenBackup\Core
 * @version 1.0.0
 */
class RestoreEngine
{
    private $logger;
    private $file_system;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->logger = new Logger();
        $this->file_system = new MB_File_System();
    }

    /**
     * Execute restore process
     */
    public function executeRestore(string $backupPath, array $options = []): array
    {
        $this->logger->info('Restore process started', ['backup_path' => $backupPath]);

        try {
            $this->restoreBackup($backupPath);

            return [
                'status' => 'success',
                'message' => 'Restore completed successfully',
                'timestamp' => current_time('mysql')
            ];
        } catch (\Exception $e) {
            $this->logger->error('Restore failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => current_time('mysql')
            ];
        }
    }

    /**
     * Execute partial restore
     */
    public function executePartialRestore(string $backupPath, array $components): array
    {
        // TODO: Implement partial restore
        return ['status' => 'pending', 'type' => 'partial'];
    }

    private function restoreBackup(string $backupPath)
    {
        if (!file_exists($backupPath)) {
            throw new \Exception('Backup file not found.');
        }

        $backup_dir = dirname($backupPath);
        $extract_path = $backup_dir . '/restore_temp';
        $this->file_system->ensure_dir_exists($extract_path);

        $zip = new ZipArchive();
        if ($zip->open($backupPath) !== TRUE) {
            throw new \Exception('Cannot open backup file.');
        }

        $zip->extractTo($extract_path);
        $zip->close();

        // 1. Restore database
        $db_dump_path = $extract_path . '/database.sql';
        if (file_exists($db_dump_path)) {
            $this->restore_database($db_dump_path);
        }

        // 2. Clean old files and move new files
        $this->replace_site_files($extract_path, ABSPATH, $backupPath);

        // 3. Clean up temporary directory
        $this->file_system->delete_directory($extract_path);
    }

    private function restore_database(string $dump_path)
    {
        // Temporarily disable foreign key checks
        $this->wpdb->query('SET foreign_key_checks = 0');

        $sql = file_get_contents($dump_path);
        $queries = explode(";\n", $sql);

        foreach ($queries as $query) {
            $query = trim($query);
            if ($query) {
                $this->wpdb->query($query);
            }
        }

        // Re-enable foreign key checks
        $this->wpdb->query('SET foreign_key_checks = 1');

        // Flush rewrite rules to apply new settings
        flush_rewrite_rules();
    }

    private function replace_site_files(string $source, string $destination, string $backup_to_exclude)
    {
        // Clean destination, but preserve essential files
        $this->clean_destination($destination, $backup_to_exclude);

        // Move new files from source to destination
        $this->move_files($source, $destination);
    }

    private function clean_destination(string $dir, string $backup_to_exclude)
    {
        $items = new \DirectoryIterator($dir);
        foreach ($items as $item) {
            if ($item->isDot()) continue;

            $path = $item->getPathname();

            // Do not delete wp-config.php or the backup file itself
            if (basename($path) === 'wp-config.php' || realpath($path) === realpath($backup_to_exclude)) {
                continue;
            }

            if ($item->isDir()) {
                $this->file_system->delete_directory($path);
            } else {
                unlink($path);
            }
        }
    }

    private function move_files(string $source, string $destination)
    {
        $items = new \DirectoryIterator($source);
        foreach ($items as $item) {
            if ($item->isDot()) continue;

            $source_path = $item->getPathname();
            $dest_path = $destination . '/' . $item->getBasename();

            if (basename($source_path) === 'database.sql') {
                continue;
            }

            rename($source_path, $dest_path);
        }
    }
}
