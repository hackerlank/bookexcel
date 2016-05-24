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
        protected IConfigParser parser;

        public string resourceName;

        public virtual bool Init(string text, string format=null)
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

            var parser = GetParser(format);
            if (parser == null)
            {
                Debug.LogErrorFormat("Failed to init: {0}, no parser for format: {1}", this.ToString(), format);
                return false;
            }

            if (!parser.ParseString(text))
            {
                Debug.LogErrorFormat("Failed to init: {0}, cannot parse {1} text", this.ToString(), format);
                return false;
            }

            int i = 0;

            foreach(var tp in parser)
            {
                TItem item = new TItem();
                if (!item.ParseFrom(tp as IConfigParser))
                {
                    Debug.LogErrorFormat("Failed to init:{0}, error at Row({1})", 
                        this.ToString(), i);
                    continue;
                }
                itemList.Add(item);
                i++;
            }
            return true;
        }

        protected IConfigParser GetParser(string format)
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

        public void SetParser(IConfigParser parser)
        {
            this.parser = parser;
        }

        public virtual IList<TItem> GetAllItems()
        {
            return new List<TItem>(itemList);
        }
    }
}