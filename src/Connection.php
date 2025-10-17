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
use PDOException;

class Connection
{
    protected PDO $pdo;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    protected function connect(): void
    {
        $configObj = new Config($this->config);
        $this->pdo = $configObj->getPDO();
    }

    public function getPDO(): PDO
    {
        try {
            $this->pdo->query('SELECT 1'); // Ping the database
        } catch (PDOException $e) {
            // Reconnect if ping fails
            $this->connect();
        }
        return $this->pdo;
    }
}
