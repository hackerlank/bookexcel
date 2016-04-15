<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

class ExcelParser
{
    public $reader;
    public $sheets;
    public $sheetType;
    public $sheetName;
    public $tagRow;
    public $nameRow;
    public $typeRow;
    public $descRow;

    private $params;
    private $mergeColumns;

    const MAX_HEADER_ROW_COUNT = 10;

    public function __construct()
    {
    }

    public function parseExcel($filename, $params)
    {
        $this->params = $params;
        $exportTag = $params['exportTag'];
        $commentSymbol = $params['commentSymbol'];
        $onlySimpleName = $params['onlySimpleName'];
        $isMergeSheet = $params['mergeSheet'];

        $reader = new SpreadsheetReader($filename);
        $sheets = $reader->Sheets();
        $this->reader = $reader;
        $mergeSheets = array();

        foreach ($sheets as $index => $sheetName) {
            //跳过非字母、数字或下划线命名的sheet
            if ($onlySimpleName &&
                !preg_match('/^[a-zA-Z]\w*$/', $sheetName)) {
                continue;
            }

            //跳过注释sheet
            if (strpos($sheetName, $commentSymbol) === 0) {
                continue;
            }

            $mergeSheetName = $isMergeSheet ?
            $this->getSheetName($sheetName) : $sheetName;

            $mergeSheets[$mergeSheetName][] = array(
                'orgIndex' => $index,
                'orgSheetName' => $sheetName,
                'mergeSheetName' => $mergeSheetName,
            );
        }

        //如果只有一个sheet，则还原sheet名
        foreach ($mergeSheets as $key => $arr) {
            if (count($arr) == 1 &&
                $arr[0]['orgSheetName'] != $arr[0]['mergeSheetName']) {
                $mergeSheets[$key][0]['mergeSheetName'] =
                    $mergeSheets[$key][0]['orgSheetName'];
            }
        }

        $this->sheets = $mergeSheets;
    }

    public function changeSheet($index)
    {
        $commentSymbol = $this->params['commentSymbol'];
        $isMergeColumn = $this->params['mergeColumn'];
        $exportTag = $this->params['exportTag'];
        $isFirstRow = true;
        $count = 0;

        $orgNameRow = array();
        $this->tagRow = array();
        $this->nameRow = array();
        $this->typeRow = array();
        $this->descRow = array();
        $this->sheetName = $this->findSheetName($index);

        $reader = $this->reader;
        $reader->ChangeSheet($index);

        //找到header
        foreach ($reader as $key => $row) {
            //<2列，查找超过上限，
            if (count($row) < 2 ||
                ++$count >= self::MAX_HEADER_ROW_COUNT) {
                break;
            }

            //跳过空行
            if ($this->trimStrArray($row)) {
                continue;
            }

            //当行字段全部为空的时候，有时候从1开始，
            $firstField = isset($row[0]) ? $this->removeComment($row[0]) : '';

            //解析第一行:tag行
            if ($isFirstRow) {
                $isFirstRow = false;
                $this->sheetType = $firstField == '' ? SHEET_TYPE_TABLE : $firstField;

                foreach ($row as $k => $tag) {
                    //非注释、tag为空、要导出tag为空、tag和要导出tag相等
                    $this->tagRow[$k] = (strpos($tag, $commentSymbol) !== 0
                        && ($tag == '' || $exportTag == '' || $tag == $exportTag));
                }
                //强制不导出第一列:tag列
                $this->tagRow[0] = false;
                continue;
            }

            //直到找到name行和type行为止，注意：如果之前有数据行，则跳过
            if ($firstField == HEADER_ROW_NAME) {
                $orgNameRow = $row;
                $this->nameRow = $this->trimRow($row);
            } else if ($firstField == HEADER_ROW_TYPE) {
                $this->typeRow = $this->trimRow($row);
            } else if ($firstField == HEADER_ROW_DESC) {
                $this->descRow = $this->trimRow($row);
            }
        }

        if (!empty($this->nameRow) && !empty($this->typeRow)) {
            $this->removeEmptyNameColunms($orgNameRow);
            if ($isMergeColumn) {
                $this->mergeHeadColumn();
            }
        }

        if (empty($this->descRow)) {
            $this->descRow = $this->nameRow;
        }
    }

    /**
     * remove emtpy column,emtpy row, comment row,
     * retain the row with target tag field
     * @param  [type] &$row
     * @return [type]       empty field count
     */
    public function trimDataRow(&$row)
    {
        $rowCount = count($row);
        //跳过空行
        if ($this->trimStrArray($row)) {
            return $rowCount;
        }

        $commentSymbol = $this->params['commentSymbol'];
        $mergeColumn = $this->params['mergeColumn'];
        $exportTag = $this->params['exportTag'];
        $tag = $row[0];
        $emptyFieldCount = $rowCount;

        if (strpos($tag, $commentSymbol) !== 0 &&
            ($tag == '' || $exportTag == '' || $tag == $exportTag)) {
            $row = $this->trimRow($row, $emptyFieldCount);
            //跳过空行
            if ($emptyFieldCount == count($row)) {
                return $emptyFieldCount;
            }

            if ($mergeColumn) {
                $this->mergeDataColumn($row);
            }
        }

        return $emptyFieldCount;
    }

