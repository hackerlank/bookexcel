{%echo '<?php'%} 
/**
 * You can modify this file for your purpose
 */

{% if ($package != ''): %}
namespace {%:$this->convertPackage($package)%};
{% endif; %}

require_once __DIR__ . '/{%:$className . $parentSuffix%}.php';

{%$filename = $className; $override=false%}
class {%$className%} extends {%:$className . $parentSuffix%} 
{
    public function parseFrom(\IConfigParser $parser)
    {
        if(parent::parseFrom($parser))
        {
            //your code
            return true;
        }

        return false;
    }
}
