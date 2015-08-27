<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 25.08.15
 * Time: 10:19
 */
namespace Parser\Interfaces;
interface Algorithm {
    /**
     * Compare text function
     * @param $first
     * @param $second
     * @return mixed
     */
    public function check_it($first, $second);
}