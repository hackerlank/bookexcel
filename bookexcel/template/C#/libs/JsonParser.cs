///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;
using System.Collections.Generic;
using LitJson;

namespace bookrpg.config
{
    /// <summary>
    /// Parse json string
    /// </summary>
    public class JsonParser : IConfigParser, ICollection, IEnumerable, IEnumerator
    {
        public JsonData data;

        public char arrayDelimiter{ get; private set; }

        public char innerArrayDelimiter{ get; private set; }

        public int currentRow { get; private set; }

        public JsonParser()
        {
        }

        public bool parseString(string content)
        {
            Reset();
            try
            {
                data = JsonMapper.ToObject(content);
                return true;
            } catch (Exception)
            {
//                throw new ConfigException("JsonParser: json format error", e);
                return false;
            }
        }

        public void setArrayDelemiter(char delimi, char innerDelimi)
        {
            arrayDelimiter = delimi;
            innerArrayDelimiter = innerDelimi;
        }

        public bool has(string columnName)
        {
            try
            {
                return data[currentRow][columnName] != null;
            } catch (Exception)
            {
                return false;
            }
        }

        public bool has(int columnIndex)
        {
            var row = data [currentRow];
            return columnIndex >= 0 && columnIndex < row.Count;
        }

        public T getValue<T>(string columnName)
        {
            Type t = typeof(T);
            try
            {
                return (T)Convert.ChangeType(data[currentRow][columnName].ToString(), t);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("JsonParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnName), e);
            }
        }

        public T getValue<T>(int columnIndex)
        {
            Type t = typeof(T);
            try
            {
                return (T)Convert.ChangeType(data[currentRow][columnIndex].ToString(), t);
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("JsonParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        public string getString(string columnName)
        {
            return (string)data[currentRow][columnName];
        }

        public string getString(int columnIndex)
        {
            return (string)data[currentRow][columnIndex];
        }

        public bool getBool(string columnName)
        {
            return (bool)data[currentRow][columnName];
        }

        public bool getBool(int columnIndex)
        {
            return (bool)data[currentRow][columnIndex];
        }

        public int getInt(string columnName)
        {
            return (int)data[currentRow][columnName];
        }

        public int getInt(int columnIndex)
        {
            return (int)data[currentRow][columnIndex];
        }

        public uint getUInt(string columnName)
        {
            return (uint)data[currentRow][columnName];
        }

        public uint getUInt(int columnIndex)
        {
            return (uint)data[currentRow][columnIndex];
        }

        public double getDouble(string columnName)
        {
            return (double)data[currentRow][columnName];
        }

        public double getDouble(int columnIndex)
        {
            return (double)data[currentRow][columnIndex];
        }

        public float getFloat(string columnName)
        {
            return (float)data[currentRow][columnName];
        }

        public float getFloat(int columnIndex)
        {
            return (float)data[currentRow][columnIndex];
        }

        public float getLong(string columnName)
        {
            return (long)data[currentRow][columnName];
        }

        public float getLong(int columnIndex)
        {
            return (long)data[currentRow][columnIndex];
        }

        public T[] getList<T>(string columnName)
        {
            try
            {
                var arr = data[currentRow][columnName];
                var tarr = new T[arr.Count];

                for (int i = 0; i < arr.Count; i++)
                {
                    tarr [i] = (T)Convert.ChangeType(arr [i].ToString(), typeof(T));
                }

                return tarr;
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("JsonParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnName), e);
            }
        }

        public T[] getList<T>(int columnIndex)
        {
            try
            {
                var arr = data[currentRow][columnIndex];
                var tarr = new T[arr.Count];

                for (int i = 0; i < arr.Count; i++)
                {
                    tarr [i] = (T)Convert.ChangeType(arr [i].ToString(), typeof(T));
                }

                return tarr;
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("JsonParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        public T[][] getListGroup<T>(string columnName)
        {
            try
            {
                var arr = data[currentRow][columnName];
                var tarr = new T[arr.Count][];

                for (int i = 0; i < arr.Count; i++)
                {
                    var arr2 = arr [i];
                    tarr [i] = new T[arr2.Count];

                    for (int j = 0; j < arr2.Count; j++)
                    {           
                        tarr [i] [j] = (T)Convert.ChangeType(arr2[j].ToString(), typeof(T));
                    }
                }

                return tarr;
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("JsonParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnName), e);
            }
        }

        public T[][] getListGroup<T>(int columnIndex)
        {
            try
            {
                var arr = data[currentRow][columnIndex];
                var tarr = new T[arr.Count][];

                for (int i = 0; i < arr.Count; i++)
                {
                    var arr2 = arr [i];
                    tarr [i] = new T[arr2.Count];

                    for (int j = 0; j < arr2.Count; j++)
                    {           
                        tarr [i] [j] = (T)Convert.ChangeType(arr2[j].ToString(), typeof(T));
                    }
                }

                return tarr;
            } catch (Exception e)
            {
                throw new ConfigException(
                    string.Format("JsonParser: cannot convert to {0} at row({1}) and column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        #region ICollection, IEnumerable, IEnumerator

        void ICollection.CopyTo(Array array, int index)
        {
            CopyTo((string[][])array, index);
        }

        public void CopyTo(string[][] array, int index)
        {
            //body.CopyTo(array, index);
            throw new NotImplementedException();
        }

        public int Count
        {
            get
            {
                return data.Count;
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

        public JsonParser GetEnumerator()
        {
            return this;
        }

        public bool MoveNext()
        {
            return ++currentRow < data.Count;
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

        public JsonParser Current
        {
            get
            {
                return this;
            }
        }

        #endregion
    }
}