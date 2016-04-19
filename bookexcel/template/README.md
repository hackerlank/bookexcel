## 对于模板文件的约定

例如PHP模板有如下文件:

* tableParent.php
* tableChild.php
* tableMgrParent.php
* tableMgrChild.php
* kvParent.php
* kvChild.php
* kvMgrParent.php
* kvMgrChild.php

`table`和`kv`表示excel的sheet类型，table是二维表，kv是键值对。

`Parent`和`Child`分别表示父类和子类，为什么要有父类和子类？因为每次重新生成代码，父类都会被重新覆盖，子类不会，用户可以任意修改子类。

`Mgr`表示管理类，主要负责查询数据。

模板的命名规则就是sheetType+xxx，xxx可以任意指定，不必严格按照上面的描述，因为模板内部通过如下语句来指定文件名：

	{%$filename = 'this is class name'; $override=false%}

`$override=false`表示此类第一次自动生成，供用户编辑修改，重新导出时不会覆盖。

另外，用户也可以根据自己的需要增删模板数量，参见C#模板。
