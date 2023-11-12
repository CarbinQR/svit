<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Csv\CreateFromSqlRequest;
use App\Http\Requests\Api\Csv\MergeRequest;
use App\Services\Csv\MergeCsvService;
use App\Services\Csv\SqlToCsvConverterService;
use App\Traits\DirectoriesTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CsvController extends Controller
{
    use DirectoriesTrait;

    public function createFromSql(CreateFromSqlRequest $request, SqlToCsvConverterService $service): JsonResponse
    {
        return response()
            ->json(
                $service->convert(
                    $request->file('sqlFiles')
                )
            );
    }

    public function download(string $folderName, string $fileName): BinaryFileResponse
    {
        $path = $this->getFilePath($folderName, $fileName);

        // перевірка чи існує необхідний файл. Метод тільки викидає ексепшени
        $this->checkIsExistDirectoryOrFile($path);

        return response()->download($path, '');
    }

    public function merge(MergeRequest $request, MergeCsvService $service): JsonResponse
    {
        return response()
            ->json($service->merge(
                $request->input('links')
            )
            );
    }

}
