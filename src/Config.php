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

class Config
{
    protected array $config;
    protected PDO $pdo;

    /**
     * @param array{driver:string, username:string, password:string} $driver
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pdo = $this->createPDO();
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    protected function createPDO(): PDO
    {
        $driver = strtolower($this->config['driver'] ?? '');

        if (empty($driver)) {
            throw new InvalidArgumentException("Invalid Driver Detected: '{$driver}'.");
        }

        $driverClass = __NAMESPACE__ . '\\Drivers\\' . ucfirst($driver);

        if (!class_exists($driverClass)) {
            throw new InvalidArgumentException("Unsupported Driver: '{$driver}'");
        }

        $driverInstance = new $driverClass();
        $dsn = $driverInstance->dsn($this->config);

        $username = $this->config['username'] ?? null;
        $password = $this->config['password'] ?? null;
        $options = $this->config['options'] ?? [];

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $options += $defaultOptions;

        return new PDO($dsn, $username, $password, $defaultOptions);
    }
}