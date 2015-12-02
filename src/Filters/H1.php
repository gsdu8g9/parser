<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 16:41
 */

namespace Parser\Filters;
use \Parser\Traits\Singleton;
use \Parser\Methods\Text;

class H1 extends \Parser\Filter {
    use Singleton;
    protected function __construct() {
        parent::__construct("/<body>.*<h1>(.+)<\/h1>.*<\/body>/siU");
    }

    /**
     * Return method to compare data
     * @return \Parser\Method
     */
    public function getMethod() {
        return new Text($this);
    }
}