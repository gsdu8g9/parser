<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 16:34
 */
/**
 * Method to compare texts
 */
namespace Parser\Methods;
use \Parser\Method;
use \RollingCurl\Request;
use \Parser\Algorithms\Shingle;

class Text extends Method {
    /**
     * @param Request $old_request
     * @param Request $new_request
     * @return array
     */
    public function execute(Request $old_request, Request $new_request) {
        $this->_columns[$this->_filter->getName()] = Shingle::getInstance()->check_it(
            $this->_filter->filter($old_request),
            $this->_filter->filter($new_request)
        );
        return $this->_columns;
    }
}