<?php

namespace App\CleanupPolicies;

use Symfony\Component\Finder\SplFileInfo;
use Spatie\DirectoryCleanup\Policies\CleanupPolicy;

class LaravelExcelPolicy implements CleanupPolicy
{
    public function shouldDelete(SplFileInfo $file) : bool
    {
        $filesToKeep = ['.gitignore'];

        return ! in_array($file->getFilename(), $filesToKeep);
    }
}
