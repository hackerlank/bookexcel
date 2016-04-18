<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

class ParseUtil
{
    public static function getList($content, $type = 'string', $delimi = ';')
    {
        if ($content == '') {
            return array();
        }

        $arr = explode($delimi, $content);
        $tarr = array();

        foreach ($arr as $val) {
            if (!self::canConvert($val, $type)) {
                throw new Exception("Can't convert {$val} to {$type}", 1);
            }
            $tarr[] = self::convertType($val, $type);
        }

        return $tarr;
    }

    public static function getListGroup(
        $content,
        $type = 'string',
        $delimi = ';',
        $innerDelimi = ':'
    ) {
        if ($content == '') {
            return array();
        }

        $arr = explode($delimi, $content);
        $tarr = array();

        foreach ($arr as $val) {
            $arr2 = explode($innerDelimi, $val);
            $tarr2 = array();

            foreach ($arr2 as $val2) {
                if (!self::canConvert($val2, $type)) {
                    throw new \Exception("Can't convert {$val2} to {$type}", 1);
                }
                $tarr2[] = self::convertType($val2, $type);
            }
            $tarr[] = $tarr2;
        }

        return $tarr;
    }

    public static function canConvert($val, $type)
    {
        $b = false;
        switch ($type) {
            case 'string':
                $b = true;
                break;
            case 'int':
            case 'uint':
            case 'long':
            case 'ulong':
            case 'float':
            case 'double':
                $b = is_numeric($val);
                break;
            case 'bool':
            case 'boolean':
                $val = strtolower($val);
                $b = $val == 'true' ||
                    $val == 'false' ||
                    $val == '0' ||
                    $val == '1';
                break;
        }

        return $b;
    }

    public static function convertType($val, $type)
    {
        switch ($type) {
            case 'string':
                $val = strval($val);
                break;
            case 'int':
            case 'uint':
            case 'long':
            case 'ulong':
                $val = intval($val);
                break;
            case 'float':
                $val = floatval($val);
                break;
            case 'double':
                $val = doubleval($val);
                break;
            case 'bool':
            case 'boolean':
                $val = boolval($val);
                break;
        }

        return $val;
    }

}
