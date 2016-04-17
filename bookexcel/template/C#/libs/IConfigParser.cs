///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System.Collections;

namespace bookrpg.config
{
    public interface IParser : ICollection, IEnumerable
    {
        int currentRow { get; }

        void setArrayDelemiter(char delimi, char innerDelimi);

        bool parseString(string content);

        T getValue<T>(string columnName);

        T getValue<T>(int columnIndex);

        T[] getList<T>(string columnName);

        T[] getList<T>(int columnIndex);

        T[][] getListGroup<T>(string columnName);

        T[][] getListGroup<T>(int columnIndex);
    }
}
