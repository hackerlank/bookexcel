<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

interface IConverter
{
    /**
     * convert start
     * @param  array  $params [array $nameRow, array $typeRow,
     * array $descRow, $sheetName, $sheetType, $convertParams]
     * @return [type]         string
     */
    public function convertHeader(array $params);
    /**
     * convert end
     * @param  array  $params [array $nameRow, array $typeRow,
     * array $descRow, $sheetName, $sheetType, $convertParams]
     * @return [type]         string
     */
    public function convertFooter(array $params);
    /**
     * convert
     * @param  array  $params [array $nameRow, array $typeRow,
     * array $descRow, $dataRow, $sheetName, $sheetType, $convertParams]
     * @return [type]         string
     */
    public function convertItem(array $params);
}
