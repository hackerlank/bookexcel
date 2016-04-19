{%echo '<?php'%} 
/**
 * You can modify this file for your purpose
 */

{% if ($package != ''): %}
namespace {%:$this->convertPackage($package)%};
{% endif; %}

require_once __DIR__ . '/{%:$managerClassName . $parentSuffix%}.php';

{%$filename = $managerClassName; $override=false%}
class {%$managerClassName%} extends {%:$managerClassName . $parentSuffix%} 
{
    public function __construct()
    {
        parent::__construct();
        //your code
    }

    public function init($content, $format='')
    {
        if(parent::init($content, $format))
        {
            //your code
            return true;
        }

        return false;
    }
}