<?php

namespace App\Repository;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ServerRepository
{
    private string $file;
    private string $cacheFile;
    private int $cacheTtl;

    public function __construct(string $file = __DIR__ . '/../Database/servers.xlsx', ?string $cacheDir = null, int $cacheTtl = 300)
    {
        $this->file = $file;
        $cacheDir = $cacheDir ?? sys_get_temp_dir();
        $this->cacheFile = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'servers_' . md5(realpath($file) ?: $file) . '.json';
        $this->cacheTtl = $cacheTtl;
    }

    public function getAll(): array
    {
        $currentHash = is_file($this->file)
            ? hash_file('sha256', $this->file)
            : null;

        if (file_exists($this->cacheFile)) {
            $content = @file_get_contents($this->cacheFile);
            $cached = $content !== false ? json_decode($content, true) : null;

            if (
                is_array($cached)
                && isset($cached['hash'], $cached['data'])
                && $cached['hash'] === $currentHash
                && (time() - filemtime($this->cacheFile) <= $this->cacheTtl)
            ) {
                return $cached['data'];
            }
        }

        $servers = [];

        try {
            $spreadsheet = IOFactory::load($this->file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $header = array_shift($rows);

            $validIndices = array_filter(
                $header,
                static fn($columnName) => !empty($columnName) && $columnName !== 'Filters'
            );

            foreach ($rows as $row) {
                $serverData = [];
                foreach ($validIndices as $index => $columnName) {
                    $serverData[$columnName] = $row[$index] ?? null;
                }
                $servers[] = $serverData;
            }
        } catch (\Throwable $e) {
            $this->logError(sprintf(
                '[%s] Repository load failed: %s in %s:%d',
                date('c'),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            return $servers;
        }

        try {
            $payload = [
                'hash' => $currentHash,
                'data' => $servers,
            ];

            $tmp = $this->cacheFile . '.' . uniqid('', true) . '.tmp';
            $written = file_put_contents($tmp, json_encode($payload), LOCK_EX);

            if ($written !== false) {
                rename($tmp, $this->cacheFile);
            } else {
                @unlink($tmp);
            }
        } catch (\Throwable $e) {
            $this->logError(sprintf(
                '[%s] Cache write failed: %s in %s:%d',
                date('c'),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }

        return $servers;
    }


    private function logError(string $message): void
    {
        $dir = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }

        $file = $dir . '/server_repository.log';
        $entry = $message . PHP_EOL;
        @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    }
}
