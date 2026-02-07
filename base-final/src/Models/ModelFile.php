<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class ModelFile extends Model
{
    private string $table = 'files';

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

    public function update(PDO $createConnection, array $arr = array()): void
    {
        $this->getDb($this->table)->update(
            $createConnection,
            $arr
        );
    }

    public function add(PDO $createConnection, array $arr = array()): void
    {
        $this->getDb($this->table)->insert(
            $createConnection,
            $arr
        );
    }
}
