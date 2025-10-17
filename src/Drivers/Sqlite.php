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

class Sqlite
{
    /**
     * @param array<string,int|string>
     * @return string
     */
    public function dsn(array $config): string
    {
        $path = $config['path'] ?? ':memory:';
        return "sqlite:{$path}";
    }
}