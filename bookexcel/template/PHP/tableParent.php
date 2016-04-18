﻿{%echo '<?php'%}
/**
 * Generated by bookexcel
 * Do't modify this file directly, modify child class file instead
 */

{% if ($package != ''): %}
namespace {%$package%} 
{
{% endif; %}
    class {%$className%}{%if($parentClassName):%} : {%$parentClassName%}{%endif;%}
    {
        {% foreach ($fields as $field): %}
        protected const $_{%:strtoupper($field['name'])%} = "{%$field['name']%}";
        {% endforeach; %}

        {% foreach ($fields as $field): %}
       
        protected ${%$field['name']%};
        /**
         * {%$field['desc']%} 
         */
        public function get{%:ucfirst($field['name'])%} 
        {
            return ${%$field['name']%};
        }
        {% endforeach; %}

        ///parse form {%$fileFormat%} 
        public override bool parseFrom(IConfigParser parser)
        {
            try{
                {% 
                    $pkey = 0;
                    foreach ($fields as $field): 
                    $name = $field['name'];
                    $uname = strtoupper($name);
                    $ctype = $this->convertType2($field['type']);
                    $type = $ctype[0];
                    $arrDeep = $ctype[1];
                    $get = 'getValue';
                    if ($arrDeep == 1) {
                        $get = 'getList';
                    } elseif($arrDeep > 1) {
                        $get = 'getListGroup';
                    }
                    $isPkey = $field['isPrimaryKey'];
                    $pkey += $isPkey ? 1 : 0;
                %}
                $this->{%$name%} = parser.{%$get%}<{%$type%}>(_{%$uname%});
                {% if ($isPkey && $pkey == 1): %}
                $this->_key1 = $this->{%$name%};
                {% elseif ($isPkey && $pkey == 2): %}
                $this->_key2 = $this->{%$name%};
                {% endif; %}
                {% endforeach; %}

                return true;
            } catch(Exception $e)
            {
                return false;
            }
        }
    }
{% if ($package != ''): %}
}
{% endif; %}