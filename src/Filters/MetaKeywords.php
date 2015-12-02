<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 15:20
 */

namespace Parser\Filters;
use Parser\Traits\Singleton;
use \Parser\Methods\Text;

class MetaKeywords extends \Parser\Filter {
    use Singleton;
    protected function __construct() {
        parent::__construct("/<meta[^>]*name=[\"|\']keywords[\"|\'][^>]*content=[\"]([^\"]*)[\"][^>]*>/i");
    }
    /**
     * Return method to compare data
     * @return \Parser\Method
     */
    public function getMethod() {
        return new Text($this);
    }
}