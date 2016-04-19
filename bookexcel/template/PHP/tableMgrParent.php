﻿{%echo '<?php'%}
/**
 * Generated by bookexcel
 * Do't modify this file directly, modify child class file instead
 */

{% if ($package != ''): %}
namespace {%:$this->convertPackage($package)%};
{% endif; %}

require_once __DIR__ . '/{%$className%}.php';

{%$filename = $managerClassName.$parentSuffix;%}
class {%:$managerClassName.$parentSuffix%} extends \{%$managerParentClass%} 
{
    public function __construct()
    {
        $parser = new \{%:ucfirst($fileFormat)%}Parser();
        $parser->setArrayDelemiter('{%$arrayDelimiter%}', '{%$innerArrayDelimiter%}');
        $this->setParser($parser);
        $this->setItemClass('{%$className%}');
    }

    {% 
        foreach ($fields as $field): 
        list($type, $arrDeep) = $this->convertType2($field['type']);
        if ($field['createQuery'] && $arrDeep == 0) :
        $name = $field['name'];
        $uname = ucfirst($name);
    %}
    public function getItemBy{%$uname%}($value)
    {
        foreach ($this->itemList as $item) 
        {
            if ($item->{%$name%} == $value) {
                return $item;
            }
        }

        return null;
    }

    public function getItemsBy{%$uname%}($value)
    {
        $items = array();
        foreach ($this->itemList as $item) 
        {
            if ($item->{%$name%} == $value) {
                $items[] = $item;
            }
        }
        return $items;
    }

    {% endif; %}
    {% endforeach; %}
}