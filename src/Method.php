<?php
/**
 * Created by PhpStorm.
 * User: a6y
 * Date: 01.12.15
 * Time: 16:21
 */

namespace Parser;
use \Parser\Interfaces\Method as IMethod;

/**
 * Метод оценки
 * @package Parser
 */
abstract class Method implements IMethod {
    protected $_filter = NULL;
    protected $_columns = array();

    public function __construct(Filter $filter) {
        $this->_filter = $filter;
        $this->setColumns();
    }

    /**
     * Set result columns
     */
    protected function setColumns() {
        $this->_columns[$this->_filter->getName()] = '';
    }

    /**
     * Default result column name as filter name
     * @return string
     */
    final public function getColumns() {
        return implode(",", array_keys($this->_columns));
    }
}