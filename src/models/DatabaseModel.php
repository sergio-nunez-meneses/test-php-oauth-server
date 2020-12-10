<?php
require './tools/sql.php';

abstract class DatabaseModel
{

  private $pdo;

  protected function connection()
  {
    $this->pdo = new PDO(
      'mysql:host=' . DB_HOST. ';port=' . DB_PORT. ';charset=' . DB_CHAR . ';dbname=' . DB_NAME,
      DB_USER,
      DB_PASS,
      PDO_OPTIONS
    );

    if (empty($this->pdo))
    {
      throw new \Exception('Connection failed.');
      return false;
    }

    // echo 'Connected to ' . DB_NAME . '.<br>'; // for debugging
    return true;
  }

  protected function run_query($sql, $placeholders = [])
  {
    if ($this->connection() === true)
    {
      if (empty($placeholders))
      {
        return $this->pdo->query($sql)->fetchAll();
      }
      else
      {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($placeholders);
        return $stmt;
      }
    }
  }
}
