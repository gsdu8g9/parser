<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 15:54
 */

namespace Parser\Interfaces;


interface Filter {
    public function filter(\RollingCurl\Request $data);
}