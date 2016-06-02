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

namespace bookrpg.data
{
    public abstract class DataMgrSingleKey<TKey, TItem> : DataMgrBase<TItem>
        where TItem : DataItemBase, new()
    {
        protected SortedList<TKey, TItem> itemSortList = new SortedList<TKey, TItem>();

        public override bool Init(string text, string format=null)
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
                if (!item.ParseFrom(tp as IDataParser))
                {
                    Debug.LogErrorFormat("Failed to init:{0}, error at Row({1})", 
                        this.ToString(), i);
                    continue;
                }
                TKey key = (TKey)item.GetKey();
                if (itemSortList.ContainsKey(key))
                {
                    Debug.LogWarningFormat("init:{0}, multi Key({1}) at Row({2})", 
                        this.ToString(), key, i);
                    itemSortList [key] = item;
                } else
                {
                    itemSortList.Add(key, item);
                }
                itemList.Add(item);
                i++;
            }
            return true;
        }

        public virtual IDictionary<TKey, TItem> GetAllSortedItems()
        {
            return new SortedList<TKey, TItem>(itemSortList);
        }

        public virtual TItem GetItem(TKey key)
        {
            if (itemSortList.ContainsKey(key))
            {
                return itemSortList [key];
            }
            return default(TItem);
        }

        public virtual bool HasItem(TKey key)
        {
            return itemSortList.ContainsKey(key);
        }

        public virtual TItem this [TKey key]
        {
            get { return GetItem(key); }
        }

    }
}