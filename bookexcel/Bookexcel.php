<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

define('APP_ROOT', __DIR__ . '/');

require_once APP_ROOT . 'vendor/autoload.php';
require_once APP_ROOT . 'ExcelParser.php';
require_once APP_ROOT . 'define.php';
require_once APP_ROOT . 'IConverter.php';
require_once APP_ROOT . 'ICodeGenerator.php';

autoload(APP_ROOT . 'converter');
autoload(APP_ROOT . 'codegenerator');

function autoload($dir)
{
    foreach (glob($dir . '/*.php') as $file) {
        include_once $file;
    }
}

/**
 *
 */
class Bookexcel
{
    private $errorCount;
    private $warningCount;

    private $params;
    private $sheetNames;

    private $fileSavePath = '';
    private $buffFileName = '';
    private $buffFileContent = '';

    const WRITE_BUFF_SIZE = 10240;

    /*
     * ['json' => new IConverter ...]
     */
    private $converter;
    /*
     * ['c#' => new ICodeGenerator ...]
     */
    private $codeGenerator;

    public function __construct()
    {
        $this->converter = array(
            'json' => new JSONConverter(),
            'xml' => new XMLConverter(),
            'csv' => new CSVConverter(),
            'txt' => new TXTConverter(),
        );

        $this->codeGenerator = array(
            'php' => new PHPCodeGenerator(),
        );
    }

    public function addConverter($formart, IConverter $converter)
    {
        $this->converter[$formart] = $converter;
    }

    public function addCodeGenerator($codeType, ICodeGenerator $codeGenerator)
    {
        $this->codeGenerator[$codeType] = $codeGenerator;
    }

