///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;

namespace bookrpg.config
{
    public class ConfigException : Exception
    {
        public ConfigException(string message) : base(message)
        {
        }
        
        public ConfigException(string message, Exception innerException) : base(message, innerException)
        {
        }
        
    }
}
