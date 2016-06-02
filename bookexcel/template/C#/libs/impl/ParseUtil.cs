///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;
using System.Collections.Generic;

namespace bookrpg.data
{
    public class ParseUtil
    {
        public static string[] GetList(string content, char delimi = ';')
        {
            if (string.IsNullOrEmpty(content))
            {
                return new string[0];
            }
            return content.Split(new char[]{ delimi });
        }

        public static string[][] GetListGroup(string content, char delimi = ';', char innerDelimi = ':')
        {
            if (string.IsNullOrEmpty(content))
            {
                return new string[0][];
            }

            var arr = content.Split(new char[]{ delimi });
            var rtv = new string[arr.Length][];

            for (int i = 0; i < arr.Length; i++)
            {
                rtv[i] = arr[i].Split(new char[]{innerDelimi});
            }

            return rtv;
        }

        public static T[] GetList<T>(string content, char delimi = ';')
        {
            var arr = content.Split(new char[]{ delimi });
            var tarr = new T[arr.Length];

            for (int i = 0; i < arr.Length; i++)
            {
                tarr[i] = (T)Convert.ChangeType(arr[i], typeof(T));
            }

            return tarr;
        }

        public static T[][] GetListGroup<T>(string content, char delimi = ';', char innerDelimi = ':')
        {
            if (string.IsNullOrEmpty(content))
            {
                return new T[0][];
            }

            var arr = content.Split(new char[]{ delimi });
            var tarr = new T[arr.Length][];

            for (int i = 0; i < arr.Length; i++)
            {
                var arr2 = arr[i].Split(new char[]{innerDelimi});
                tarr[i] = new T[arr2.Length];

                for (int j = 0; j < arr2.Length; j++)
                {
                    tarr[i][j] = (T)Convert.ChangeType(arr2[j], typeof(T));
                }
            }

            return tarr;
        }

    }
}
