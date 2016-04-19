<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

include_once  __DIR__ . '/IConfigParser.php';

class ConfigItemBase
{
    protected $_key1;
    protected $_key2;

    public function parseFrom(IConfigParser $parser)
    {
        throw new Exception('not implements', 1);
    }

    public function getKey()
    {
        return $this->_key1;
    }

    public function getSecondKey()
    {
        return $this->_key2;
    }
}