///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;

namespace bookrpg.data
{
    public class DataException : Exception
    {
        public DataException(string message) : base(message)
        {
        }
        
        public DataException(string message, Exception innerException) : base(message, innerException)
        {
        }
        
    }
}
