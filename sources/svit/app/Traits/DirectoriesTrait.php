<?php

namespace App\Traits;

trait DirectoriesTrait
{
    public function getFilePath(string $folderName, string $fileName): string
    {
        return storage_path(
            'app'
            . DIRECTORY_SEPARATOR
            . 'public'
            . DIRECTORY_SEPARATOR
            . $folderName
            . '/'
            . $fileName
        );
    }

    public function getDirectoryPath(string $folderName): string
    {
        return storage_path(
            'app'
            . DIRECTORY_SEPARATOR
            . 'public'
            . DIRECTORY_SEPARATOR
            . $folderName
        );
    }

    public function checkIsExistDirectoryOrFile(string $path, bool $isNeedCreate = false): void {
        if (!file_exists($path)) {
            if ($isNeedCreate && !mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            } else {
                return;
            }

            throw new \RuntimeException(sprintf('Directory not found'));
        }
    }
}
