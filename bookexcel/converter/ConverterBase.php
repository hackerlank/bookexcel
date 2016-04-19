<?php

/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */
class ConverterBase implements IConverter
{
    public function convertHeader(array $params)
    {

    }

    public function convertFooter(array $params)
    {

    }

    public function convertItem(array $params)
    {

    }

    protected function removeItemType($params, &$row)
    {
        if ($params['sheetType'] == SHEET_TYPE_KV) {
            $key = array_search('itemType', $params['nameRow']);
            if ($key !== false) {
                unset($row[$key]);
            }
        }
    }

    protected function checkKVSheet($params)
    {
        if ($params['sheetType'] == SHEET_TYPE_KV) {
            $nameRow = $params['nameRow'];
            if (array_search('itemName', $nameRow) === false ||
                array_search('itemType', $nameRow) === false ||
                array_search('itemValue', $nameRow) === false
            ) {
                throw new Exception('The sheetType is kv, but lack itemName, itemType or itemValue');
            }
        }
    }

    protected function convertType($type, $value, $params)
    {
        if (substr_count($type, TYPE_ARRAY) > 0) {
            return $this->getArrayVal($type, $value, $params);
        } else {
            return $this->getval($type, $value);
        }
    }

    protected function getval($type, $value)
    {
        //need throw error when config is set incorrectly
        //return $value;
        switch ($type) {
            case TYPE_INT:
            case TYPE_UINT:
            case TYPE_LONG:
                return intval($value);
                break;
            case TYPE_BOOL:
                return strtolower($value) == 'true' || $value == '1';
                break;
            case TYPE_FLOAT:
                return floatval($value);
                break;
            case TYPE_DOUBLE:
                return doubleval($value);
                break;
            default:
                return $value;
                break;
        }
    }

    protected function getArrayVal($type, $value, $params)
    {
        $arrayDepth = substr_count($type, TYPE_ARRAY);

        if($arrayDepth == 0) {
            return $value;
        }

        $convertParams = $params['convertParams'];
        $type = trim(str_replace(TYPE_ARRAY, '', $type));
        $arr1 = explode($convertParams['arrayDelimiter'], $value);

        foreach ($arr1 as $k1 => $v1) {
            if ($arrayDepth > 1) {
                $arr2 = explode($convertParams['innerArrayDelimiter'], $v1);
                foreach ($arr2 as $k2 => $v2) {
                    $arr2[$k2] = $this->getval($type, $v2);
                }
                $arr1[$k1] = $arr2;
            } else {
                $arr1[$k1] = $this->getval($type, $v1);
            }
        }

        return $arr1;
    }
}
