<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Db
{
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(PDO $createConnection, array $arr = array()): array|false
    {
        $params = [];
        $conditions = [];
        $select = [];

        foreach ($arr as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
            $select = $createConnection->prepare("SELECT * 
FROM 
    {$this->table} 
WHERE " . implode(' AND ', $conditions));
            $select->execute($params);
        }
        return $select->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(PDO $createConnection, array $arr = array()): void
    {
        $par0 = array_keys($arr)[0];
        $par1 = array_keys($arr)[1];

        $update = $createConnection->prepare("UPDATE {$this->table}
SET
    $par1 = :$par1
WHERE $par0 = :$par0"
        );
        $update->execute(array(
            $par0 => $arr[$par0],
            $par1 => $arr[$par1]
        ));
    }

    public function insert(PDO $createConnection, array $arr = array()): void
    {
        $columns = implode(", ", array_keys($arr));
        $placeholders = implode(", ", array_fill(0, count($arr), '?'));
        $values = array_values($arr);

        $insert = $createConnection->prepare(
            "INSERT INTO 
    {$this->table} 
    ($columns) 
VALUES 
    ($placeholders)"
        );
        $insert->execute($values);
    }

    public function delete(PDO $createConnection, array $arr = array()): void
    {
        $params = [];
        $conditions = [];

        foreach ($arr as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        $delete = $createConnection->prepare("DELETE 
FROM 
    {$this->table} 
WHERE " . implode(' AND ', $conditions));
        $delete->execute($params);
    }
}
