<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

/**
 * CSharp
 */
class CSharpCodeGenerator extends CodeGeneratorBase
{
    private $typeMap = array(
        'double' => 'double',
        'float' => 'float',
        'int' => 'int',
        'uint' => 'uint',
        'long' => 'long',
        'ulong' => 'ulong',
        'bool' => 'bool',
        'string' => 'string',
    );

    public function __construct()
    {
    	$this->templateDir = APP_ROOT . 'template/C#/';
    	$this->defaultParentClass = 'bookrpg.config.ConfigItemBase';
    	$this->defaultmanagerParentClass = 'bookrpg.config.ConfigMgrSingleKey';
        $this->createTwoClass = false;
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

}
