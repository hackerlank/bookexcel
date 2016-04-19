<?php

/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */
class JSONConverter extends ConverterBase
{
    private $isFirst = false;

    private $nameIndex;
    private $typeIndex;
    private $valueIndex;

    public function convertHeader(array $params)
    {
        $this->checkKVSheet($params);

        if ($params['sheetType'] == SHEET_TYPE_KV) {
            $nameRow = $params['nameRow'];
            $this->nameIndex = array_search('itemName', $nameRow);
            $this->typeIndex = array_search('itemType', $nameRow);
            $this->valueIndex = array_search('itemValue', $nameRow);
        }

        $this->isFirst = true;
        return "[" . $params['convertParams']['endOfLine'];
    }

    public function convertFooter(array $params)
    {
        return $params['convertParams']['endOfLine'] . "]";
    }

    public function convertItem(array $params)
    {
        $nameRow = $params['nameRow'];
        $typeRow = $params['typeRow'];
        $dataRow = $params['dataRow'];
        $sheetType = $params['sheetType'];
        $convertParams = $params['convertParams'];

        $arr = array();

        if ($sheetType == SHEET_TYPE_KV) {
            $arr[$dataRow[$this->nameIndex]] = $this->convertType(
                $dataRow[$this->typeIndex],
                $dataRow[$this->valueIndex],
                $params
            );
        } else {
            foreach ($nameRow as $k => $v) {
                $arr[$v] = $this->convertType($typeRow[$k], $dataRow[$k], $params);
            }
        }

        $result = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $result = str_replace("\\\\", "\\", $result);

        if ($this->isFirst) {
            $this->isFirst = false;
        } else {
            $result = ',' . $convertParams['endOfLine'] . $result;
        }

        return $result;
    }
}
