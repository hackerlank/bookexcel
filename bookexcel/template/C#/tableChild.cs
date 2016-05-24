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
        private static {%$managerClassName%} instance;

        public static {%$managerClassName%} IT
        {
            get
            {
                if(instance == null){
                    instance = new {%$managerClassName%}();
                }
                return instance;
            }
        }

        public {%$managerClassName%}() : base()
        {
            //your code
        }

        public override bool Init(string text, string format=null)
        {
            if (base.Init(text, format))
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
        public override bool ParseFrom(IConfigParser parser)
        {
            if (base.ParseFrom(parser))
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