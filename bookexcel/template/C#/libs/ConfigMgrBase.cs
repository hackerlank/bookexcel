///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using LitJson;
using bookrpg.log;

namespace bookrpg.config
{
    public abstract class ConfigMgrBase<TItem>
        where TItem : ConfigItemBase, new()
    {
        protected IList<TItem> itemList = new List<TItem>();
        protected IParser parser;

        public virtual bool init(string text, string format)
        {
            if (string.IsNullOrEmpty(text))
            {
                Debug.LogErrorFormat("Failed to init: {0}, text is null", this.ToString());
                return false;
            }

            if (itemList != null && itemList.Count > 0)
            {
                Debug.LogWarningFormat("Repeated init: {0}", this.ToString());
                itemList.Clear();
            }

            var parser = getParser(format);
            if (parser == null)
            {
                Debug.LogErrorFormat("Failed to init: {0}, no parser for format: {1}", this.ToString(), format);
                return false;
            }

            if (!parser.parseString(text))
            {
                Debug.LogErrorFormat("Failed to init: {0}, cannot parse {1} text", this.ToString(), format);
                return false;
            }

            int i = 0;

            foreach(var tp in parser)
            {
                TItem item = new TItem();
                if (!item.parseFrom(tp as IParser))
                {
                    Debug.LogErrorFormat("Failed to init:{0}, error at row({1})", 
                        this.ToString(), i);
                    continue;
                }
                itemList.Add(item);
                i++;
            }
            return true;
        }

        protected IParser getParser(string format)
        {
            switch(format){
                case "txt":
                    this.parser = new TxtParser();
                    break;
                case "json":
                    this.parser = new JsonParser();
                    break;
                default:
                    break;
            }

            return this.parser;
        }

        public void setParser(IParser parser)
        {
            this.parser = parser;
        }

        public virtual IList<TItem> getAllItems()
        {
            return new List<TItem>(itemList);
        }
    }
}