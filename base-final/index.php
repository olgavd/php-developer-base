<?php

use App\Core\App;
use App\Core\Logger;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

ini_set('display_errors', 1);

date_default_timezone_set('Europe/Minsk');

try {
    $app = new App();
    $app->run();
} catch (Exception $exception) {
    $logger = new Logger();
    $logger->error($exception->getMessage());
}
