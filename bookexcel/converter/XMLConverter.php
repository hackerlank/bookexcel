<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

class XMLConverter implements IConverter
{

    public function convertHeader(array $params)
    {
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

        $arr = array('<item');
        foreach ($nameRow as $k => $v) {
            $value = htmlspecialchars($dataRow[$k]);
            $arr[] = sprintf('%s="%s"', $v, $value);
        }
        $arr[] = "/>" . $convertParams['endOfLine'];
        return implode(' ', $arr);
    }
}
