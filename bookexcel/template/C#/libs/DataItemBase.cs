///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;

namespace bookrpg.data
{
    public abstract class DataItemBase
    {
        protected object _key1;
        protected object _key2;

        public DataItemBase()
        {
        }

        public virtual bool ParseFrom(IDataParser parser)
        {
            throw new NotImplementedException();
        }

        public virtual object GetKey()
        {
            return _key1;
        }

        public virtual object GetSecondKey()
        {
            return _key2;
        }
    }
}