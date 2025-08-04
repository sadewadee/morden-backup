<?php
namespace MordenBackup\Migration;

class TokenManager
{
    private $option_name = 'morden_backup_migration_tokens';

    public function create_token(string $backup_path)
    {
        $tokens = get_option($this->option_name, []);
        $token = wp_generate_password(64, false, false);
        $expires = time() + (24 * 3600);

        $tokens[$token] = [
            'backup_path' => $backup_path,
            'expires' => $expires,
        ];

        update_option($this->option_name, $tokens);

        return [
            'token' => $token,
            'expires' => $expires,
        ];
    }

    public function get_backup_path(string $token)
    {
        $tokens = get_option($this->option_name, []);

        if (!isset($tokens[$token])) {
            return false;
        }

        if (time() > $tokens[$token]['expires']) {
            unset($tokens[$token]);
            update_option($this->option_name, $tokens);
            return false;
        }

        return $tokens[$token]['backup_path'];
    }
}
