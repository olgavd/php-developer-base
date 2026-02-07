<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

class App
{
    protected Route $router;

    public function __construct()
    {
        $this->router = new Route();
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->router->start();
    }
}
