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

abstract class Model
{
    // Database Object
    public Db $db;

    // Table Name
    public string $table;

    // Table Primary Column ID Name
    public string $id;

    // Table UUID Column Name
    public string $uuid;

    // protected string $name;

    public function __construct(string $name = 'default')
    {
        $this->db = DB::getInstance($name);
    }

    /**
     * @param array $where Optional parameter. Default is []
     * @param string $operator Optional parameter. Default is '='
     * @param string $compare Optional parameter. Default is 'AND'
     * @return array
     */
    public function all(array $where = [], string $operator = '=', string $compare = 'AND'):array
    {
        // $db = DB::getInstance($this->name)->table($this->table);
        return $where ? $this->db->table($this->table)->where($where, $operator, compare:$compare)->get() :
                        $this->db->table($this->table)->get();
    }

    /**
     * @param array $where Required parameter.
     * @param string $operator Optional parameter. Default is '='
     * @param string $compare Optional parameter. Default is 'OR'
     * @return array
     */
    public function find(array $where, string $operator = '=', string $compare = 'OR'):array
    {
        return $this->db->table($this->table)->where($where, $operator, compare:$compare)->get();
    }

    /**
     * @param array $where Required parameter.
     * @param string $operator Optional parameter. Default is '='
     * @param string $compare Optional parameter. Default is 'OR'
     * @return array
     */
    public function first(array $where, string $operator = '=', string $compare = 'AND'):array
    {
        return $this->db->table($this->table)->where($where, $operator, compare:$compare)->first();
    }

    /**
     * @param array $where Required parameter.
     * @param string $operator Optional parameter. Default is '='
     * @param string $compare Optional parameter. Default is 'AND'
     * @return int
     */
    public function delete(array $where, string $operator = '=', string $compare = 'AND'):int
    {
        return $this->db->table($this->table)->where($where, $operator, compare:$compare)->delete();
    }

    /**
     * @param array $data Required parameter.
     * @return int
     */
    public function insert(array $data):int
    {
        return $this->db->table($this->table)->insert($data);
    }

    /**
     * @param array $rows Required parameter.
     * @return int
     */
    public function insertMany(array $rows):bool
    {
        return $this->db->table($this->table)->insertMany($rows);
    }

    /**
     * @param array $where Required parameter.
     * @param array $data Required parameter.
     * @return int
     */
    public function update(array $where, array $data):int
    {
        return $this->db->table($this->table)->where($where)->update($data);
    }

    // Generate UUID
    /**
     * @param ?string $column Optional Argument. Default is null
     * @return string
     */
    public function uuid(?string $column = null):string
    {
        $column = $column ?: $this->uuid;
        $time = substr(str_replace('.', '', microtime(true)), -6);
        $uid = 'uuid-'.bin2hex(random_bytes(3)).'-'.bin2hex(random_bytes(3)).'-'.bin2hex(random_bytes(3)).'-'.bin2hex(random_bytes(3)).'-'.$time;
        // Check Already Exist & Return
        if($this->db->table($this->table)->where($column, '=', $uid)->first()){
            return $this->uuid($column);
        }
        return $uid;
    }

    // Count Column
    public function count(string $column = null, array $where = [], string $operator = '=', string $compare = 'AND'): int
    {
        $column = $column ?: $this->id;
        return $where ? $this->db->table($this->table)->where($where, $operator, null, $compare)->count($column):
                        $this->db->table($this->table)->count($column);
    }
}