<?php
namespace MordenBackup\Adapters;

use MordenBackup\Contracts\BackupDestinationInterface;

/**
 * SFTPAdapter - SFTP storage adapter
 *
 * @package MordenBackup\Adapters
 * @version 1.0.0
 */
class SFTPAdapter implements BackupDestinationInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function testConnection(): bool
    {
        // TODO: Implement SFTP connection test
        return true;
    }

    public function upload(string $localPath, string $remotePath): bool
    {
        // TODO: Implement SFTP upload
        return true;
    }

    public function download(string $remotePath, string $localPath): bool
    {
        // TODO: Implement SFTP download
        return true;
    }

    public function listBackups(): array
    {
        // TODO: Implement backup listing
        return [];
    }

    public function delete(string $remotePath): bool
    {
        // TODO: Implement file deletion
        return true;
    }
}
