<?php 

    class MYSQL {

        public $connection;
        public $schema;
        public $isInitialized = false;
        
        public function initialize() {
            $details = getDatabaseDetails();
    
            $servername = $details['server'];
            $port = $details['port'];
            $username = $details['user'];
            $password = $details['password'];

            if(!$this->schema){
                $this->schema = $details['schema'];
            }
            
            try {
                $dsn = "mysql:host=$servername:$port" ;
                $this->connection = new PDO($dsn, $username, $password);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->isInitialized = true;
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function getDatabases() {
            try {
                $stmt = $this->connection->query("SHOW DATABASES");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function getTables() {
            if(!$this->schema){
                return array();
            }
            try {
                $this->connection->exec("USE ". $this->schema .";");
                $stmt = $this->connection->query("SHOW TABLES");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function getAutocompleteKeywords() {
            try {
                $keywords = [];
                $stmt = $this->connection->query("
                    SELECT TABLE_NAME, COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE()
                ");
                
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                foreach ($result as $row) {
                    $keywords[] = $row['TABLE_NAME'];
                    $keywords[] = $row['COLUMN_NAME'];
                }
        
                return array_unique($keywords);
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function executeQuery($query) {
            try {
                $this->connection->exec("USE ". $this->schema .";");
                if (preg_match('/^\s*SELECT/i', $query) && !preg_match('/LIMIT\s+\d+/i', $query)) {
                    $query = rtrim($query, ";") . " LIMIT 100;";
                }

                $stmt = $this->connection->prepare($query);
                $stmt->execute();
    
                if (preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $query)) {
                    return ["affected_rows" => $stmt->rowCount()];
                } else {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

            } catch (PDOException $e) {
                return ["error" => $e->getMessage()];
            } catch (Exception $e) {
                return ["error" => $e->getMessage()];
            }
        }

    }


?>