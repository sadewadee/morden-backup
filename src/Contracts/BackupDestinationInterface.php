<?php
namespace MordenBackup\Contracts;

/**
 * BackupDestinationInterface - Contract for backup storage adapters
 *
 * @package MordenBackup\Contracts
 * @version 1.0.0
 */
interface BackupDestinationInterface
{
    public function testConnection(): bool;
    public function upload(string $localPath, string $remotePath): bool;
    public function download(string $remotePath, string $localPath): bool;
    public function listBackups(): array;
    public function delete(string $remotePath): bool;
}
