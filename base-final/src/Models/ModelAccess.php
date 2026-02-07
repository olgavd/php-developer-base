<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class ModelAccess extends Model
{
    private string $table = 'access';

    public function select(PDO $createConnection, array $arr = array()): array|false
    {
        return $this->getDb($this->table)->select(
            $createConnection,
            $arr
        );
    }

    public function delete(PDO $createConnection, array $arr = array()): void
    {
        $this->getDb($this->table)->delete(
            $createConnection,
            $arr
        );
    }

    public function insert(PDO $createConnection, array $arr = array()): void
    {
        $this->getDb($this->table)->insert(
            $createConnection,
            $arr
        );
    }
}