    /**
     * batch export excels to json,xml,csv,txt ... and generate program code
     * @param  array|null $params see defaultParams.php
     * @return [type]             void
     */
    public function parseExcels(array $params = null)
    {
        $excelCount = 0;
        $this->errorCount = 0;
        $this->warningCount = 0;
        $this->sheetNames = array();

        $defaultParams = require 'defaultParams.php';
        if (is_null($params)) {
            $params = $defaultParams;
        } else {
            foreach ($defaultParams as $k => $v) {
                if (!isset($params[$k])) {
                    $params[$k] = $v;
                }
            }
        }

        $this->params = $params;
        $path = $this->getAbsolutePath($params['inputPath']);
        $excludes = $params['excludes'];

        if ($params['outputPath'] != '') {
            $this->fileSavePath = $this->getAbsolutePath(
                $params['outputPath'],
                is_dir($path) ? $path : dirname($path)
            );
        }

        if (empty($this->converter[$params['exportFormat']])) {
            $this->error('not support exportFormat: ' . $params['exportFormat']);
            echo PHP_EOL;
            return;
        }

        if ($params['codeType'] != '' &&
            empty($this->codeGenerator[$params['codeType']])) {
            $this->warning('not support codeType: ' . $params['codeType']);
        }

        echo '---------------------------------' . PHP_EOL;
        echo 'start parse excel at: ' . $path . PHP_EOL;
        echo PHP_EOL;

        try {
            if (is_dir($path)) {
                $dir = $this->addDirDelimiter($path);
                $needCheckExt = true;
                $files = scandir($path);
            } else {
                $dir = '';
                $needCheckExt = !preg_match('/.+\.^\/+$/', $path);
                $files = glob($path);
            }

            if ($files === false) {
                $this->error('can not find files at: ' . $path);
                echo PHP_EOL;
                return;
            }

            foreach ($files as $filename) {
                $basename = basename($filename);
                $filename = $dir . $filename;

                if (is_file($filename) &&
                    !$this->isIgnoreFile($basename) &&
                    !$this->isExclude($filename, $excludes) &&
                    (($needCheckExt && $this->isExcelExt($basename)) || !$needCheckExt)) {

                    $excelCount++;
                    echo '--------read file:' . $filename . PHP_EOL;

                    try {
                        $this->parseExcel($filename);
                    } catch (Exception $e) {
                        $this->error($e->getMessage());
                    }
                }
            }

        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        echo PHP_EOL;
        echo 'finish parse excel at: ' . $path . PHP_EOL;
        echo 'total file count: ' . $excelCount . PHP_EOL;
        echo 'error count: ' . $this->errorCount . PHP_EOL;
        echo 'warning count: ' . $this->warningCount . PHP_EOL;
        echo '---------------------------------' . PHP_EOL;
    }

    private function parseExcel($filename)
    {
        $basename = basename($filename);
        $params = $this->params;
        $exportTag = $params['exportTag'];
        $commentSymbol = $params['commentSymbol'];
        $emptyRowWarnCount = $params['emptyRowWarnCount'];
        $emptyFieldWarnCount = $params['emptyFieldWarnCount'];
        $codeType = $params['codeType'];
        $extension = $params['extension'] == '' ?
        $params['exportFormat'] : $params['extension'];
        $converter = $this->converter[$params['exportFormat']];

        $excel = new ExcelParser();
        $excel->parseExcel($filename, $params);
        $reader = $excel->reader;
        $sheets = $excel->sheets;

        foreach ($sheets as $mergeSheets) {

            $lastNameRow = null;
            $lastTypeRow = null;
            $orgSheetName = '';
            $mergeSheetName = '';
            $isFirstSheet = true;

            foreach ($mergeSheets as $mergeSheet) {
                $orgSheetName = $mergeSheet['orgSheetName'];
                $mergeSheetName = $mergeSheet['mergeSheetName'];

                echo 'parse sheet: ' . $basename . '->' . $orgSheetName . PHP_EOL;

                $fileSavePath = $this->fileSavePath == '' ?
                dirname($filename) : $this->fileSavePath;
                $fileSavePath = $this->addDirDelimiter($fileSavePath) .
                    $mergeSheetName . '.' . $extension;

                $emptyRowCount = 0;
                $isFirstRow = true;
                $isLastRowEmpty = true;

                $excel->changeSheet($mergeSheet['orgIndex']);

                if (empty($excel->nameRow) || empty($excel->typeRow)) {
                    $this->warning('there is no name row or type row');
                    continue;
                }

                if ($isFirstSheet) {
                    $isFirstSheet = false;
                    $header = $converter->convertHeader($excel->createParams());
                    $this->writeToBuff($fileSavePath, $header);
                } else {
                    if (!empty($lastNameRow) && $lastNameRow != $excel->nameRow) {
                        $this->error('nameRow not equal lastNameRow');
                        continue;
                    }

                    if (!empty($lastTypeRow) && $lastTypeRow != $excel->typeRow) {
                        $this->error('typeRow not equal lastTypeRow');
                        continue;
                    }
                }

                $lastNameRow = $excel->nameRow;
                $lastTypeRow = $excel->typeRow;

                foreach ($reader as $key => $row) {

                    if ($isFirstRow) {
                        $isFirstRow = false;
                        continue;
                    }

                    $emptyFieldCount = $excel->trimDataRow($row);
                    //跳过空行
                    if ($emptyFieldCount == count($row)) {
                        $emptyRowCount++;
                        $isLastRowEmpty = true;
                        continue;
                    }
                    $isLastRowEmpty = false;

                    if ($emptyFieldCount >= $emptyFieldWarnCount) {
                        $this->warning('too many empty field at row: ' . $key);
                    }

                    $result = $converter->convertItem($excel->createParams($row));
                    $this->writeToBuff($fileSavePath, $result);
                }

                //最后一行不为空，中间有太多空行，用于提醒用户检查脏数据
                if (!$isLastRowEmpty && $emptyRowCount >= $emptyRowWarnCount) {
                    $this->warning('too many empty row, total: ' . $emptyRowCount);
                }
            }

            if (!empty($lastNameRow) && !empty($lastTypeRow)) {
                $footer = $converter->convertFooter($excel->createParams());
                $this->writeToBuff($fileSavePath, $footer);
                $this->flush();

                if (isset($this->sheetNames[$mergeSheetName])) {
                    $this->warning(sprintf(
                        "sheet name repeated at: %s->%s and %s->%s",
                        $basename,
                        $mergeSheetName,
                        $this->sheetNames[$mergeSheetName],
                        $mergeSheetName));
                }

                $this->sheetNames[$mergeSheetName] = $basename;

                //生成解析代码
                if ($codeType != '' && !empty($this->nameRow) &&
                    !empty($this->codeGenerator[$codeType])) {
                    $codeGenerator = $this->codeGenerator[$codeType];
                    $codeGenerator->generate($excel->createParams());
                }
            }
        }
    }

    private function writeToBuff($filename, $content)
    {
        if ($filename != $this->buffFileName) {
            $this->buffFileName = $filename;
            $this->buffFileContent = $content;

            if (!file_exists($filename)) {
                $dir = dirname($filename);
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
            } else {
                unlink($filename);
            }
        } else {
            $this->buffFileContent .= $content;
        }

        if (strlen($this->buffFileContent) > self::WRITE_BUFF_SIZE) {
            $this->writeToFile($this->buffFileName, $this->buffFileContent);
            $this->buffFileContent = '';
        }
    }

    private function flush()
    {
        if ($this->buffFileName == '' || $this->buffFileContent == '') {
            return;
        }
        $this->writeToFile($this->buffFileName, $this->buffFileContent);
    }

    private function writeToFile($filename, $content)
    {
        $chaset = $this->params['outputEncode'];
        $buff = ($chaset != '' && $chaset != 'utf-8') ?
        iconv('utf-8', $chaset, $this->buffFileContent) : $this->buffFileContent;
        $file = fopen($filename, 'a');
        if ($success = ($file !== false)) {
            $success = (fwrite($file, $buff) !== false);
            $success = ($success ? fclose($file) : $success);
        }

        if (!$success) {
            throw new Exception("can't write to file: " . $filename);
        }

        return $success;
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

    private function isExclude($filename, $excludes)
    {
        foreach ($excludes as $pattern) {
            if (fnmatch($this->getAbsolutePath($pattern), $filename)) {
                return true;
            }
        }

        return false;
    }

    private function unifyDirDelimiter($path)
    {
        if (empty($path)) {
            return $path;
        }
        return str_replace("\\", "/", $path);
    }

    private function addDirDelimiter($path)
    {
        if (empty($path)) {
            return $path;
        }
        if (!preg_match('/\/$/', $path)) {
            $path .= '/';
        }
        return $path;
    }

    private function getAbsolutePath($path, $relativePath = null)
    {
        if (strpos($path, '/') !== 0 && !preg_match('/^[a-zA-Z]:/', $path)) {
            //当前pwd或本文件的父目录作为相对目录的起点
            if ($relativePath == null && ($relativePath = getcwd()) === false) {
                $relativePath = dirname(__DIR__);
            }

            $path = $relativePath . '/' . $path;
        }

        $path = str_replace("\\", "/", $path);
        $path = str_replace("//", "/", $path);

        if (strpos($path, './') !== false) {

            $arr = explode('/', $path);
            for ($i = 0; $i < count($arr); $i++) {
                if ($arr[$i] == '..') {
                    if ($i > 1) {
                        array_splice($arr, $i - 1, 2);
                        $i -= 2;
                    } else {
                        array_splice($arr, $i, 1);
                        $i--;
                    }
                } else if ($arr[$i] == '.') {
                    array_splice($arr, $i, 1);
                    $i--;
                }
            }
            $path = implode($arr, '/');
        }

        return $path;
    }

    //XLSX XLS ODS
    private function isExcelExt($filename)
    {
        return preg_match('/\.(xlsx|xls|ods)$/i', $filename);
    }

    private function isIgnoreFile($filename)
    {
        //#中文.xls 非字母、数字、下划线命名的文件
        // if($this->params['onlySimpleName'] && !preg_match('/^[a-zA-Z]\w*\.?\w*$/', $filename)){
        //     return true;
        // }

        //~$xxx.xls 临时文件
        if (preg_match('/^[~$.]/', $filename)) {
            return true;
        }

        //#xxx.xls 文件被注释
        $commentSymbol = $this->params['commentSymbol'];
        if ($commentSymbol != '' && strpos($filename, $commentSymbol) === 0) {
            return true;
        }
    }

    private function warning($msg)
    {
        $this->warningCount++;
        echo 'warning: ' . $msg . PHP_EOL;
    }

    private function error($msg)
    {
        $this->errorCount++;
        echo 'error: ' . $msg . PHP_EOL;
    }
}
