<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 18:38
 */

namespace Parser\Methods;
use \Parser\Method;
use \RollingCurl\Request;
use \Parser\Xbb_RobotsTxt;

class Robots extends Method {
    protected function setColumns() {
        $this->_columns[$this->_filter->getName().'_old'] = '';
        $this->_columns[$this->_filter->getName().'_new'] = '';
    }

    public function execute(Request $old_request, Request $new_request) {
        $old_url = parse_url($old_request->getUrl());
        $new_url = parse_url($new_request->getUrl());
        $old_robots = Xbb_RobotsTxt::getInstance($old_url["scheme"].'://'.$old_url["host"]);
        $new_robots = Xbb_RobotsTxt::getInstance($new_url["scheme"].'://'.$new_url["host"]);
        $this->_columns[$this->_filter->getName().'_old'] = (int)$old_robots->allow($old_request->getUrl());
        $this->_columns[$this->_filter->getName().'_new'] = (int)$new_robots->allow($new_request->getUrl());
        return $this->_columns;
    }
}