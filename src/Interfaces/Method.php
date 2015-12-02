<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 16:16
 */

/**
 * Интерфейс для методов оценки данных
 */
namespace Parser\Interfaces;
use \RollingCurl\Request;

interface Method {
    public function execute(Request $old_request, Request $new_request);
    public function getColumns();
}