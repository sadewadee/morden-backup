<?php
namespace MordenBackup\Contracts;

/**
 * LoggerInterface - Contract for logging implementations
 *
 * @package MordenBackup\Contracts
 * @version 1.0.0
 */
interface LoggerInterface
{
    public function error(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function debug(string $message, array $context = []): void;
}
