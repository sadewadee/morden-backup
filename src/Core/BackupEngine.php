<?php
namespace MordenBackup\Core;

use MordenBackup\Contracts\BackupDestinationInterface;
use MordenBackup\Utils\Logger;
use MordenBackup\Utils\MB_File_System;
use ZipArchive;

/**
 * BackupEngine - Main backup processing engine
 *
 * @package MordenBackup\Core
 * @version 1.0.0
 */
class BackupEngine
{
    private $logger;
    private $settings;
    private $file_system;
    private $wpdb;
    private $backup_dir;
    private $backup_file;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->logger = new Logger();
        $this->settings = get_option('morden_backup_settings', []);
        $this->file_system = new MB_File_System();
        $this->backup_dir = $this->file_system->get_upload_dir() . '/morden-backup';
    }

    /**
     * Execute backup process
     */
    public function executeBackup(array $options = [], BackupDestinationInterface $destination = null): array
    {
        $this->logger->info('Backup process started', $options);

        try {
            $backup_path = $this->createBackup();
            $file_count = $this->file_system->count_files($backup_path);
            $total_size = $this->file_system->get_file_size($backup_path);

            if ($destination) {
                $remote_path = 'morden-backup/' . basename($backup_path);
                $destination->upload($backup_path, $remote_path);
            }

            return [
                'status' => 'success',
                'message' => 'Backup completed successfully',
                'timestamp' => current_time('mysql'),
                'file_count' => $file_count,
                'total_size' => $total_size,
                'backup_path' => $backup_path,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Backup failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => current_time('mysql')
            ];
        }
    }

    /**
     * Create incremental backup
     */
    public function createIncrementalBackup(string $lastBackupId): array
    {
        // TODO: Implement incremental backup
        return ['status' => 'pending', 'type' => 'incremental'];
    }

    private function createBackup()
    {
        $this->file_system->ensure_dir_exists($this->backup_dir);
        $this->backup_file = 'backup-' . date('Y-m-d-H-i-s') . '.zip';
        $backup_path = $this->backup_dir . '/' . $this->backup_file;

        $zip = new ZipArchive();
        if ($zip->open($backup_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception('Cannot create backup file.');
        }

        // 1. Dump database
        $db_dump_path = $this->dump_database();
        if ($db_dump_path) {
            $zip->addFile($db_dump_path, 'database.sql');
        }

        // 2. Add files
        $source = ABSPATH;
        $this->add_files_to_zip($zip, $source);

        $zip->close();

        // 3. Clean up temporary files
        if ($db_dump_path) {
            unlink($db_dump_path);
        }

        return $backup_path;
    }

    private function add_files_to_zip(ZipArchive $zip, string $source)
    {
        $excluded_paths = $this->get_excluded_paths();
        $source_path = realpath($source);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source_path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if ($file->isDir()) {
                continue;
            }

            $file_path = $file->getRealPath();
            $relative_path = substr($file_path, strlen($source_path) + 1);

            if ($this->is_excluded($file_path, $excluded_paths)) {
                continue;
            }

            $zip->addFile($file_path, $relative_path);
        }
    }

    private function dump_database()
    {
        $dump_path = $this->backup_dir . '/database.sql';
        $handle = fopen($dump_path, 'w');

        if (!$handle) {
            throw new \Exception('Cannot create database dump file.');
        }

        $tables = $this->wpdb->get_results('SHOW TABLES', ARRAY_N);
        $tables = array_map(function($a) { return $a[0]; }, $tables);

        foreach ($tables as $table) {
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            $create_table = $this->wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            fwrite($handle, $create_table[1] . ";\n\n");

            $rows = $this->wpdb->get_results("SELECT * FROM `$table`", ARRAY_A);
            if ($rows) {
                fwrite($handle, "INSERT INTO `$table` VALUES\n");
                $buffer = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = is_null($value) ? 'NULL' : "'" . $this->wpdb->_real_escape($value) . "'";
                    }
                    $buffer[] = '(' . implode(',', $values) . ')';
                }
                fwrite($handle, implode(",\n", $buffer) . ";\n\n");
            }
        }

        fclose($handle);
        return $dump_path;
    }

    private function get_excluded_paths()
    {
        $defaults = [
            'wp-content/cache',
            'wp-content/backups',
            'wp-content/uploads/morden-backup',
            $this->backup_file,
        ];

        $from_settings = $this->settings['exclude_paths'] ?? '';
        $custom = array_filter(array_map('trim', explode("\n", $from_settings)));

        return array_merge($defaults, $custom);
    }

    private function is_excluded(string $path, array $excluded_paths): bool
    {
        $path = str_replace('\\', '/', $path); // Normalize path separators
        foreach ($excluded_paths as $excluded) {
            $excluded = str_replace('\\', '/', ABSPATH . $excluded);
            if (strpos($path, $excluded) === 0) {
                return true;
            }
        }
        return false;
    }
}
