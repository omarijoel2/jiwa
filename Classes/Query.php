<?php
/**
 * Classes/Query.php
 * 
 * Author: Timon
 * Date: 08/02/2025
 * 
 */
namespace App\Classes;

use PDO;
use PDOException;
use App\Config\Database;

class Query
{
    private static $conn;

    // Set the connection using the Database class (static)
    public static function setConnection()
    {
        if (self::$conn === null) {
            $db = new Database();
            self::$conn = $db->connect(); // Get the PDO connection from Database class
        }
    }

    // Execute any query
    public static function query($sql, $params = [])
    {
        self::setConnection(); // Ensure connection is set before executing

        try {
            $stmt = self::$conn->prepare($sql);
            
            // Bind parameters to the query securely
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, self::getPDODataType($value));
            }

            $stmt->execute();

            // Return results depending on query type
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all results for SELECT queries
            } elseif (stripos($sql, 'INSERT') === 0 || stripos($sql, 'UPDATE') === 0 || stripos($sql, 'DELETE') === 0) {
                return $stmt->rowCount();  // Return the number of affected rows for non-SELECT queries
            }

            return null;
        } catch (PDOException $e) {
            // Log error and throw exception
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }

    // Fetch a single result (useful for SELECT queries that return one row)
    public static function fetchOne($sql, $params = [])
    {
        self::setConnection(); // Ensure connection is set before executing

        try {
            $stmt = self::$conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, self::getPDODataType($value));
            }

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch a single row
        } catch (PDOException $e) {
            throw new \Exception("Query execution failed: " . $e->getMessage());
        }
    }

    // Insert data and return the inserted ID
    public static function insert($sql, $params = [])
    {
        self::setConnection(); // Ensure connection is set before executing

        try {
            $stmt = self::$conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, self::getPDODataType($value));
            }

            $stmt->execute();
            return self::$conn->lastInsertId();  // Return the last inserted ID
        } catch (PDOException $e) {
            throw new \Exception("Insert failed: " . $e->getMessage());
        }
    }

    // Update or Delete and return the number of affected rows
    public static function updateDelete($sql, $params = [])
    {
        self::setConnection(); // Ensure connection is set before executing

        try {
            $stmt = self::$conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, self::getPDODataType($value));
            }

            $stmt->execute();
            return $stmt->rowCount();  // Return the number of affected rows
        } catch (PDOException $e) {
            throw new \Exception("Update/Delete failed: " . $e->getMessage());
        }
    }

    // Get the correct PDO data type based on the value type
    private static function getPDODataType($value)
    {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }
}
