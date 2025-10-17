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

use PDO;
use Exception;
use InvalidArgumentException;

class DB
{
    private static ?self $instance = null;
    protected PDO $pdo;
    protected string $table     =   '';
    protected string $columns   =   '*';
    protected array $joins      =   [];
    protected array $wheres     =   [];
    protected array $bindings   =   [];
    protected array $groupBy    =   [];
    protected array $orderBy    =   [];
    protected ?int $limit       =   null;
    protected ?int $offset      =   null;
    protected array $having     =   [];
    protected string $name      =   '';

    private function __construct(string $name = 'default')
    {
        $this->name = $name;
        $this->pdo = ConnectionManager::get($this->name);
    }

    // Get Instance of DB
    /**
     * @param PDO $pdo Required PDO instance
     * @return object Returns the DB instance
     */
    public static function getInstance(string $name = 'default'): self
    {
        if (self::$instance === null) {
            self::$instance = new self($name);
        }
        return self::$instance;
    }

    // Set the table name
    /**
     * @param string $table Required table name
     * @return object Returns the DB instance
     */
    public function table(string $table): object
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    // Reset the query builder
    /**
     * @return void
     */
    protected function reset(): void
    {
        $this->columns  =   '*';
        $this->joins    =   [];
        $this->wheres   =   [];
        $this->bindings =   [];
        $this->groupBy  =   [];
        $this->orderBy  =   [];
        $this->limit    =   null;
        $this->offset   =   null;
        $this->having   =   [];
    }

    // Set the columns to select
    /**
     * @param string $columns Required columns to select
     * @return object Returns the DB instance
     */
    public function select(string $columns = '*'): object
    {
        $this->columns = $columns;
        return $this;
    }

    // Set Join
    /**
     * @param string $table Required table name to join
     * @param string $first Required first column
     * @param string $operator Required operator
     * @param string $second Required second column
     * @param string $type Optional join type (LEFT, RIGHT, INNER)
     * @return object Returns the DB instance
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'LEFT'): object
    {
        $type = strtoupper($type);
        if (!in_array($type, ['LEFT', 'RIGHT', 'INNER'])) {
            throw new InvalidArgumentException("Invalid join type: {$type}");
        }
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    // Set Where
    /**
     * @param array|string $column Required column name or array of column-value pairs
     * @param string $operator Optional operator (default: '=')
     * @param mixed $value Optional value (default: null)
     * @param string $compare Optional comparison type (AND, OR)
     * @return object Returns the DB instance
     */
    public function where(array|string $column, string $operator = '=', mixed $value = null, string $compare = 'AND'): object
    {
        if (is_array($column)) {
            foreach ($column as $col => $val) {
                $this->addWhere("{$col} {$operator} ?", [$val], strtoupper($compare));
            }
        } else {
            $this->addWhere("{$column} {$operator} ?", [$value], strtoupper($compare));
        }
        return $this;
    }

    // Set Where Like
    /**
     * @param array $where Required column name
     * @param string $compare Optional comparison type (AND, OR)
     * @return object Returns the DB instance
     */
    public function whereLike(array $where, string $compare = 'AND'): object
    {
        foreach($where as $col => $val) {
            $this->addWhere("{$col} LIKE ?", [$val], strtoupper($compare));
        }
        return $this;
    }

