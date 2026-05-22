<?php
/**
 * Config/Database.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 * Description:
 * This file contains the database connection code.
 */
namespace App\Config;

use PDO;
use PDOException;
use App\Classes\Logger;

class Database {
    private $database, $host, $username, $password, $database_name, $conn;

    public function connect() {
        $logger = new Logger();
        $config = require_once __DIR__ . '/db.php';

        $database = $config['database'];
        $this->host = $config[$database]['host'];
        $this->username = $config[$database]['username'];
        $this->password = $config[$database]['password'];
        $this->database_name = $config[$database]['database_name'];

        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->database_name", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed!");
        }
       
    }
}
