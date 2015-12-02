<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 11:08
 */
/**
 * Wrap regexp to filter content
 */
namespace Parser;
use \RollingCurl\Request;
use \Parser\Interfaces\Filter as IFilter;

abstract class Filter implements IFilter{
    use Traits\Named;

    private $__regexp = '';

    protected function __construct($regexp) {
        $this->__regexp = $regexp;
    }

    public function filter(Request $data) {
        preg_match($this->__regexp, $data->getResponseText(), $match);
        return (!empty($match[1])) ? $match[1] : NULL;
    }
}
