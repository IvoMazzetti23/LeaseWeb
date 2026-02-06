<?php

namespace App\Service;

class FileLogger
{
    private string $logDir;
    private string $logFile;

    public function __construct(string $filename = 'app.log')
    {
        $this->logDir = dirname(__DIR__, 2) . '/logs';
        $this->logFile = $this->logDir . '/' . $filename;
    }

    public function error(string $message): void
    {
        $this->write("ERROR: $message");
    }

    public function info(string $message): void
    {
        $this->write("INFO: $message");
    }

    private function write(string $message): void
    {
        if (!is_dir($this->logDir)) {
            if (!mkdir($this->logDir, 0777, true) && !is_dir($this->logDir)) {
                return;
            }
        }

        $entry = '[' . date('c') . '] ' . $message . PHP_EOL;
        @file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
