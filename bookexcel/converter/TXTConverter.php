<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

class TXTConverter implements IConverter
{

    const DELIMITER = "\t";

    public function convertHeader(array $params)
    {
        $nameRow = $params['nameRow'];
        $convertParams = $params['convertParams'];

        foreach ($nameRow as $k => $v) {
            $nameRow[$k] = $this->escape($v);
        }
        return implode(self::DELIMITER, $nameRow) . $convertParams['endOfLine'];
    }

    public function convertFooter(array $params)
    {
        return '';
    }

    public function convertItem(array $params)
    {
        $dataRow = $params['dataRow'];
        $convertParams = $params['convertParams'];

        foreach ($dataRow as $k => $v) {
            $dataRow[$k] = $this->escape($v);
        }
        return implode(self::DELIMITER, $dataRow) . $convertParams['endOfLine'];
    }

    //去掉tab
    private function escape($str)
    {
        return str_replace(self::DELIMITER, '', $str);
    }
}
