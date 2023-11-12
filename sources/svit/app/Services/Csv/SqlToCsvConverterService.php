<?php

namespace App\Services\Csv;

use App\Traits\DirectoriesTrait;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;
use League\Csv\Writer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Query;

class SqlToCsvConverterService
{
    use DirectoriesTrait;

    private const ALLOWED_TABLES = [
        'wp1of20_posts',
        'wp_posts'
    ];

    private const TITLE_COLUMN_NAME = 'post_title';

    private const CONTENT_COLUMN_NAME = 'post_content';

    private const INSERT_QUERY_TYPE = 'INSERT';

    public function deleteUrlsFromString(string $string): string
    {
        return preg_replace('/\b(?:https?:\/\/|www\.)\S+\b/', '', $string);
    }

    public function deleteImagesFromString(string $string): string
    {
        return preg_replace('/<img[^>]+\>/i', '', $string);
    }

    private function parseString(?string $string): string
    {
        if (empty($string)) {
            return '';
        }

        $string = str_replace(
            ["\n", "\t", "\r", "\f", "\$", "\0", "\v"],
            ['\\n', '\\t', '\\r', '\\e', '\\f', '\\$', '\\0', '\\v'],
            $string
        );

        $string = $this->deleteUrlsFromString($string);
        $string = $this->deleteImagesFromString($string);

        return $string;
    }

    // перевірка, чи всі строки записались
    public function checkRowsCount(int $expectedCount, string $writerFilePath)
    {
        $csvReader = Reader::createFromPath($writerFilePath);

        if ($expectedCount !== $csvReader->count()) {
            throw new \Exception('Not all lines are recorded');
        }
    }

    private function saveCsv(UploadedFile $dump, array $csvContent): string
    {
        $currentDate = Carbon::now();
        $trimedName = trim($dump->getClientOriginalName(), '.sql');
        $csvFileName = $trimedName . '-' . $currentDate->format('d-m-Y-H-i-s') . '.csv';
        $csvPath = $this->getFilePath($currentDate->format('d-m-Y'), $csvFileName);
        $directory = $this->getDirectoryPath($currentDate->format('d-m-Y'));

        // перевірка чи існує необхідна директорія. Якщо ні, то метод спробує ії створити.
        // Метод тільки викидає ексепшени
        $this->checkIsExistDirectoryOrFile($directory, true);

        $csvWriter = Writer::createFromPath($csvPath, 'w+')->setDelimiter(';')->forceEnclosure();
        $csvWriter->insertAll($csvContent);

        $this->checkRowsCount(count($csvContent), $csvPath);

        return route(
            'api.csv.download',
            [
                'folderName' => $currentDate->format('d-m-Y'),
                'fileName' => $csvFileName
            ]
        );
    }

    public function convert(array $dumps): array
    {
        $links = [];

        foreach ($dumps as $dump) {
            $parser = new Parser($dump->getContent(), true);
            $statements = $parser->statements;

            foreach ($statements as $statement) {
                $type = Query::getFlags($statement);

                $isInsertQuery = $type['querytype'] === self::INSERT_QUERY_TYPE;
                $hasTable = isset($statement->into->dest->table);
                $tableIsAllowed = $hasTable && in_array($statement->into->dest->table, self::ALLOWED_TABLES);

                if ($isInsertQuery && $tableIsAllowed) {
                    $titleIndex = array_search(self::TITLE_COLUMN_NAME, $statement->into->columns);
                    $contentIndex = array_search(self::CONTENT_COLUMN_NAME, $statement->into->columns);
                    $csvContent = [];

                    foreach ($statement->values as $row) {
                        $csvContent[] = [
                            $this->parseString($row->values[$titleIndex]),
                            $this->parseString($row->values[$contentIndex]),
                        ];
                    }

                    if (!empty($csvContent)) {
                        $links[] = $this->saveCsv($dump, $csvContent);
                    }
                }
            }
        }

        return $links;
    }
}
