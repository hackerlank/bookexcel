///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System;
using System.Collections;
using System.Collections.Generic;
using LitJson;

namespace bookrpg.data
{
    /// <summary>
    /// Parse json string
    /// </summary>
    public class JsonParser : IDataParser, ICollection, IEnumerable, IEnumerator
    {
        public JsonData data;

        public char arrayDelimiter{ get; private set; }

        public char innerArrayDelimiter{ get; private set; }

        public int currentRow { get; private set; }

        public JsonParser()
        {
        }

        public bool ParseString(string content)
        {
            Reset();
            try
            {
                data = JsonMapper.ToObject(content);
                return true;
            } catch (Exception)
            {
//                throw new DataException("JsonParser: json format error", e);
                return false;
            }
        }

        public void SetArrayDelemiter(char delimi, char innerDelimi)
        {
            arrayDelimiter = delimi;
            innerArrayDelimiter = innerDelimi;
        }

        public bool Has(string columnName)
        {
            try
            {
                return data[currentRow][columnName] != null;
            } catch (Exception)
            {
                return false;
            }
        }

        public bool Has(int columnIndex)
        {
            var row = data [currentRow];
            return columnIndex >= 0 && columnIndex < row.Count;
        }

        public T GetValue<T>(string columnName)
        {
            Type t = typeof(T);
            try
            {
                return (T)Convert.ChangeType(data[currentRow][columnName].ToString(), t);
            } catch (Exception e)
            {
                throw new DataException(
                    string.Format("JsonParser: cannot convert to {0} at Row({1}) and Column({2})", 
                        typeof(T), currentRow, columnName), e);
            }
        }

        public T GetValue<T>(int columnIndex)
        {
            Type t = typeof(T);
            try
            {
                return (T)Convert.ChangeType(data[currentRow][columnIndex].ToString(), t);
            } catch (Exception e)
            {
                throw new DataException(
                    string.Format("JsonParser: cannot convert to {0} at Row({1}) and Column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        public string GetString(string columnName)
        {
            return (string)data[currentRow][columnName];
        }

        public string GetString(int columnIndex)
        {
            return (string)data[currentRow][columnIndex];
        }

        public bool GetBool(string columnName)
        {
            return (bool)data[currentRow][columnName];
        }

        public bool GetBool(int columnIndex)
        {
            return (bool)data[currentRow][columnIndex];
        }

        public int GetInt(string columnName)
        {
            return (int)data[currentRow][columnName];
        }

        public int GetInt(int columnIndex)
        {
            return (int)data[currentRow][columnIndex];
        }

        public uint GetUInt(string columnName)
        {
            return (uint)data[currentRow][columnName];
        }

        public uint GetUInt(int columnIndex)
        {
            return (uint)data[currentRow][columnIndex];
        }

        public double GetDouble(string columnName)
        {
            return (double)data[currentRow][columnName];
        }

        public double GetDouble(int columnIndex)
        {
            return (double)data[currentRow][columnIndex];
        }

        public float GetFloat(string columnName)
        {
            return (float)data[currentRow][columnName];
        }

        public float GetFloat(int columnIndex)
        {
            return (float)data[currentRow][columnIndex];
        }

        public float GetLong(string columnName)
        {
            return (long)data[currentRow][columnName];
        }

        public float GetLong(int columnIndex)
        {
            return (long)data[currentRow][columnIndex];
        }

        public T[] GetList<T>(string columnName)
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
                throw new DataException(
                    string.Format("JsonParser: cannot convert to {0} at Row({1}) and Column({2})", 
                        typeof(T), currentRow, columnName), e);
            }
        }

        public T[] GetList<T>(int columnIndex)
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
                throw new DataException(
                    string.Format("JsonParser: cannot convert to {0} at Row({1}) and Column({2})", 
                        typeof(T), currentRow, columnIndex), e);
            }
        }

        public T[][] GetListGroup<T>(string columnName)
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
                throw new DataException(
                    string.Format("JsonParser: cannot convert to {0} at Row({1}) and Column({2})", 
                        typeof(T), currentRow, columnName), e);
            }
        }

        public T[][] GetListGroup<T>(int columnIndex)
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
                throw new DataException(
                    string.Format("JsonParser: cannot convert to {0} at Row({1}) and Column({2})", 
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