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

use InvalidArgumentException;

class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected array $primaryKeys = [];
    protected array $uniqueKeys = [];
    protected array $indexes = [];
    protected array $foreignKeys = [];
    protected ?string $engine = null;
    protected ?string $charset = null;
    protected ?string $collation = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    // ID Column
    /**
     * @param string $name Column name. Default is 'id'
     * @param string $type Column type (INT or BIGINT). Default is 'INT'
     * @param bool $unsigned Whether the column is unsigned. Default is true
     * @return object
     */
    public function id(string $name = 'id', string $type = 'INT', bool $unsigned = true):object
    {
        $type = strtoupper($type);
        if (!in_array($type, ['INT', 'BIGINT'])) {
            throw new InvalidArgumentException("Invalid type for {$name} column: {$type}");
        }
        $unsignedFlag = $unsigned ? ' UNSIGNED' : '';
        $this->columns[] = "{$name} {$type}{$unsignedFlag} AUTO_INCREMENT";
        $this->primaryKeys[] = $name; // Adds to primary keys
        return $this;
    }

    // VARCHAR column
    /**
     * @param string $name Column name. Required Argument
     * @param int $length Length of the VARCHAR column. Default id 255
     * @param bool $null Whether the column is nullable. Default is false
     * @param string $default Default value for the column
     * @return object
     */
    public function string(string $name, int $length = 255, bool $null = false, string $default = ''):object
    {
        $null = $null ? ' NULL' : ' NOT NULL';
        $default = $default ? " DEFAULT '{$default}'" : '';
        $this->columns[] = "{$name} VARCHAR({$length}){$null}{$default}";
        return $this;
    }

    // TEXT column
    /**
     * @param string $name Column name. Required Argument
     * @param bool $null Whether the column is nullable. Default is false
     * @return object
     */
    public function text(string $name, bool $null = false):object
    {
        $null = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} TEXT{$null}";
        return $this;
    }

    /**
     * Add a MEDIUMTEXT column
     *
     * @param string $name Column name. Required.
     * @param bool $null Whether the column is nullable. Default is false.
     * @return self
     */
    public function mediumText(string $name, bool $null = false): self
    {
        $null = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} MEDIUMTEXT{$null}";
        return $this;
    }

    /**
     * Add a LONGTEXT column
     *
     * @param string $name Column name. Required.
     * @param bool $null Whether the column is nullable. Default is false.
     * @return self
     */
    public function longText(string $name, bool $null = false): self
    {
        $null = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} LONGTEXT{$null}";
        return $this;
    }

    // INTEGER column
    /**
     * @param string $name Column name. Required Argument
     * @param string $type Column type (INT or BIGINT). Default is 'INT'
     * @param bool $unsigned Whether the column is unsigned. Default is true
     * @param bool $nullable Whether the column is nullable. Default is false
     * @return object
     */
    public function integer(string $name, string $type = 'INT', bool $unsigned = true, bool $null = false):object
    {
        $type = strtoupper($type);
        if (!in_array($type, ['INT', 'BIGINT'])) {
            throw new InvalidArgumentException("Invalid type for {$name} column: {$type}");
        }
        $unsignedFlag = $unsigned ? ' UNSIGNED' : '';
        $nullableFlag = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} {$type}{$unsignedFlag}{$nullableFlag}";
        return $this;
    }

    // BOOLEAN column
    /**
     * @param string $name Column name. Required Argument
     * @param bool $nullable Whether the column is nullable. Default is false
     * @return object
     */
    public function boolean(string $name, bool $null = false):object
    {
        $nullable = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} BOOLEAN{$nullable}";
        return $this;
    }

    // DECIMAL column
    /**
     * @param string $name Column name. Required Argument
     * @param int $precision Precision of the DECIMAL column. Default is 10
     * @param int $scale Scale of the DECIMAL column. Default is 2
     * @param bool $nullable Whether the column is nullable. Default is false
     * @return object
     */
    public function decimal(string $name, int $precision = 10, int $scale = 2, bool $null = false):object
    {
        $nullable = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} DECIMAL({$precision}, {$scale}){$nullable}";
        return $this;
    }

    // FLOAT column
    /**
     * @param string $name Column name. Required Argument
     * @param bool $nullable Whether the column is nullable. Default is false
     * @return object
     */
    public function float(string $name, bool $null = false):object
    {
        $nullable = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} FLOAT{$nullable}";
        return $this;
    }

    /**
     * Add an ENUM column
     *
     * @param string $name Column name. Required.
     * @param array $allowedValues List of allowed string values. Required.
     * @param string|null $default Default value. Must be in allowed values or null.
     * @param bool $null Whether the column is nullable. Default is false.
     * @return self
     */
    public function enum(string $name, array $values, ?string $default = null, bool $null = false):self
    {
        if(empty($values)){
            throw new InvalidArgumentException("ENUM column must have at least one allowed value.");
        }

        $quotedValues = array_map(fn($v) => "'".addslashes($v)."'", $values);
        $enumList = implode(', ', $quotedValues);
        $null = $null ? ' NULL' : ' NOT NULL';

        $defaultClause = '';
        if($default !== null){
            if(!in_array($default, $values, true)){
                throw new InvalidArgumentException("Default value '{$default}' is not in the allowed ENUM values.");
            }
            $defaultClause = " DEFAULT '" . addslashes($default) . "'";
        }

        $this->columns[] = "{$name} ENUM({$enumList}){$null}{$defaultClause}";
        return $this;
    }

    // DATETIME column
    /**
     * @param string $name Column name. Required Argument
     * @param bool $nullable Whether the column is nullable. Default is false
     * @return object
     */
    public function datetime(string $name, bool $null = false):object
    {
        $nullable = $null ? ' NULL' : ' NOT NULL';
        $this->columns[] = "{$name} DATETIME{$nullable}";
        return $this;
    }

    // created_at and updated_at TIMESTAMP columns
    /**
     * @return object
     */
    public function timestamps():object
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    // foreign key
    /**
     * @param string $column Column name. Required Argument
     * @param string $referencedTable Referenced table name. Required Argument
     * @param string $referencedColumn Referenced column name. Default is 'id'
     * @return object
     */
    public function foreign(string $column, string $referencedTable, string $referencedColumn = 'id'):object
    {
        $this->foreignKeys[] = "FOREIGN KEY ({$column}) REFERENCES {$referencedTable}({$referencedColumn})";
        return $this;
    }

    // Primary Key
    /**
     * @param string $column Array of column names. Required Argument
     * @return object
     */
    public function primary(string $column):object
    {
        $this->primaryKeys = [$column];
        return $this;
    }

    // Unique Key
    /**
     * @param string $column Column name. Required Argument
     * @param int $length Length of the unique key. Default is null
     * @return object
     */
    public function unique(string $column, int $length = null):object
    {
        $this->uniqueKeys[] = [$column . ($length ? "({$length})" : '')];
        return $this;
    }

    // Index Key
    /**
     * @param string $column Column name. Required Argument
     * @param ?int $length Length of the index key. Default is null
     * @return object
     */
    public function index(string $column, ?int $length = null):object
    {
        $this->indexes[] = [$column . ($length ? "({$length})" : '')];
        return $this;
    }

    // Storage engine
    /**
     * @param string $engine Storage engine. Required Argument (e.g., InnoDB, MyISAM, etc.)
     * @return object
     */
    public function engine(string $engine):object
    {
        $this->engine = strtoupper($engine);
        return $this;
    }

    // Character set
    /**
     * @param string $charset Character set. Required Argument (e.g., utf8mb4, latin1, etc.)
     * @return object
     */
    public function charset(string $charset):object
    {
        $this->charset = $charset;
        return $this;
    }

    // Collation
    /**
     * @param string $collation Collation. Required Argument (e.g., utf8mb4_unicode_ci, utf8mb4_general_ci, etc.)
     * @return object
     */
    public function collation(string $collation): object
    {
        $this->collation = $collation;
        return $this;
    }

    // Make SQL
    public function makeSql(): string
    {
        $sql = "(";
        // Columns
        $sql .= implode(", ", $this->columns);

        // Primary Key
        $sql .= !empty($this->primaryKeys) ? ", PRIMARY KEY (" . implode(", ", $this->primaryKeys) . ")" : '';
        // Unique Keys
        // $uniqueKeysSQL = '';
        foreach ($this->uniqueKeys as $uniqueColumns) {
            $sql .= ", UNIQUE (" . implode(", ", $uniqueColumns) . ")";
        }

        // Indexes
        // $indexesSQL = '';
        foreach ($this->indexes as $indexColumns) {
            $sql .= ", INDEX (" . implode(", ", $indexColumns) . ")";
        }

        // Foreign Keys
        $sql .= !empty($this->foreignKeys) ? ', ' . implode(", ", $this->foreignKeys) : '';

        $sql .= ")";

        // Engine
        $sql .= $this->engine ? " ENGINE={$this->engine}" : "";

        // Charset
        $sql .= $this->charset ? " DEFAULT CHARSET={$this->charset}" : " DEFAULT CHARSET=utf8mb4";

        // Collation
        $sql .= $this->collation ? " COLLATE {$this->collation}" : " COLLATE utf8mb4_unicode_ci";

        // Create Table SQL
        return "CREATE TABLE IF NOT EXISTS {$this->table} {$sql}";
    }
}