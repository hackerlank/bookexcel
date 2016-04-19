<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

interface ICodeGenerator
{
    /**
     * generate code
     * @param  array  $params [array $nameRow, array $typeRow,
     * array $descRow, $sheetName, $sheetType, $convertParams]
     * @return string
     */
    public function generate(array $params);

    public function start(array $params);

    public function end(array $params);
}
