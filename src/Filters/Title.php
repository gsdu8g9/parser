<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 19.08.15
 * Time: 11:54
 */

/**
 * Search for title in content
 */
namespace Parser\Filters;


class Title extends \Parser\Filter {
    public function __construct() {
        parent::__construct("/<title>(.+)<\/title>/siU");
    }
}