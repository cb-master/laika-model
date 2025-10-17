<?php

/**
 * Laika Database Model
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika PHP MVC Framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Laika\Model;

use Exception;
use PDO;

class Schema
{
    // Pdo Connection
    // This should be set via ConnectionManager
    protected static PDO $pdo;

    // Set the PDO connection via ConnectionManager
    /**
     * @param string $name Optional Argument
     * @return void
     */
    public static function setConnection(string $name = 'default'): void
    {
        static::$pdo = ConnectionManager::get($name);
    }

    // Create a table using the schema blueprint
    /**
     * @param string $table Required Argument
     * @param callable $callback Required Argument
     * @return void
     */
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->makeSql();

        // Execute the SQL to create the table
        self::execute($sql);
    }

    // Drop an existing table
    /**
     * @param string $table Required Argument
     * @return void
     */
    public static function drop(string $table): void
    {
        // Make sure table name is valid, no user inputs should be allowed
        $table  =   self::sanitizeTableName($table);
        $sql    =   "DROP TABLE IF EXISTS {$table}";

        self::execute($sql);
    }

    // Execute the provided SQL query
    /**
     * @param string $sql Required Argument
     * @return void
     */
    protected static function execute(string $sql): void
    {
        try {
            if (empty(self::$pdo)) {
                throw new Exception("Database connection is not set.");
            }

            self::$pdo->exec($sql);
        } catch (Exception $e) {
            throw new Exception("Migration failed: {$e->getMessage()}", 0, $e);
        }
    }

    // Sanitize table names to prevent SQL injection
    /**
     * @param string $table Required Argument
     * @return string
     */
    protected static function sanitizeTableName(string $table): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }
}
