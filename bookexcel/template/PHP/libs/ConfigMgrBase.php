<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
 * @license The MIT License
 */

abstract class ConfigMgrBase
{
    /**
     * [ key=>obj ...]
     * or
     * [ key1=>[key2=>obj ...] ...]
     */
    protected $itemSortList = array();
    /**
     * @var array
     */
    protected $itemList = array();

    /**
     * @var IConfigParser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $itemClass;

    public function init($text, $format = '')
    {
        $this->doInit($text, $format, 0);
    }

    protected function doInit($text, $format = '', $keyCount = 0)
    {
        if ($text == '') {
            //Debug.LogErrorFormat("Failed to init: {0}, text is null", this.ToString());
            return false;
        }

        if (!empty($this->itemList)) {
            //Debug.LogWarningFormat("Repeated init: {0}", this.ToString());
            $this->itemSortList = array();
            $this->itemList = array();
        }

        $parser = $this->getParser($format);
        if ($parser == null) {
            // Debug.LogErrorFormat("Failed to init: {0}, no parser for format: {1}", this.ToString(), format);
            return false;
        }

        if (!$parser->parseString($text)) {
            // Debug.LogErrorFormat("Failed to init: {0}, cannot parse {1} text", this.ToString(), format);
            return false;
        }

        if ($this->itemClass == null) {
            // Debug.LogErrorFormat("Failed to init: , please call setItemClass first");
            return false;
        }

        $i = 0;

        foreach ($parser as $tp) {
            $item = new $this->itemClass();
            
            if (!$item->parseFrom($tp)) {
                // Debug.LogErrorFormat("Failed to init:{0}, error at row({1})",
                //     this.ToString(), i);
                continue;
            }

            if ($keyCount == 2) {
                $key = $item->getKey();
                $key2 = $item->getSecondKey();

                if (!isset($this->itemSortList[$key])) {
                    $this->itemSortList[$key] = array($key2 => $item);
                } elseif (isset($this->itemSortList[$key][$key2])) {
                    // Debug.LogWarningFormat("init:{0}, multi key({1}) at row({2})",
                    //     this.ToString(), key, i);
                }
                $this->itemSortList[$key][$key2] = $item;

            } else if ($keyCount == 1) {
                $key = $item->getKey();

                if (isset($this->itemSortList[$key])) {
                    // Debug.LogWarningFormat("init:{0}, multi key({1}) at row({2})",
                    //     this.ToString(), key, i);
                }
                $this->itemSortList[$key] = $item;
            }

            $this->itemList[] = $item;
            $i++;
        }
        return true;
    }

    protected function getParser($format)
    {
        switch ($format) {
            case "txt":
                $this->parser = new TxtParser();
                break;
            case "json":
                $this->parser = new JsonParser();
                break;
            default:
                break;
        }

        return $this->parser;
    }

    public function setParser(IConfigParser $parser)
    {
        $this->parser = $parser;
    }

    public function setItemClass(string $class)
    {
        $this->itemClass = $class;
    }

    /**
     * @return array[item ...]
     */
    public function getAllItems()
    {
        return $this->itemList;
    }

}
