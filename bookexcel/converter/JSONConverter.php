<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

class JSONConverter implements IConverter
{
    private $isFirst = false;

    public function convertHeader(array $params)
    {
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
        $descRow = $params['descRow'];
        $sheetType = $params['sheetType'];
        $sheetName = $params['sheetName'];
        $convertParams = $params['convertParams'];

        $arr = array();

        foreach ($nameRow as $k => $v) {
            $type = $typeRow[$k];
            $arrayDepth = substr_count($type, TYPE_ARRAY);

            if ($arrayDepth > 0) {
                $type = trim(str_replace(TYPE_ARRAY, '', $type));
                $arr1 = explode($convertParams['arrayDelimiter'], $dataRow[$k]);

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
                $arr[$v] = $arr1;
            } else {
                $arr[$v] = $this->getval($type, $dataRow[$k]);
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

    private function getval($type, $value)
    {
        switch ($type) {
            case TYPE_INT:
            case TYPE_UINT:
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
}