    public function createParams($dataRow = null)
    {
        return array(
            'sheetType' => $this->sheetType,
            'sheetName' => $this->sheetName,
            'convertParams' => $this->params,
            'nameRow' => $this->nameRow,
            'typeRow' => $this->typeRow,
            'dataRow' => $dataRow,
            'descRow' => $this->descRow,
        );
    }

    private function findSheetName($sheetIndex)
    {
        foreach ($this->sheets as $key => $val) {
            foreach ($val as $v) {
                if ($v['orgIndex'] == $sheetIndex) {
                    return $v['mergeSheetName'];
                }
            }
        }

        return '';
    }

    private function mergeHeadColumn()
    {
        $nameRow = &$this->nameRow;
        $typeRow = &$this->typeRow;
        $descRow = &$this->descRow;
        $mergeColumns = array();

        //合并名称相同的列
        for ($i = 0; $i < count($nameRow) - 1; $i++) {
            $v = $nameRow[$i];

            $start = $end = $i;
            for ($j = $i + 1; $j < count($nameRow); $j++) {
                if ($v != $nameRow[$j]) {
                    break;
                }
                $end = $j;
            }
            if ($end > $start) {
                $type = $typeRow[$start] . '[]';
                $mergeColumns[] = array($start, $end);
                array_splice($nameRow, $start + 1, $end - $start);
                $nameRow[$start] = $v;
                array_splice($typeRow, $start + 1, $end - $start);
                $typeRow[$start] = $type;
                array_splice($descRow, $start + 1, $end - $start);
                $descRow[$start] = $type;
            }
        }

        //合并列名形如a_1,a_2,a_3的列
        for ($i = 0; $i < count($nameRow) - 1; $i++) {

            $columnName = $this->getColumnName($nameRow[$i]);
            if ($columnName === false) {
                continue;
            }
            $start = $end = $i;
            for ($j = $i + 1; $j < count($nameRow); $j++) {
                if ($columnName != $this->getColumnName($nameRow[$j])) {
                    break;
                }
                $end = $j;
            }
            if ($end > $start) {
                $type = $typeRow[$start] . '[]';
                $mergeColumns[] = array($start, $end);
                array_splice($nameRow, $start + 1, $end - $start);
                $nameRow[$start] = $columnName;
                array_splice($typeRow, $start + 1, $end - $start);
                $typeRow[$start] = $type;
                array_splice($descRow, $start + 1, $end - $start);
                $descRow[$start] = $type;
            }
        }

        $this->mergeColumns = $mergeColumns;
    }

    private function mergeDataColumn(&$dataRow)
    {
        $delimter1 = $this->params['arrayDelimiter'];
        $delimter2 = $this->params['innerArrayDelimiter'];

        foreach ($this->mergeColumns as $v) {
            $start = $v[0];
            $end = $v[1];
            $arr = array_splice($dataRow, $start + 1, $end - $start);
            array_unshift($arr, $dataRow[$start]);
            foreach ($arr as $key => $value) {
                $arr[$key] = str_replace($delimter1, $delimter2, $value);
            }
            $dataRow[$start] = implode($delimter1, $arr);
        }

        return $dataRow;
    }

    private function getColumnName($v)
    {
        if (preg_match('/^(.+)_\d+$/', $v, $arr)) {
            return $arr[1];
        }
        return false;
    }

    private function getSheetName($v)
    {
        if (preg_match('/^(.+)_.+$/', $v, $arr)) {
            return $arr[1];
        }
        return $v;
    }

    private function trimStrArray(&$arr)
    {
        $isEmptyArr = true;
        foreach ($arr as $k => $v) {
            $arr[$k] = trim($v);
            if ($arr[$k] != '') {
                $isEmptyArr = false;
            }
        }
        return $isEmptyArr;
    }

    private function trimRow($row, &$emptyFieldCount = 0)
    {
        $emptyFieldCount = 0;
        $newRow = array();
        foreach ($row as $k => $v) {
            if ($this->tagRow[$k]) {
                $newRow[] = $v;
                if ($v == '') {
                    $emptyFieldCount++;
                }
            }
        }
        return $newRow;
    }

    private function removeEmptyNameColunms($orgNameRow)
    {
        foreach ($orgNameRow as $k => $v) {
            if ($v == '') {
                $this->tagRow[$k] = false;
                echo "warning: column $k has no name" . PHP_EOL;
            }
        }

        for ($i = 0; $i < count($this->nameRow); $i++) {
            if ($this->nameRow[$i] == '') {
                array_splice($this->nameRow, $i, 1);
                array_splice($this->typeRow, $i, 1);
                array_splice($this->descRow, $i, 1);
                $i--;
            }
        }
    }

    private function removeComment($str)
    {
        if ($str == '') {
            return $str;
        }

        if (strpos($str, $this->params['commentSymbol']) === 0) {
            $str = substr($str, strlen($this->params['commentSymbol']));
        }
        return $str;
    }

}
