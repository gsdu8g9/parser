<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 15:20
 */

namespace Parser\Filters;


class MetaKeywords extends \Parser\Filter {
    public function __construct() {
        parent::__construct("/<meta name=\"keywords\" content=\"(.+)\">/siU");
    }
}