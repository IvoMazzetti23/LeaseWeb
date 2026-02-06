<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelParser
{
    private FileLogger $logger;

    public function __construct(?FileLogger $logger = null)
    {
        $this->logger = $logger ?? new FileLogger('excel_parser.log');
    }

    public function parse(string $filePath): array
    {
        $servers = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $header = array_shift($rows);

            $validIndices = array_filter(
                $header,
                static fn($columnName) => !empty($columnName) && $columnName !== 'Filters'
            );

            foreach ($rows as $index => $row) {
                $serverData = [];
                foreach ($validIndices as $colIndex => $columnName) {
                    $serverData[$columnName] = $row[$colIndex] ?? null;
                }

                if (!isset($serverData['id'])) {
                    $serverData['id'] = md5(implode('', $serverData) . $index);
                }

                $servers[] = $serverData;
            }
        } catch (\Throwable $e) {
            $this->logger->error("Excel parse error: " . $e->getMessage());
            throw new \RuntimeException("Excel parse error: " . $e->getMessage(), 0, $e);
        }

        return $servers;
    }
}
