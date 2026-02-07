<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Model
{
    public ?PDO $connection = null;

    protected function getDb(string $table): Db
    {
        return new Db($table);
    }

    private function getLogger(): Logger
    {
        return new Logger();
    }

    protected function createConnection(): PDO
    {
        try {
            $this->connection = new PDO(
                "mysql:host=db;dbname=cloud_storage;charset=utf8",
                "root",
                "root"
            );
        } catch (\PDOException $PDOException) {
            $this->getLogger()->error($PDOException->getMessage());
        }
        return $this->connection;
    }

    public function transaction(callable $callback): void
    {
        $createConnection = $this->createConnection();
        $createConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $createConnection->beginTransaction();
        try {
            $callback($createConnection);
            $createConnection->commit();
            $this->getLogger()->info("Транзакция успешно завершена");
        } catch (\PDOException $PDOException) {
            $this->getLogger()->error("Ошибка транзакции: " . $PDOException->getMessage());
            $createConnection->rollBack();
        }
    }
}
