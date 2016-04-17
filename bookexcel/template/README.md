## 对于模板文件的规定

例如PHP模板有4个文件:

* tableParent.php
* tableMgrParent.php
* tableChild.php
* tableMgrChild.php
* kvChild.php
* kvChild.php

`table`和`kv`表示excel的sheet类型，table是二维表，kv是键值对。

`Parent`和`Child`分别表示父类和子类，为什么要有父类和子类？因为每次重新生成代码，父类都会被重新覆盖，子类不会，用户可以任意修改子类。

也可以只保留父类模板，删除子类模板，比如C#模板，因为C#的partial机制很好的解决了上面的问题。
