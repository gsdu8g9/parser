<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 15:15
 */

namespace Parser\Filters;


class MetaDescription extends \Parser\Filter {
    public function __construct() {
        parent::__construct("/<meta.*name==\"|\'description=\"|\'.*content==\"|\'(.+)=\"|\'.*>/siU");
    }
}