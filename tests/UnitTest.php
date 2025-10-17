```


## tests/UnitTest.php


```php
<?php

use PHPUnit\Framework\TestCase;
use Laika\Model\ConnectionManager;


class UnitTest extends TestCase
{    
    public function testRenderSimple()
    {
        $driver = getenv('DB_DRIVER');
        $config = match ($driver) {
            'mysql', 'pgsql' => [
                'driver'   => $driver,
                'host'     => getenv('DB_HOST'),
                'username' => getenv('DB_USER'),
                'password' => '1234567890#ABcd',
                'database' => getenv('DB_NAME'),
                'port'     => getenv('DB_PORT')
            ],
            'sqlite' => [
                'driver'   => $driver,
                'database' => getenv('DB_PATH')
            ],
            default =>  [
                'driver'   => $driver,
                'database' => getenv('DB_PATH')
            ]
        };

        $conn = ConnectionManager::add($config);
        $this->assertNotNull($conn, "Failed to initialize connection for {$driver}");
    }
}
