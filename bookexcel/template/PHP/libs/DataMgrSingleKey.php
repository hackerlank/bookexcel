<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

abstract class DataMgrSingleKey extends DataMgrBase
{

    public function init($text, $format = '')
    {
        return $this->doInit($text, $format, 1);
    }

    /**
     * @return array[key=>item ...]
     */
    public function getAllSortedItems()
    {
        return new $this->itemSortList;
    }

    public function getItem($key)
    {
        return isset($this->itemSortList[$key]) ? $this->itemSortList[$key] : null;
    }

    public function hasItem($key)
    {
        return isset($this->itemSortList[$key]);
    }
}
