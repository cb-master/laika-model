# Cloud Bill Master Database Model
Cloud Bill Master Singleton Database Model is a PHP-based project that implements a robust, object-oriented database management system for handling complex transactions and data manipulation tasks. Built on top of MySQL, this singleton model aims to provide a high-performance, flexible, and secure way to interact with databases in PHP applications, specifically designed to streamline billing and cloud data management systems.

# Key Features
* <b>Object-Oriented Structure</b>: Built with PHP OOP principles, ensuring code reusability, scalability, and maintainability.</br>
* <b>Custom Database and Model Classes</b>: Uses a custom Database class for managing database connections, queries, and transactions, and a Model class to represent data entities in the application.</br>
* <b>Secure Transactions</b>: Implements ACID-compliant transactions for consistent and reliable data handling.</br>
* <b>Dynamic Query Builder</b>: Supports dynamic query generation with a range of options for filters, sorting, and pagination, making it easy to create complex queries without directly writing SQL.</br>
* <b>Error Handling</b>: Comprehensive error handling and logging for tracking and debugging issues efficiently.</br>
* <b>Scalable Architecture</b>: Designed with scalability in mind, suitable for all type of PHP applications.</br>
* <b>Easy Integration</b>: Integrates seamlessly with other PHP-based applications and frameworks, allowing flexible deployment in diverse environments.</br>

## Technologies Used
* <b>PHP (Object-Oriented)</b>: Core programming language, providing OOP features for structure and maintainability.</br>
* <b>MySQL</b>: Relational database management system used for data storage, with optimized queries for faster performance.</br>
* <b>PDO (PHP Data Objects)</b>: Utilized for secure database access with prepared statements to prevent SQL injection.</br>

## Installation
Install with composer:
```bash
composer require cb-master/laika-model
```
##  Connection Manager
Configure your database settings in your PHP application page top section.
To config use <b>ConnectionManager::add(array $config, string $name = 'default')</b>.
Array $config:
    * 'driver'  => [Required]: Accepted mysql/pgsql/sqlite. Example: mysql
    * 'host'    => [Optional]: localhost/127.0.0.1 || [Required]: If Foreign. Example: otherhost
    * 'port'    => [Optional]: 3306 || [Required]: If Port is Not 3306
    * 'database'=> [Required]: Your Database Name. Example: 'dbname'
    * 'username'=> [Required]: Your Database Username. Example: 'db_username'
    * 'password'=> [Required]: Your Database Password. Example: 'db_password'

String $name: Default is 'default'. Has Read & Write Access

```php
use Laika\Model\ConnectionManager;

// Require Autoload File
require_once("./vendor/autoload.php");
// Add Default Connection Manager
ConnectionManager::add(array $config); // DB Default Connection Details for Read & Write both

/**
 * Add Multiple Connection Manager. Default is for read, write or foreign
 */
ConnectionManager::add(array $config, 'other'); // DB Another Connection for Read & Write. Local or Foreign
ConnectionManager::add(array $ReadDbConfig, 'read'); // DB Connection Details for Read
ConnectionManager::add(array $WriteDbConfig, 'write'); // DB Connection Details for Write
```
## Usage
This project provides a base for any PHP application needing a reliable and efficient database model, especially useful for billing and cloud services. For detailed usage examples, please see the given method implementation below.

### Get PDO Connection
```php
// Get Default PDO Connection
$pdo = ConnectionManager::get();

// Get Read PDO Connection if Configured
$pdo = ConnectionManager::get('read');
// Get Write PDO Connection if Configured
$pdo = ConnectionManager::get('write');
// Get Other PDO Connection if Configured
$pdo = ConnectionManager::get('other');
```
Now you can execute any query by using any PDO methods.
### Get Laika Model Pre-build Methods
To use Laika Pre-build methods instead of PDO Methods you can use DB Class from Laika model.

```php
use Laika\Model\DB;

// Get Default DB Model
$db = DB::getInstance();

// Get Read DB Model if Configured
$db = DB::getInstance('read');
// Get Write DB Model if Configured
$db = DB::getInstance('write');
// Get Other DB Model if Configured
$db = DB::getInstance('other');

// Get All Columns Data from Table
$data = $db->table('table')->get();

// Get Selected Columns Data from Table
$data = $db->table('table')->select('column1,column2,column3')->get();

// Get Data from Table By Using Strings in Where Clause
$data = $db->table('table')->where('column', '=', 'value')->get();
// OR
// Get Data from Table By Using Array in Where Clause
$data = $db->table('table')->where(['id' => 1,'country'=>'usa'], '=', null, 'AND')->get();







```
Use between() method. Its like where. You also can use it with where() method. Between method needs 3 arguments {between(string $column, int|string $min, int|string $max, string $compare = 'AND')}
```php
// Get All Data Between min and max
Model::table('table_name')->between('id', 1, 10)->get();

// Get All Data Between min and max with multiple condition
Model::table('table_name')->between('id', 1, 10)->between('id', 50, 60)->get();

```

### Get Limited Data From Table (Default is 20)
Additional method to use is limit()
```php
// Get Data for Default Limit 20
Model::table('table_name')->limit()->get();

// Get Data for Default Limit 20 With Offset
Model::table('table_name')->limit()->offset(0)->get();

// Custom Limit Set
Model::table('table_name')->limit(40)->get();
```
