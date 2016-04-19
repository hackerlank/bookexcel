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
    public abstract class ConfigMgrDoubleKey<TKey1, TKey2, TItem>
            where TItem : ConfigItemBase, new()
    {
        protected SortedList<TKey1, SortedList<TKey2, TItem>> itemSortList = 
            new SortedList<TKey1, SortedList<TKey2, TItem>>();
        
        protected IList<TItem> itemList = new List<TItem>();

        protected IConfigParser parser;

        public virtual bool init(string text, string format = null)
        {
            if (string.IsNullOrEmpty(text))
            {
                Debug.LogErrorFormat("Failed to init: {0}, text is null", this.ToString());
                return false;
            }

            if (itemList != null && itemList.Count > 0)
            {
                Debug.LogWarningFormat("Repeated init: {0}", this.ToString());
                itemSortList.Clear();
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

            foreach (var tp in parser)
            {
                TItem item = new TItem();
                if (!item.parseFrom(tp as IConfigParser))
                {
                    Debug.LogErrorFormat("Failed to init:{0}, error at row({1})", 
                        this.ToString(), i);
                    continue;
                }

                TKey1 key = (TKey1)item.getKey();
                TKey2 key2 = (TKey2)item.getSecondKey();

                if (getItemGroup(key) == null)
                {
                    itemSortList.Add(key, new SortedList<TKey2, TItem>());
                    itemSortList[key].Add(key2, item);
                } else if (itemSortList[key].ContainsKey(key2))
                {
                    Debug.LogWarningFormat("init:{0}, multi key1:({1}) key2(2) at row({3})", 
                        this.ToString(), key, key2, i);
                    itemSortList[key][key2] = item;
                } else
                {
                    itemSortList[key].Add(key2, item);
                }

                itemList.Add(item);
                i++;
            }
            return true;
        }

        protected IConfigParser getParser(string format)
        {
            switch (format)
            {
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

        public void setParser(IConfigParser parser)
        {
            this.parser = parser;
        }

        public virtual IList<TItem> getAllItems()
        {
            return new List<TItem>(itemList);
        }

        public virtual IDictionary<TKey1, SortedList<TKey2, TItem>> getAllSortedItems()
        {
            return new SortedList<TKey1, SortedList<TKey2, TItem>>(itemSortList);
        }

        public virtual TItem getItem(TKey1 key1, TKey2 key2)
        {
            if (itemSortList.ContainsKey(key1) && itemSortList[key1].ContainsKey(key2))
            {
                return itemSortList[key1][key2];
            }
            return default(TItem);
        }

        public virtual IDictionary<TKey2, TItem> getItemGroup(TKey1 key1)
        {
            if (itemSortList.ContainsKey(key1))
            {
                return itemSortList[key1];
            }
            return null;
        }

        public virtual bool hasItem(TKey1 key1, TKey2 key2)
        {
            return itemSortList.ContainsKey(key1) && itemSortList[key1].ContainsKey(key2);
        }

        public virtual bool hasItemGroup(TKey1 key1)
        {
            return itemSortList.ContainsKey(key1);
        }

        public virtual IDictionary<TKey2, TItem> this [TKey1 key1]
        {
            get { return getItemGroup(key1); }
        }

        public virtual TItem this [TKey1 key1, TKey2 key2]
        {
            get { return getItem(key1, key2); }
        }
    }
}