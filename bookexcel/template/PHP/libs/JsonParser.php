<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

class JsonParser implements IConfigParser
{
    private $body;

    private $arrayDelimiter;

    private $innerArrayDelimiter;

    private $currentRow;

    public function __construct()
    {
        $this->arrayDelimiter = ';';
        $this->innerArrayDelimiter = ':';
    }

    public function parseString($content)
    {
        if ($content == '') {
            return false;
        }

        try{
            $this->body = json_decode($content, true);

        } catch(Exception $e){
            //to do log
            return false;
        }
        
        return true;
    }

    public function getCurrentRow()
    {
        return $this->currentRow;
    }

    public function setArrayDelemiter($delimi, $innerDelimi)
    {
        $this->arrayDelimiter = $delimi;
        $this->innerArrayDelimiter = $innerDelimi;
    }

    public function has($column)
    {
        $row = $this->body[$this->currentRow];

        if (is_string($column)) {
            return isset($row[$column]);
        }

        return $column > 0 && $column < count($row);
    }

    public function getValue($column, $type)
    {
        return $this->getColumnValue($column, $type);
    }

    public function getString($column)
    {
        return $this->getColumnValue($column, 'string');
    }

    public function getBool($column)
    {
        return $this->getColumnValue($column, 'bool');
    }

    public function getInt($column)
    {
        return $this->getColumnValue($column, 'int');
    }

    public function getDouble($column)
    {
        return $this->getColumnValue($column, 'double');
    }

    public function getFloat($column)
    {
        return $this->getColumnValue($column, 'float');
    }

    public function getList($column, $type)
    {
        if (!isset($this->body[$this->currentRow][$column])) {
            throw new ConfigException(
                sprintf("JsonParser: cannot read at row(%d) and column(%s)",
                    $this->currentRow, $column));
        }

        $val = $this->body[$this->currentRow][$column];
        if (!is_array($val) || (!empty($val) && !ParseUtil::isType($val[0], $type))) {
            throw new ConfigException(
                sprintf("JsonParser: cannot convert to %s[] at row(%d) and column(%s)",
                    $type, $this->currentRow, $column));
        }

        return $val;
    }

    public function getListGroup($column, $type)
    {
        if (!isset($this->body[$this->currentRow][$column])) {
            throw new ConfigException(
                sprintf("JsonParser: cannot read at row(%d) and column(%s)",
                    $this->currentRow, $column));
        }

        $val = $this->body[$this->currentRow][$column];
        if (!is_array($val) || (!isset($val[0][0]) && !ParseUtil::isType($val[0][0], $type))) {
            throw new ConfigException(
                sprintf("JsonParser: cannot convert to %s[][] at row(%d) and column(%s)",
                    $type, $this->currentRow, $column));
        }

        return $val;
    }

    private function getColumnValue($column, $type)
    {
        $row = $this->body[$this->currentRow];
        
        if (!isset($row[$column])) {
            throw new ConfigException(
                sprintf("JsonParser: cannot read at row(%d) and column(%s)",
                    $this->currentRow, $column));
        }

        $val = $row[$column];

        if (!ParseUtil::isType($val, $type)) {
            throw new ConfigException(
                sprintf("JsonParser: cannot convert to %s at row(%d) and column(%s)",
                    $type, $this->currentRow, $column));
        }

        return $val;
    }

    //region Iterator

    public function rewind()
    {
        $this->currentRow = -1;
    }

    public function current()
    {return $this;}

    public function key()
    {return $this->currentRow;}

    public function next()
    {
        $this->currentRow++;
        return $this;
    }

    public function valid()
    {
        return $this->currentRow >= 0 &&
        $this->currentRow < count($this->body);
    }

    //endregion iterator
}
