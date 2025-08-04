<?php
namespace MordenBackup\Exceptions;

/**
 * BackupException - Custom exception for backup operations
 *
 * @package MordenBackup\Exceptions
 * @version 1.0.0
 */
class BackupException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
