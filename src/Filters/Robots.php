<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 18:39
 */

namespace Parser\Filters;
use \Parser\Filter;
use \Parser\Traits\Singleton;
use \Parser\Methods\Robots as RobotsMethod;

class Robots extends Filter {
    use Singleton;

    protected function __construct() {}
    /**
     * Return method to compare data
     * @return \Parser\Method
     */
    public function getMethod() {
        return new RobotsMethod($this);
    }
}