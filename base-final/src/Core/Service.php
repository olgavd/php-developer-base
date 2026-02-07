<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\ModelAccess;
use App\Models\ModelDirectories;
use App\Models\ModelFile;
use App\Models\ModelUser;

class Service
{
    protected function getModelUser(): ModelUser
    {
        return new ModelUser();
    }

    protected function getModelDirectories(): ModelDirectories
    {
        return new ModelDirectories();
    }

    protected function getModelFile(): ModelFile
    {
        return new ModelFile();
    }

    protected function getModelAccess(): ModelAccess
    {
        return new ModelAccess();
    }

    protected function getModel(): Model
    {
        return new Model();
    }

    protected function getLogger(): Logger
    {
        return new Logger();
    }

    protected function getResponse(): Response
    {
        return new Response();
    }
}
