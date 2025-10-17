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

namespace Laika\Model\Drivers;

use RuntimeException;

class Mysql
{
    /**
     * @param array<string,int|string>
     * @return string
     */
    public function dsn(array $config): string
    {
        $host = $config['host'] ?? 'localhost';
        // Check 'database' key exists
        if (!isset($config['database']) && !$config['database']) {
            throw new RuntimeException("'database' Key Missing or Invalid!");
        }
        $database = $config['database'];
        $port = $config['port'] ?? 3306;
        $charset = $config['charset'] ?? 'utf8mb4';
        return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
    }
}
