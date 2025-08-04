<?php
namespace MordenBackup\Exceptions;

/**
 * RestoreException - Custom exception for restore operations
 *
 * @package MordenBackup\Exceptions
 * @version 1.0.0
 */
class RestoreException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
