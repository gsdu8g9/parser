<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.10.15
 * Time: 16:30
 */

namespace Parser\Traits;


trait Singleton {
    static protected $instance;

    private function __construct() { /* ... @return Singleton */ }  // Защищаем от создания через new Singleton
    private function __clone() { /* ... @return Singleton */ }  // Защищаем от создания через клонирование
    private function __wakeup() { /* ... @return Singleton */ }  // Защищаем от создания через unserialize

    static public function getInstance() {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}