<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public function post(string $name, string|null $default = null)
    {
        return $_POST[$name] ?? $default;
    }

    public function server(string $name, string|null $default = null)
    {
        return $_SERVER[$name] ?? $default;
    }

    public function file(string $name, string|null $default = null)
    {
        return $_FILES[$name] ?? $default;
    }

    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    public function isPut(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'PUT';
    }

    public function isDelete(): bool
    {
        return $_SERVER['REQUEST_METHOD'] == 'DELETE';
    }
}
