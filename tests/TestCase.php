<?php
namespace MordenBackup\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case for Morden Backup tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Common test setup
    }

    protected function tearDown(): void
    {
        // Common test cleanup
        parent::tearDown();
    }

    /**
     * Create a test file with specified content
     */
    protected function createTestFile(string $content = 'test content', string $filename = null): string
    {
        $filename = $filename ?: tempnam(sys_get_temp_dir(), 'morden_test_');
        file_put_contents($filename, $content);
        return $filename;
    }

    /**
     * Clean up test files
     */
    protected function cleanupTestFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
