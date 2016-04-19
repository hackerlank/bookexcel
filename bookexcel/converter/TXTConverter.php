<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

class TXTConverter extends ConverterBase
{

    const DELIMITER = "\t";

    public function convertHeader(array $params)
    {
        $this->checkKVSheet($params);

        $nameRow = $params['nameRow'];
        $convertParams = $params['convertParams'];

        $this->removeItemType($params, $nameRow);

        foreach ($nameRow as $k => $v) {
            $nameRow[$k] = $this->escape($v);
        }

        if ($params['sheetType'] == SHEET_TYPE_KV) {
            unset($nameRow['itemType']);
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

        $this->removeItemType($params, $dataRow);

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
