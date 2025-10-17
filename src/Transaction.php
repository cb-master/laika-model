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

use PDOException;
use Closure;

class Transaction extends DB
{
    // Run Transaction
    /**
     * @param Closure $callback Required Argument.
     * @param string $connection_name Optional Argument. Default is 'default'
     * @return array<string,mixed> Example ['error'=>false, 'message'=>'Anything']
     */
    public static function run(Closure $callback, string $connection_name = 'default'): array
    {
        try {
            $db     =   self::getInstance($connection_name);
            $db->pdo->beginTransaction();
            $result =   $callback($db);

            $db->pdo->commit();

            return [
                'error'     =>  false,
                'message'   =>  $result
            ];
        } catch (PDOException $e) {
            $db->pdo->rollBack();
            return [
                'error'     =>  true,
                'message'   =>  $e->getMessage()
            ];
        }
    }
}
