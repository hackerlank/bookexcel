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

        public bool parseString(string content)
        {
            if (string.IsNullOrEmpty(content))
            {
                return false;
            }

            string[] arr = content.Split(new char[]{ '\n', '\r' }, StringSplitOptions.RemoveEmptyEntries);
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

        public void setArrayDelemiter(char delimi, char innerDelimi)
        {
            arrayDelimiter = delimi;
            innerArrayDelimiter = innerDelimi;
        }

        public bool has(string columnName)
        {
            return this.has(Array.IndexOf(title, columnName));
        }

        public bool has(int columnIndex)
        {
            var row = body [currentRow];
            return columnIndex >= 0 && columnIndex < row.Length;
        }

        public T getValue<T>(string columnName)
        {
            return this.getValue<T>(Array.IndexOf(title, columnName));
        }

        public T getValue<T>(int columnIndex)
        {
            string str = this.getColumnValue(columnIndex);
            Type t = typeof(T);
            try
            {
                if (t == typeof(bool)){
                    return (T)Convert.ChangeType(convertToBool(str), t);
                }
                return (T)Convert.ChangeType(str, t);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        public string getString(string columnName)
        {
            return getColumnValue(Array.IndexOf(title, columnName));
        }

        public string getString(int columnIndex)
        {
            return getColumnValue(columnIndex);
        }

        public bool getBool(string columnName)
        {
            return getBool(Array.IndexOf(title, columnName));
        }

        public bool getBool(int columnIndex)
        {
            try
            {
                return convertToBool(getColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to bool at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        private bool convertToBool(string val)
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

        public int getInt(string columnName)
        {
            return getInt(Array.IndexOf(title, columnName));
        }

        public int getInt(int columnIndex)
        {
            try
            {
                return Convert.ToInt32(getColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to int32 at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public uint getUInt(string columnName)
        {
            return getUInt(Array.IndexOf(title, columnName));
        }

        public uint getUInt(int columnIndex)
        {
            try
            {
                return Convert.ToUInt32(getColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to uint32 at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public double getDouble(string columnName)
        {
            return getDouble(Array.IndexOf(title, columnName));
        }

        public double getDouble(int columnIndex)
        {
            try
            {
                return Convert.ToDouble(getColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to double at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public float getFloat(string columnName)
        {
            return (float)getDouble(columnName);
        }

        public float getFloat(int columnIndex)
        {
            return (float)getDouble(columnIndex);
        }

        public float getLong(string columnName)
        {
            return getLong(Array.IndexOf(title, columnName));
        }

        public float getLong(int columnIndex)
        {
            try
            {
                return Convert.ToInt64(getColumnValue(columnIndex));
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to long at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public string[] getList(string columnName)
        {
            return getList(Array.IndexOf(title, columnName));
        }

        public string[] getList(int columnIndex)
        {
            return ParseUtil.getList(getColumnValue(columnIndex), arrayDelimiter);
        }

        public string[][] getListGroup(string columnName)
        {
            return getListGroup(Array.IndexOf(title, columnName));
        }

        public string[][] getListGroup(int columnIndex)
        {
            return ParseUtil.getListGroup(getColumnValue(columnIndex), 
                arrayDelimiter, innerArrayDelimiter);
        }

        public T[] getList<T>(string columnName)
        {
            return getList<T>(Array.IndexOf(title, columnName));
        }

        public T[] getList<T>(int columnIndex)
        {
            try
            {
                return ParseUtil.getList<T>(getColumnValue(columnIndex), arrayDelimiter);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to array at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        public T[][] getListGroup<T>(string columnName)
        {
            return getListGroup<T>(Array.IndexOf(title, columnName));
        }

        public T[][] getListGroup<T>(int columnIndex)
        {
            try
            {
                return ParseUtil.getListGroup<T>(getColumnValue(columnIndex), 
                    arrayDelimiter, innerArrayDelimiter);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot convert to array at row({0}) and column({1})", 
                        currentRow, columnIndex), e);
            }
        }

        private string getColumnValue(int columnIndex)
        {
            var row = body [currentRow];
            if (columnIndex < 0 || columnIndex >= row.Length)
            {
                throw new ConfigException(
                    string.Format("TxtParser: cannot read at row({0}) and column({1})", currentRow, columnIndex));
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