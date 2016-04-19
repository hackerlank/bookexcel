<?php
require 'bookexcel/Bookexcel.php';

date_default_timezone_set('PRC');

$params = array(
    //常用参数
    "inputPath" => "", //留空则转换当前目录下的所有excel，支持通配符
    "outputPath" => "excelExports", //默认在原目录下新建此文件夹
    "exportFormat" => "json", //导出格式：txt、csv、xml、json
    "exportTag" => "", //c(该行/列为服务端特有)、s(该行/列为客户端特有)，留空则全部导出，可以自定义

    //生成解析代码的参数
    "codeType" => "", //生成解析代码类型：php、c#，留空不生成解析代码
    "codeSavePath" => "", //解析代码存放路径
    "package" => "", //解析代码所在的包
    "codeSuffix" => "", //解析代码的后缀
    "genPackageDir" => true, //根据包名创建目录

    //不常用参数
    "excludes" => array(), //排除列表,支持通配符，如array('dir/no.xlsx', 'dir/tmp*.xlsx')
    "extension" => "", //留空则采用exportFormat为扩展名
    "outputEncode" => "utf-8", //建议utf-8，如果想用mac的excel打开当导出的txt、csv选gbk
    "commentSymbol" => "#", //注释符，如果excel文件名、sheet名、行和列的开头有注释符，忽略导出
    "onlySimpleName" => true, //只导出字母、数字或下划线命名的sheet
    "mergeColumn" => true, //合并名称相同或者形如a_1，a_2，a_3的列为数组
    "mergeSheet" => true, //合并名称前缀相同的sheet，如human_man，human_woman，human_girl
    "endOfLine" => "\r\n", //换行符:win(\r\n)，linux(\n)，mac(\r)
    "arrayDelimiter" => ";", //数组分隔符，例如12;34;6
    "innerArrayDelimiter" => ":", //内层数组分隔符，例如1002:2;1003:3;1008:6
    "emptyRowWarnCount" => 10, //空行太多，发出警告
    "emptyFieldWarnCount" => 4, //一行的空字段太多，发出警告
);

$params['inputPath'] = 'test';
$params['outputPath'] = '';
$params['exportFormat'] = 'json';
$params['codeType'] = 'c#';
$params['codeSuffix'] = 'Cfg';
$params['codeSavePath'] = '';
$bk = new Bookexcel();
$bk->convertExcels($params);

//
//add more worker
//
// $params['inputPath'] = 'xxx';
// $params['outputPath'] = 'xxx';
// $bk->convertExcels($params);
