<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 25.08.15
 * Time: 13:40
 */
namespace Parser\Traits;
trait Named {
    /**
     * Get filter name
     * @return string
     */
    public function getName() {
        return (new \ReflectionClass($this))->getShortName();
    }
}