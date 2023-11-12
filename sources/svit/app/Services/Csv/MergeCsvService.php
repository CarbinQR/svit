<?php

namespace App\Services\Csv;

use App\Traits\DirectoriesTrait;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Writer;

class MergeCsvService
{
    use DirectoriesTrait;

    private const DEFAULT_MERGED_FILE_NAME = 'merged-dump';

    public function getPathFromLink(string $link): string
    {
        $implodedLink = explode('/', $link);
        $folderNameIndex = count($implodedLink) - 2;
        $fileNameIndex = count($implodedLink) - 1;

        return $this->getFilePath($implodedLink[$folderNameIndex], $implodedLink[$fileNameIndex]);
    }

    public function getFileNameFromLink(string $link): string
    {
        $implodedLink = explode('/', $link);
        $fileNameIndex = count($implodedLink) - 1;

        return $implodedLink[$fileNameIndex];
    }

    // перевірка, чи всі строки записались
    public function checkRowsCount(int $expectedCount, string $writerFilePath, string $readerFileName): void
    {
        $csvReader = Reader::createFromPath($writerFilePath);

        if ($expectedCount !== $csvReader->count()) {
            throw new \Exception('Error when merged' . $readerFileName);
        }
    }

    public function merge(array $links): string
    {
        $currentDate = Carbon::now();
        $writerFileName = self::DEFAULT_MERGED_FILE_NAME . '-' . $currentDate->format('d-m-Y-H-i-s') . '.csv';
        $writerFilePath = $this->getFilePath($currentDate->format('d-m-Y'), $writerFileName);
        $directory = $this->getDirectoryPath($currentDate->format('d-m-Y'));
        $this->checkIsExistDirectoryOrFile($directory, true);
        $csvWriter = Writer::createFromPath($writerFilePath, 'w+')->setDelimiter(';')->forceEnclosure();
        $expectedWriteCount = 0;

        foreach ($links as $link) {
            $csvReader = Reader::createFromPath($this->getPathFromLink($link), 'r')->setDelimiter(';');
            $csvReader->includeEmptyRecords();

            $csvWriter->insertAll($csvReader->getRecords());

            $expectedWriteCount += $csvReader->count();

            $readerFileName = $this->getFileNameFromLink($link);
            $this->checkRowsCount($expectedWriteCount, $writerFilePath, $readerFileName);
        }

        return route(
            'api.csv.download',
            [
                'folderName' => $currentDate->format('d-m-Y'),
                'fileName' => $writerFileName
            ]
        );
    }
}
