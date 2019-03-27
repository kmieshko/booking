<?php

namespace vendor\core\base;

use vendor\core\Db;

abstract class Model
{

    protected $pdo;
    protected $table;
    protected $pk = 'id';
    public $attributes = [];
    public $errors = [
        'login' => [],
        'password' => [],
        'email' => [],
        'unique' => []
    ];

     public function __construct()
     {
         $this->pdo = Db::instance();
     }

     public function query($sql)
     {
         return $this->pdo->execute($sql);
     }

     public function findBySql($sql, $params = [])
     {
         return $this->pdo->query($sql, $params);
     }
}