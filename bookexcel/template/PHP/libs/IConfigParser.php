<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

interface IDataParser extends \Iterator
{
    public function getCurrentRow();

    public function setArrayDelemiter($delimi, $innerDelimi);

    public function parseString($content);

    /**
     * $column: columnName or columnIndex
     */
    public function has($column);

    /**
     * get a value
     * $column: columnName or columnIndex
     */
    public function getValue($column, $type);

    /**
     * get array
     * $column: columnName or columnIndex
     */
    public function getList($column, $type);

    /**
     * get array of array
     * $column: columnName or columnIndex
     */
    public function getListGroup($column, $type);
}