    // Set Where In
    /**
     * @param string $column Required column name
     * @param array $values Required array of values to match
     * @param string $compare Optional comparison type (AND, OR)
     * @return object Returns the DB instance
     */
    public function whereIn(string $column, array $values, string $compare = 'AND'): object
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->addWhere("{$column} IN ({$placeholders})", $values, strtoupper($compare));
        return $this;
    }

    // Where Null
    /**
     * @param string $column Required column name
     * @param string $compare Optional comparison type (AND, OR)
     * @return self Returns the DB instance
     */
    public function whereNull(string $column, string $compare = 'AND'): self
    {
        $this->addWhere("{$column} IS NULL", [], strtoupper($compare));
        return $this;
    }

    // Where Between
    /**
     * @param string $column Required column name
     * @param mixed $value1 Required first value
     * @param mixed $value2 Required second value
     * @param string $compare Optional comparison type (AND, OR)
     * @return self Returns the DB instance
     */
    public function whereBetween(string $column, mixed $value1, mixed $value2, string $compare = 'AND'): self
    {
        $this->addWhere("{$column} BETWEEN ? AND ?", [$value1, $value2], strtoupper($compare));
        return $this;
    }

    // Where Group
    /**
     * @param callable $callback Required callback function to build the group
     * @param string $compare Optional comparison type (AND, OR)
     * @return self Returns the DB instance
     */
    public function whereGroup(callable $callback, string $compare = 'AND'): self
    {
        $newQuery = new self($this->name);

        $callback($newQuery);

        if (empty($newQuery->wheres)) {
            return $this;
        }

        $groupedConditions = implode(' ', $newQuery->wheres);
        $prefix = empty($this->wheres) ? '' : (strtoupper($compare) === 'OR' ? 'OR ' : 'AND ');
        $this->wheres[] = "{$prefix}({$groupedConditions})";
        $this->bindings = array_merge($this->bindings, $newQuery->bindings);

        return $this;
    }

    // Set Group By
    /**
     * @param string ...$columns Required columns to group by
     * @return self Returns the DB instance
     */
    public function groupBy(string ...$columns): self
    {
        $this->groupBy = $columns;
        return $this;
    }

    // Set Having
    /**
     * @param string $column Required column name
     * @param string $operator Required operator
     * @param mixed $value Required value
     * @return self Returns the DB instance
     */
    public function having(string $column, string $operator, mixed $value): self
    {
        $this->having[]     =   "{$column} {$operator} ?";
        $this->bindings[]   =   $value;
        return $this;
    }

    // Set Order By
    /**
     * @param string $column Required column name
     * @param string $direction Optional direction (ASC, DESC)
     * @return self Returns the DB instance
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $column = strtoupper($column);
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    // Set Limit
    /**
     * @param int|string $limit Required limit
     * @return self Returns the DB instance
     */
    public function limit(int|string $limit): self
    {
        $this->limit = (int) $limit;
        return $this;
    }

    // Set Offset
    /**
     * @param int|string $page Optional Argument. Default is Page Number 1
     * @return self Returns the DB instance
     */
    public function offset(int|string $page = 1): self
    {
        $offset = ((int)$page - 1) * (int) $this->limit;
        $this->offset = ($offset < 0) ? 0 : $offset;
        return $this;
    }

    // Get the results
    /**
     * @return array<int,array> Returns the results as an array
     */
    public function get(): array
    {
        $sql    =   $this->buildSelectSQL();
        $stmt   =   $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        $result =   $stmt->fetchAll();
        $this->reset();
        return $result;
    }

    // Get the first result
    /**
     * @return array<string,mixed> Returns the first result as an array
     */
    public function first(): array
    {
        $this->limit(1);
        $result =   $this->get();
        $first  =   $result[0] ?? [];
        return $first;
    }

    // Count Columns
    /**
     * @param string $column Optional Argument. Default is '*'
     * @return int
     */
    public function count(string $column = '*'): int
    {
        $this->columns = "COUNT({$column}) as count";
        $stmt = $this->pdo->prepare($this->buildSelectSQL());
        $stmt->execute($this->bindings);
        $result = $stmt->fetch();
        // Reset Query Builder
        $this->reset();
        return (int) ($result['count'] ?? 0);
    }
    
    // Insert a single row
    /**
     * @param array<string,string|int|null> $data Required data to insert
     * @return string|false Returns the last inserted ID
     */
    public function insert(array $data): string|false
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(array_values($data));
        $lastId = $this->pdo->lastInsertId();
        $this->reset();
        return $lastId;
    }

    // Insert multiple rows
    /**
     * @param array $rows Required rows to insert
     * @return bool Returns true on success, false on failure
     */
    public function insertMany(array $rows): bool
    {
        if (empty($rows)) {
            throw new InvalidArgumentException("Cannot insert empty rows.");
        }

        // Prepare columns from the first row (assuming all rows have the same structure)
        $columns = array_keys($rows[0]);
        $placeholders = array_fill(0, count($columns), '?');
        $placeholders = implode(', ', $placeholders);

        // Prepare the full query
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ";
        $valueSets = [];
        foreach ($rows as $row) {
            $valueSets[] = "({$placeholders})";
        }
        $sql .= implode(', ', $valueSets);

        // Flatten the rows for bindings
        $bindings = [];
        foreach ($rows as $row) {
            $bindings = array_merge($bindings, array_values($row));
        }

        $stmt = $this->pdo->prepare($sql);
        $inserted = $stmt->execute($bindings);
        $this->reset();
        return $inserted;
    }

    // Chunk the results
    /**
     * @param int $size Required chunk size
     * @param callable $callback Required callback function to process each chunk
     * @return void
     */
    public function chunk(int $size, callable $callback): void
    {
        $offset = 0;

        while (true) {
            // Select in chunks
            $sql = $this->buildSelectSQL() . " LIMIT {$size} OFFSET {$offset}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->bindings);
            $results = $stmt->fetchAll();

            // Break if no results
            if (empty($results)) {
                break;
            }

            // Pass the results to the callback
            $callback($results);

            // Move offset forward
            $offset += $size;
        }
        $this->reset();
    }

    // Update the rows
    /**
     * @param array $data Required data to update
     * @return bool Returns true on success, false on failure
     */
    public function update(array $data): int
    {
        if (empty($this->wheres)) {
            throw new InvalidArgumentException("No WHERE clause provided for UPDATE operation.");
        }
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        $sql .= " WHERE " . implode(' AND ', $this->wheres);

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(array_merge(array_values($data), $this->bindings));
        $rowcount = $stmt->rowCount();
        $this->reset();
        return $rowcount;
    }

    // Delete the rows
    /**
     * @return int Returns the number of affected rows
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        if (empty($this->wheres)) {
            throw new InvalidArgumentException("No WHERE clause provided for DELETE operation.");
        }

        $sql .= " WHERE " . implode(' AND ', $this->wheres);

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($this->bindings);
        $rowcount = $stmt->rowCount();
        $this->reset();
        return $rowcount;
    }

    // Add Where condition
    /**
     * @param string $condition Required condition string
     * @param array $bindings Optional bindings for the condition
     * @param string $compare Optional comparison type (AND, OR)
     * @return void
     */
    private function addWhere(string $condition, array $bindings = [], string $compare = 'AND'): void
    {
        $compare = strtoupper($compare);
        $prefix = empty($this->wheres) ? '' : ($compare === 'OR' ? 'OR ' : 'AND ');
        $this->wheres[] = "{$prefix}{$condition}";
        $this->bindings = array_merge($this->bindings, $bindings);
    }

    // Build the SQL query
    /**
     * @return string Returns the built SQL query
     */
    private function buildSelectSQL(): string
    {
        $sql = "SELECT {$this->columns} FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' ', $this->wheres);
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $sql .= " HAVING " . implode(' AND ', $this->having);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    // Debug SQL
    /**
     * @return string Returns the SQL query with bindings
     */
    public function debugSql(): string
    {
        $sql = $this->buildSelectSQL();
        $bindings = $this->bindings;

        $sql = preg_replace_callback('/\?/', function() use (&$bindings) {
            $value = array_shift($bindings);
            if (is_numeric($value)) {
                return $value;
            }
            return "'" . addslashes($value) . "'";
        }, $sql);

        return $sql;
    }

    // Prevent cloning
    /**
     * @throws Exception Throws an exception if cloning is attempted
     */
    private function __clone(){
        throw new Exception('Cloning is not allowed.');
    }

    // Prevent serialization
    /**
     * @throws Exception Throws an exception if serialization is attempted
     */
    public function __wakeup(){
        throw new Exception('Unserializing is not allowed.');
    }
}
