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
    private $filesystem;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->init_filesystem();
    }

    private function init_filesystem()
    {
        if (!class_exists('WP_Filesystem_SSH2')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-ssh2.php';
        }

        $this->filesystem = new \WP_Filesystem_SSH2($this->config);
    }

    public function testConnection(): bool
    {
        return $this->filesystem->connect();
    }

    public function upload(string $localPath, string $remotePath): bool
    {
        if (!$this->testConnection()) {
            return false;
        }
        return $this->filesystem->put_contents($remotePath, file_get_contents($localPath));
    }

    public function download(string $remotePath, string $localPath): bool
    {
        if (!$this->testConnection()) {
            return false;
        }
        $contents = $this->filesystem->get_contents($remotePath);
        if ($contents === false) {
            return false;
        }
        return file_put_contents($localPath, $contents) !== false;
    }

    public function listBackups(): array
    {
        if (!$this->testConnection()) {
            return [];
        }
        $files = $this->filesystem->dirlist($this->config['path']);
        return $files ? array_keys($files) : [];
    }

    public function delete(string $remotePath): bool
    {
        if (!$this->testConnection()) {
            return false;
        }
        return $this->filesystem->delete($remotePath);
    }
}
