<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\ServiceAdmin;
use App\Services\ServiceFile;
use App\Services\ServiceUser;

class Controller
{
    protected function getServiceUser(): ServiceUser
    {
        return new ServiceUser();
    }

    protected function getServiceFile(): ServiceFile
    {
        return new ServiceFile();
    }

    protected function getServiceAdmin(): ServiceAdmin
    {
        return new ServiceAdmin();
    }

    protected function getResponse(): Response
    {
        return new Response();
    }

    protected function getRequest(): Request
    {
        return new Request();
    }
}
