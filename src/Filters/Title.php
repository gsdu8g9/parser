<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 11:54
 */

/**
 * Search for title in content
 */
namespace Parser\Filters;
use Parser\Traits\Singleton;
use \Parser\Methods\Text;

class Title extends \Parser\Filter {
    use Singleton;
    protected function __construct() {
        parent::__construct("/<title>(.+)<\/title>/siU");
    }
    /**
     * Return method to compare data
     * @return \Parser\Method
     */
    public function getMethod() {
        return new Text($this);
    }
}