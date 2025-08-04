<?php
namespace MordenBackup\Utils;

class MB_File_System
{
    public function get_upload_dir()
    {
        return wp_upload_dir()['basedir'];
    }

    public function ensure_dir_exists(string $dir)
    {
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
    }

    public function count_files(string $path)
    {
        if (!file_exists($path)) {
            return 0;
        }

        if (is_file($path)) {
            return 1;
        }

        $count = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $count++;
            }
        }

        return $count;
    }

    public function get_file_size(string $path)
    {
        if (!file_exists($path)) {
            return 0;
        }

        return filesize($path);
    }

    public function delete_directory(string $dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($dir);
    }
}
