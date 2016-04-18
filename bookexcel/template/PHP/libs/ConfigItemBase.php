<?php
/**
* 
*/
class ConfigItemBase
{
    protected $_key1;
    protected $_key2;

    public bool parseFrom(IConfigParser parser)
    {
        throw new NotImplementedException();
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