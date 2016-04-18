<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

include 'ConfigException.php';
include 'IConfigParser.php';
include 'ParseUtil.php';

/**
 * Parse tab delimited string, much like csv
 */
class TxtParser implements IConfigParser
{
    private $title;
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

        if (strpos($content, "\r\n") !== false) {
            $delimi = "\r\n";
        } elseif (strpos($content, "\n") !== false) {
            $delimi = "\n";
        } else {
            $delimi = "\r";
        }

        $arr = explode($delimi, $content);
        $this->title = explode("\t", $arr[0]);

        $this->body = array();
        for ($i = 1; $i < count($arr); $i++) {
            $this->body[] = explode("\t", $arr[$i]);
        }
        $this->rewind();
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
        if (is_string($column)) {
            $index = array_search($column, $this->title);
            return $index !== false;
        }

        $row = $this->body[$this->currentRow];
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
        return ParseUtil::getList(
            $this->getString($column),
            $type,
            $this->arrayDelimiter
        );
    }

    public function getListGroup($column, $type)
    {
        return ParseUtil::getListGroup(
            $this->getString($column),
            $type,
            $this->arrayDelimiter,
            $this->innerArrayDelimiter
        );
    }

    private function getColumnValue($column, $type)
    {
        if (is_string($column)) {
            $column = array_search($column, $this->title);
        }

        $row = $this->body[$this->currentRow];
        if ($column < 0 || $column >= count($row)) {
            throw new ConfigException(
                sprintf("TxtParser: cannot read at row(%s) and column(%s)",
                    $this->currentRow, $column));
        }

        $val = $row[$column];

        if (!ParseUtil::canConvert($val, $type)) {
            throw new ConfigException(
                sprintf("TxtParser: cannot read at row(%s) and column(%s)",
                    $this->currentRow, $column));
        }

        return ParseUtil::convertType($val, $type);
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

$tp = new TxtParser();
$tp->parseString(file_get_contents('Sheet.txt'));
$tp->next();
$id = $tp->getValue(0, 'int');

$n = $tp->getList('name', 'string');
$n2 = $tp->getListGroup('color', 'float');

$tp = null;
