<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    private function log(string $message, string $level): void
    {
        $logFile = __DIR__ . "/../../logs/$level.log";
        $time = date("Y-m-d H:i:s");
        $formatMessage = "[$time][$level] $message" . PHP_EOL;
        file_put_contents($logFile, $formatMessage, FILE_APPEND | LOCK_EX);
    }

    public function error(string $message, string $level = 'ERROR'): void
    {
        $this->log($message, $level);
    }

    public function info(string $message, string $level = 'INFO'): void
    {
        $this->log($message, $level);
    }
}
