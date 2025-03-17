<?php
class DBClient
{
    private static $instance = null;
    private $connection;

    public function __construct($schema = null)
    {
        $this->connection = new MYSQL();
        if ($schema) {
            $this->connection->schema = $schema;
        }
        if (!$this->connection->isInitialized) {
            $this->connection->initialize();
        }
    }


    public static function get($schema = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($schema);
        }
        return self::$instance->connection;
    }

}
?>