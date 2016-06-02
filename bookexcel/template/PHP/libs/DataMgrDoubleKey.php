<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

abstract class DataMgrDoubleKey extends DataMgrBase
{

    public function init($text, $format = '')
    {
        return $this->doInit($text, $format, 2);
    }

    /**
     * @return array[key=>item ...]
     */
    public function getAllSortedItems()
    {
        return new $this->itemSortList;
    }

    public function getItem($key1, $key2)
    {
        return isset($this->itemSortList[$key1][$key2]) ?
        $this->itemSortList[$key1][$key2] : null;
    }

    public function getItemGroup($key1)
    {
        if (isset($this->itemSortList[$key1])) {
            return $this->itemSortList[$key1];
        }
        return null;
    }

    public function hasItem($key1, $key2)
    {
        return isset($this->itemSortList[$key1][$key2]);
    }

    public function hasItemGroup($key1)
    {
        return isset($this->itemSortList[$key1]);
    }

}
