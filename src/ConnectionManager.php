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
use PDO;

class ConnectionManager
{
    private static array $connections = [];

    public static function add(array $config, string $name = 'default'): void
    {
        // Make PDO Instance
        if (!self::has($name)) {
            $pdo = new Config($config);
            self::$connections[$name] = $pdo->getPDO();
        }
        return;
    }

    public static function get(string $name = 'default'): PDO
    {
        if (!self::has($name)) {
            throw new InvalidArgumentException("Connection '{$name}' does not exist.");
        }

        return self::$connections[$name];
    }

    public static function has(string $name): bool
    {
        return isset(self::$connections[$name]);
    }
}