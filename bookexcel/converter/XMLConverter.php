<?php

/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */
class XMLConverter extends ConverterBase
{
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

        $convertParams = $params['convertParams'];
        return sprintf(
            '<?xml version="1.0" encoding="%s"?>%s<root>%s',
            $convertParams['outputEncode'],
            $convertParams['endOfLine'],
            $convertParams['endOfLine']
        );
    }

    public function convertFooter(array $params)
    {
        return '</root>';
    }

    public function convertItem(array $params)
    {
        $nameRow = $params['nameRow'];
        $dataRow = $params['dataRow'];
        $convertParams = $params['convertParams'];
        $sheetType = $params['sheetType'];

        if ($sheetType == SHEET_TYPE_KV) {
            $key = $dataRow[$this->nameIndex];
            $value = htmlspecialchars($dataRow[$this->valueIndex]);
            return sprintf('<%s>%s</%s>', $key, $value, $key)  . $convertParams['endOfLine'];

        } else {
            $arr = array('<item');

            foreach ($nameRow as $k => $v) {
                $value = htmlspecialchars($dataRow[$k]);
                $arr[] = sprintf('%s="%s"', $v, $value);
            }

            $arr[] = "/>" . $convertParams['endOfLine'];
            return implode(' ', $arr);
        }
    }
}
