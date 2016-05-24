///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;
using System.Collections.Generic;

namespace bookrpg.config
{
    /// <summary>
    /// Parse tab delimited string, much like csv
    /// </summary>
    public class TxtParser : IConfigParser, ICollection, IEnumerable, IEnumerator
    {
        private string[] title;
        private List<string[]> body;

        public char arrayDelimiter{ get; private set; }

        public char innerArrayDelimiter{ get; private set; }

        public int currentRow { get; private set; }

        public TxtParser()
        {
            arrayDelimiter = ';';
            innerArrayDelimiter = ':';
        }

        public bool ParseString(string content)
        {
            if (string.IsNullOrEmpty(content))
            {
                return false;
            }

            string[] arr = content.Split(new string[]{ "\r\n" }, StringSplitOptions.RemoveEmptyEntries);
            title = arr [0].Split(new char[]{ '\t' });

            body = new List<string[]>();
            for (int i = 1; i < arr.Length; i++)
            {
                string[] row = arr [i].Split(new char[]{ '\t' });
                body.Add(row);
            }
            body.TrimExcess();
            Reset();
            return true;
        }

        public void SetArrayDelemiter(char delimi, char innerDelimi)
        {
            arrayDelimiter = delimi;
            innerArrayDelimiter = innerDelimi;
        }

        public bool Has(string columnName)
        {
            return this.Has(Array.IndexOf(title, columnName));
        }

        public bool Has(int columnIndex)
        {
            var row = body [currentRow];
            return columnIndex >= 0 && columnIndex < row.Length;
        }

        public T GetValue<T>(string columnName)
        {
            return this.GetValue<T>(Array.IndexOf(title, columnName));
        }

        public T GetValue<T>(int columnIndex)
        {
            string str = this.GetColumnValue(columnIndex);
            Type t = typeof(T);
            try
            {
                if (t == typeof(bool)){
                    return (T)Convert.ChangeType(ConvertToBool(str), t);
                }
                return (T)Convert.ChangeType(str, t);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to {0} at Row({1}) and Column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        public string GetString(string columnName)
        {
            return GetColumnValue(Array.IndexOf(title, columnName));
        }

        public string GetString(int columnIndex)
        {
            return GetColumnValue(columnIndex);
        }

        public bool GetBool(string columnName)
        {
            return GetBool(Array.IndexOf(title, columnName));
        }

        public bool GetBool(int columnIndex)
        {
            try
            {
                return ConvertToBool(GetColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to bool at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        private bool ConvertToBool(string val)
        {
            if (val.Equals("0"))
            {
                return false;
            } else if (val.Equals("1"))
            {
                return true;
            } else
            {
                return Convert.ToBoolean(val);
            }
        }

        public int GetInt(string columnName)
        {
            return GetInt(Array.IndexOf(title, columnName));
        }

        public int GetInt(int columnIndex)
        {
            try
            {
                return Convert.ToInt32(GetColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to int32 at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public uint GetUInt(string columnName)
        {
            return GetUInt(Array.IndexOf(title, columnName));
        }

        public uint GetUInt(int columnIndex)
        {
            try
            {
                return Convert.ToUInt32(GetColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to uint32 at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public double GetDouble(string columnName)
        {
            return GetDouble(Array.IndexOf(title, columnName));
        }

        public double GetDouble(int columnIndex)
        {
            try
            {
                return Convert.ToDouble(GetColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to double at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public float GetFloat(string columnName)
        {
            return (float)GetDouble(columnName);
        }

        public float GetFloat(int columnIndex)
        {
            return (float)GetDouble(columnIndex);
        }

        public float GetLong(string columnName)
        {
            return GetLong(Array.IndexOf(title, columnName));
        }

        public float GetLong(int columnIndex)
        {
            try
            {
                return Convert.ToInt64(GetColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to long at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public string[] GetList(string columnName)
        {
            return GetList(Array.IndexOf(title, columnName));
        }

        public string[] GetList(int columnIndex)
        {
            return ParseUtil.GetList(GetColumnValue(columnIndex), arrayDelimiter);
        }

        public string[][] GetListGroup(string columnName)
        {
            return GetListGroup(Array.IndexOf(title, columnName));
        }

        public string[][] GetListGroup(int columnIndex)
        {
            return ParseUtil.GetListGroup(GetColumnValue(columnIndex), 
                arrayDelimiter, innerArrayDelimiter);
        }

        public T[] GetList<T>(string columnName)
        {
            return GetList<T>(Array.IndexOf(title, columnName));
        }

        public T[] GetList<T>(int columnIndex)
        {
            try
            {
                return ParseUtil.GetList<T>(GetColumnValue(columnIndex), arrayDelimiter);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to array at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public T[][] GetListGroup<T>(string columnName)
        {
            return GetListGroup<T>(Array.IndexOf(title, columnName));
        }

        public T[][] GetListGroup<T>(int columnIndex)
        {
            try
            {
                return ParseUtil.GetListGroup<T>(GetColumnValue(columnIndex), 
                    arrayDelimiter, innerArrayDelimiter);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to array at Row({0}) and Column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        private string GetColumnValue(int columnIndex)
        {
            var row = body [currentRow];
            if (columnIndex < 0 || columnIndex >= row.Length)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot read at Row({0}) and Column({1})", currentRow, columnIndex));
            }
            return row [columnIndex];
        }

        #region ICollection, IEnumerable, IEnumerator

        void ICollection.CopyTo (Array array, int index)
        {
            CopyTo((string[][])array, index);
        }

        public void CopyTo(string[][] array, int index)
        {
            body.CopyTo(array, index);
        }

        public int Count
        {
            get
            {
                return body.Count;
            }
        }

        public bool IsSynchronized
        {
            get
            {
                return false;
            }
        }

        public object SyncRoot
        {
            get
            {
                return this;
            }
        }

        IEnumerator IEnumerable.GetEnumerator()
        {
            return (IEnumerator)GetEnumerator();
        }

        public TxtParser GetEnumerator()
        {
            return this;
        }

        public bool MoveNext()
        {
            return ++currentRow < body.Count;
        }

        public void Reset()
        {
            currentRow = -1;
        }

        object IEnumerator.Current
        {
            get
            {
                return Current;
            }
        }

        public TxtParser Current
        {
            get
            {
                return this;
            }
        }

        #endregion
    }
}