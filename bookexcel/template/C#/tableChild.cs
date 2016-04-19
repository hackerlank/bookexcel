///
/// You can modify this file for your purpose 
///

using System;
using System.Collections;
using System.Collections.Generic;
using LitJson;
using bookrpg.config;
using UnityEngine;

{%$filename = $className; $override=false;%}
{% if ($package != ''): %}
namespace {%$package%} 
{
{% endif; %}
    public class {%$managerClassName%} : {%:$managerClassName.$parentSuffix%} 
    {
        public {%$managerClassName%}()
        {
            base();
            //your code
        }

        public override bool init(string text, string format=null)
        {
            if (base.init(text, format))
            {
                //your code
                return true;
            }

            return false;
        }
        
    }

    public class {%$className%} : {%:$className.$parentSuffix%} 
    {
        ///parse form {%$fileFormat%} 
        public override bool parseFrom(IConfigParser parser)
        {
            if (base.parseFrom(parser))
            {
                //your code
                return true;
            }

            return false;
        }
    }
{% if ($package != ''): %}
}
{% endif; %}