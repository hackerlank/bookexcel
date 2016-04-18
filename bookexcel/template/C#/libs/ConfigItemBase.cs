///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;

namespace bookrpg.config
{
    public abstract class ConfigItemBase
    {
        protected object _key1;
        protected object _key2;

        public ConfigItemBase()
        {
        }

        public virtual bool parseFrom(IConfigParser parser)
        {
            throw new NotImplementedException();
        }

        public virtual object getKey()
        {
            return _key1;
        }

        public virtual object getSecondKey()
        {
            return _key2;
        }
    }
}