<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

/**
 * PHPCodeGenerator
 */
class PHPCodeGenerator extends CodeGeneratorBase
{
    private $typeMap = array(
        'double' => 'double',
        'float' => 'float',
        'int' => 'int',
        'uint' => 'int',
        'long' => 'int',
        'ulong' => 'int',
        'bool' => 'bool',
        'string' => 'string',
    );

    public function __construct()
    {
        $this->templateDir = APP_ROOT . 'template/PHP/';
        $this->defaultParentClass = 'ConfigItemBase';
        $this->defaultmanagerParentClass = 'ConfigMgrSingleKey';
    }

    public function convertType($type)
    {
        $typeMap = $this->typeMap;
        $arr = explode('[', $type, 2);
        $type = trim($arr[0]);
        $type = isset($typeMap[$type]) ? $typeMap[$type] : 'string';
        if (count($arr) > 1) {
            $arr[1] = '[' . $arr[1];
            $type .=  trim($arr[1]);
        }
        return $type;
    }

    public function convertType2($type)
    {
        $typeMap = $this->typeMap;
        $arr = explode('[', $type, 2);
        $type = trim($arr[0]);
        $type = isset($typeMap[$type]) ? $typeMap[$type] : 'string';
        if (count($arr) > 1) {
            $arr[1] = '[' . $arr[1];
            return array($type, substr_count($arr[1], '[]'));
        }
        return array($type, 0);
    }

    public function convertPackage($package)
    {
        if (strpos($package, '\\') !== false) {
            return $package;
        }

        return str_replace('.', '\\', $package);
    }
}
