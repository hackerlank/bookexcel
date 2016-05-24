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
    public abstract class ConfigMgrDoubleKey<TKey1, TKey2, TItem>  : ConfigMgrBase<TItem>
            where TItem : ConfigItemBase, new()
    {
        protected SortedList<TKey1, SortedList<TKey2, TItem>> itemSortList = 
            new SortedList<TKey1, SortedList<TKey2, TItem>>();

        public override bool Init(string text, string format = null)
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

            foreach (var tp in parser)
            {
                TItem item = new TItem();
                if (!item.ParseFrom(tp as IConfigParser))
                {
                    Debug.LogErrorFormat("Failed to init:{0}, error at Row({1})", 
                        this.ToString(), i);
                    continue;
                }

                TKey1 key = (TKey1)item.GetKey();
                TKey2 key2 = (TKey2)item.GetSecondKey();

                if (GetItemGroup(key) == null)
                {
                    itemSortList.Add(key, new SortedList<TKey2, TItem>());
                    itemSortList[key].Add(key2, item);
                } else if (itemSortList[key].ContainsKey(key2))
                {
                    Debug.LogWarningFormat("init:{0}, multi key1:({1}) Key2(2) at Row({3})", 
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

        public virtual IDictionary<TKey1, SortedList<TKey2, TItem>> GetAllSortedItems()
        {
            return new SortedList<TKey1, SortedList<TKey2, TItem>>(itemSortList);
        }

        public virtual TItem GetItem(TKey1 key1, TKey2 key2)
        {
            if (itemSortList.ContainsKey(key1) && itemSortList[key1].ContainsKey(key2))
            {
                return itemSortList[key1][key2];
            }
            return default(TItem);
        }

        public virtual IDictionary<TKey2, TItem> GetItemGroup(TKey1 key1)
        {
            if (itemSortList.ContainsKey(key1))
            {
                return itemSortList[key1];
            }
            return null;
        }

        public virtual bool HasItem(TKey1 key1, TKey2 key2)
        {
            return itemSortList.ContainsKey(key1) && itemSortList[key1].ContainsKey(key2);
        }

        public virtual bool HasItemGroup(TKey1 key1)
        {
            return itemSortList.ContainsKey(key1);
        }

        public virtual IDictionary<TKey2, TItem> this [TKey1 key1]
        {
            get { return GetItemGroup(key1); }
        }

        public virtual TItem this [TKey1 key1, TKey2 key2]
        {
            get { return GetItem(key1, key2); }
        }
    }
}