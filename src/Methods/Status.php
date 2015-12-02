<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 17:21
 */

namespace Parser\Methods;
use \Parser\Method;
use \RollingCurl\Request;

class Status extends Method {

    protected function setColumns() {
        $this->_columns[$this->_filter->getName().'_old'] = '';
        $this->_columns[$this->_filter->getName().'_new'] = '';
    }

    public function execute(Request $old_request, Request $new_request) {
        $this->_columns[$this->_filter->getName().'_old'] = $old_request->getResponseInfo()["http_code"];
        $this->_columns[$this->_filter->getName().'_new'] = $new_request->getResponseInfo()["http_code"];
        return $this->_columns;
    }
}