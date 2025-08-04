<?php
namespace MordenBackup\Utils;

/**
 * Logger - Logging utility
 *
 * @package MordenBackup\Utils
 * @version 1.0.0
 */
class Logger
{
    private $logFile;

    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        $this->logFile = $upload_dir['basedir'] . '/morden-backup/backup.log';

        // Create log directory if not exists
        $log_dir = dirname($this->logFile);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    private function log(string $level, string $message, array $context): void
    {
        $timestamp = current_time('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] $level: $message$contextStr" . PHP_EOL;

        error_log($logEntry, 3, $this->logFile);
    }
}
