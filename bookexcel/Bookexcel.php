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
require_once APP_ROOT . 'Util.php';
require_once APP_ROOT . 'TplEngine.php';
require_once APP_ROOT . 'converter/ConverterBase.php';
require_once APP_ROOT . 'codegenerator/CodeGeneratorBase.php';

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
            'C#' => new CSharpCodeGenerator(),
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
     * @param  array|null   see defaultParams.php
     * @return void
     */
    public function convertExcels(array $params = null)
    {
        Util::restWarningError();

        $params = $this->mergeParams($params);
        $path = Util::getAbsolutePath($params['inputPath']);
        $this->sheetNames = array();

        if ($params['outputPath'] != '') {
            $this->fileSavePath = Util::getAbsolutePath(
                $params['outputPath'],
                is_dir($path) ? $path : dirname($path)
            );
        }

        if (empty($this->converter[$params['exportFormat']])) {
            Util::error('not support exportFormat: ' . $params['exportFormat']);
            echo PHP_EOL;
            return;
        }

        echo '---------------------------------' . PHP_EOL;
        echo 'start parse excel at: ' . $path . PHP_EOL;
        echo PHP_EOL;

        if (!($files = $this->findExcelFiles($path))) {
            return;
        }
        $excelCount = count($files);

        foreach ($files as $filename) {
            $basename = basename($filename);
            echo '--------read file:' . $filename . PHP_EOL;

            try {
                $this->parseExcel($filename);
            } catch (Exception $e) {
                Util::error($e->getMessage());
            }
        }

        echo PHP_EOL;
        echo 'finish parse excel at: ' . $path . PHP_EOL;
        echo 'total file count: ' . $excelCount . PHP_EOL;
        echo 'error count: ' . Util::$errorCount . PHP_EOL;
        echo 'warning count: ' . Util::$warningCount . PHP_EOL;
        echo '---------------------------------' . PHP_EOL;
    }

    public function generateCode(array $params = null)
    {
        Util::restWarningError();

        $params = $this->mergeParams($params);
        $path = Util::getAbsolutePath($params['inputPath']);

        if (!$this->getCodeGenerator()) {
            Util::warning('not support codeType: ' . $params['codeType']);
        }

        echo '---------------------------------' . PHP_EOL;
        echo 'start generate code at: ' . $path . PHP_EOL;
        echo PHP_EOL;

        if (!($files = $this->findExcelFiles($path))) {
            return;
        }
        $excelCount = count($files);

        foreach ($files as $filename) {
            $basename = basename($filename);
            echo '--------read file:' . $filename . PHP_EOL;

            try {
                $this->parseExcel($filename, true);
            } catch (Exception $e) {
                Util::error($e->getMessage());
            }
        }

        echo PHP_EOL;
        echo 'finish generate code at: ' . $path . PHP_EOL;
        echo 'total file count: ' . $excelCount . PHP_EOL;
        echo 'error count: ' . Util::$errorCount . PHP_EOL;
        echo 'warning count: ' . Util::$warningCount . PHP_EOL;
        echo '---------------------------------' . PHP_EOL;

    }

    private function parseExcel($filename, $onlyGenCode = false)
    {
        $basename = basename($filename);
        $params = $this->params;
        $emptyRowWarnCount = $params['emptyRowWarnCount'];
        $emptyFieldWarnCount = $params['emptyFieldWarnCount'];
        $extension = $params['extension'] == '' ?
            $params['exportFormat'] : $params['extension'];

        $converter = $onlyGenCode ? false : $this->converter[$params['exportFormat']];
        $codeGenerator = $this->getCodeGenerator();

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
            $rows = array();

            foreach ($mergeSheets as $mergeSheet) {
                $orgSheetName = $mergeSheet['orgSheetName'];
                $mergeSheetName = $mergeSheet['mergeSheetName'];

                echo 'parse sheet: ' . $basename . '->' . $orgSheetName . PHP_EOL;

                $fileSavePath = $this->fileSavePath == '' ?
                    dirname($filename) : $this->fileSavePath;
                $fileSavePath = Util::addDirSeparator($fileSavePath) .
                    $mergeSheetName . '.' . $extension;

                $emptyRowCount = 0;
                $isFirstRow = true;
                $isLastRowEmpty = true;

                $excel->changeSheet($mergeSheet['orgIndex']);

                if (empty($excel->nameRow) || empty($excel->typeRow)) {
                    Util::warning('there is no name row or type row');
                    continue;
                }

                if ($isFirstSheet) {
                    $isFirstSheet = false;

                    if ($converter) {
                        $header = $converter->convertHeader($excel->createParams());
                        $this->writeToBuff($fileSavePath, $header);
                    }

                    if ($codeGenerator) {
                        $codeGenerator->start($excel->createParams());
                    }

                } else {
                    if (!empty($lastNameRow) && $lastNameRow != $excel->nameRow) {
                        Util::error('nameRow not equal lastNameRow');
                        continue;
                    }

                    if (!empty($lastTypeRow) && $lastTypeRow != $excel->typeRow) {
                        Util::error('typeRow not equal lastTypeRow');
                        continue;
                    }
                }

                $lastNameRow = $excel->nameRow;
                $lastTypeRow = $excel->typeRow;

                if (!$converter && $excel->sheetType != SHEET_TYPE_KV) {
                    continue;
                }

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
                        Util::warning('too many empty field at row: ' . $key);
                    }

                    if ($excel->sheetType == SHEET_TYPE_KV) {
                        $rows[] = $row;
                    }

                    if ($converter){
                        $result = $converter->convertItem($excel->createParams($row));
                        $this->writeToBuff($fileSavePath, $result);
                    }
                }

                //最后一行不为空，中间有太多空行，用于提醒用户检查脏数据
                if (!$isLastRowEmpty && $emptyRowCount >= $emptyRowWarnCount) {
                    Util::warning('too many empty row, total: ' . $emptyRowCount);
                }
            }

            if (!empty($lastNameRow) && !empty($lastTypeRow)) {
                if ($converter) {
                    $footer = $converter->convertFooter($excel->createParams());
                    $this->writeToBuff($fileSavePath, $footer);
                    $this->flush();

                    if (isset($this->sheetNames[$mergeSheetName])) {
                        Util::warning(sprintf(
                            "sheet name repeated at: %s->%s and %s->%s",
                            $basename,
                            $mergeSheetName,
                            $this->sheetNames[$mergeSheetName],
                            $mergeSheetName));
                    }

                    $this->sheetNames[$mergeSheetName] = $basename;
                }

                //生成解析代码
                if ($codeGenerator && !empty($excel->nameRow)) {
                    if ($excel->sheetType == SHEET_TYPE_KV) {
                        $codeGenerator->generate($excel->createParams($rows));
                    } else {
                        $codeGenerator->generate($excel->createParams());
                    }
                }
            }

            if ($codeGenerator) {
                $codeGenerator->end($excel->createParams());
            }
        }
    }

    private function mergeParams($params = null) {
        $defaultParams = require 'defaultParams.php';

        if ($params == null) {
            $params = $defaultParams;
        } else {
            foreach ($defaultParams as $k => $v) {
                if (!isset($params[$k])) {
                    $params[$k] = $v;
                }
            }
        }

        $this->params = $params;
        return $params;
    }

    private function findExcelFiles($path)
    {
        global $params;
        $excludes = $params['excludes'];

        try {
            if (is_dir($path)) {
                $dir = Util::addDirSeparator($path);
                $needCheckExt = true;
                $files = scandir($path);
            } else {
                $dir = '';
                $needCheckExt = !preg_match('/.+\.^\/+$/', $path);
                $files = glob($path);
            }

            if ($files === false) {
                Util::error('can not find files at: ' . $path);
                echo PHP_EOL;
                return false;
            }

            $arr = array();

            foreach ($files as $filename) {
                $filename = $dir . $filename;

                if (is_file($filename) &&
                    !$this->isIgnoreFile($filename) &&
                    !Util::isExclude($filename, $excludes) &&
                    (($needCheckExt && $this->isExcelExt($filename)) || !$needCheckExt)
                ) {
                    $arr[] = $filename;
                }
            }

            return $arr;

        } catch (Exception $e) {
            Util::error($e->getMessage());
            return false;
        }

    }

    private function getCodeGenerator()
    {
        $codeType = $this->params['codeType'];
        if ($codeType != '' && isset($this->codeGenerator[$codeType])) {
            return $this->codeGenerator[$codeType];
        }

        return null;
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


    //XLSX XLS ODS
    private function isExcelExt($filename)
    {
        return preg_match('/\.(xlsx|xls|ods)$/i', $filename);
    }

    private function isIgnoreFile($filename)
    {
        $filename = basename($filename);

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
}
