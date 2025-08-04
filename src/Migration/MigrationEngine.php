<?php
namespace MordenBackup\Migration;

use MordenBackup\Core\BackupEngine;
use MordenBackup\Core\RestoreEngine;
use MordenBackup\Utils\Logger;
use MordenBackup\Utils\TokenManager;

/**
 * MigrationEngine - Handle site migration
 *
 * @package MordenBackup\Migration
 * @version 1.0.0
 */
class MigrationEngine
{
    private $logger;
    private $backup_engine;
    private $restore_engine;
    private $token_manager;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->backup_engine = new BackupEngine();
        $this->restore_engine = new RestoreEngine();
        $this->token_manager = new TokenManager();
    }

    /**
     * Export site for migration
     */
    public function exportSite(array $options = []): array
    {
        $this->logger->info('Export process started', $options);

        try {
            $backup_data = $this->backup_engine->executeBackup();
            if ($backup_data['status'] === 'error') {
                throw new \Exception($backup_data['message']);
            }

            $token_data = $this->token_manager->create_token($backup_data['backup_path']);

            return [
                'status' => 'success',
                'token' => $token_data['token'],
                'expires' => $token_data['expires'],
                'download_url' => $this->get_download_url($token_data['token']),
                'file_size' => $backup_data['total_size']
            ];
        } catch (\Exception $e) {
            $this->logger->error('Export failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Import site from migration token
     */
    public function importSite(string $token, array $options = []): array
    {
        $this->logger->info('Import process started', ['token' => substr($token, 0, 8) . '...']);

        try {
            $backup_path = $this->token_manager->get_backup_path($token);
            if (!$backup_path) {
                throw new \Exception('Invalid or expired token.');
            }

            $restore_data = $this->restore_engine->executeRestore($backup_path);
            if ($restore_data['status'] === 'error') {
                throw new \Exception($restore_data['message']);
            }

            return [
                'status' => 'success',
                'message' => 'Import completed successfully',
                'timestamp' => current_time('mysql')
            ];
        } catch (\Exception $e) {
            $this->logger->error('Import failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function get_download_url(string $token)
    {
        return admin_url('admin-ajax.php?action=morden_backup_download_migration&token=' . $token);
    }
}
