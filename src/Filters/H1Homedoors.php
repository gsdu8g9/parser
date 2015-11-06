<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 16:41
 */

namespace Parser\Filters;


class H1Homedoors extends \Parser\Filter {
    public function __construct() {
        parent::__construct("/<body>.*<h1>(.+)<.*<\/body>/siU");
    }
}