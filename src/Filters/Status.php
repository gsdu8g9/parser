<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 15:59
 */
/**
 * Фильтр на статус
 */
namespace Parser\Filters;
use \Parser\Filter;
use \Parser\Traits\Singleton;
use \Parser\Methods\Status as StatusMethod;

class Status extends Filter {
    use Singleton;
    protected function __construct() {}
    /**
     * Return method to compare data
     * @return \Parser\Method
     */
    public function getMethod() {
        return new StatusMethod($this);
    }
}