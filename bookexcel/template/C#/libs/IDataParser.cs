///
/// Copyright (c) 2016, bookrpg, All rights reserved.
/// @author llj <wwwllj1985@163.com>
/// @license The MIT License
///

using System.Collections;

namespace bookrpg.data
{
    public interface IDataParser : ICollection, IEnumerable
    {
        int currentRow { get; }

        void SetArrayDelemiter(char delimi, char innerDelimi);

        bool ParseString(string content);

        bool Has(string columnName);

        bool Has(int columnIndex);

        T GetValue<T>(string columnName);

        T GetValue<T>(int columnIndex);

        T[] GetList<T>(string columnName);

        T[] GetList<T>(int columnIndex);

        T[][] GetListGroup<T>(string columnName);

        T[][] GetListGroup<T>(int columnIndex);
    }
}
