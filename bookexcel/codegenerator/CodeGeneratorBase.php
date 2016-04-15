<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj wwwllj1985@163.com
 * @license The MIT License
 */

/**
 * CodeGeneratorBase
 */
class CodeGeneratorBase implements ICodeGenerator
{
    protected $params;
    protected $templateDir;
    protected $defaultParentClass = 'defaultParentClass';
    protected $defaultmanagerParentClass = 'defaultmanagerParentClass';

    private $prjFile;

    //{
    // "package" : "bookrpg.cfg",
    // "codeSuffix" : "Cfg",
    // "time": "",
    // "sheets": {
    //     "Sheet": {
    //         "package" : "bookrpg.cfg",
    //         "className": "Sheet",
    //         "classNameSuffix" : "Cfg",
    //         "parentClass": "bookrpg.config.ConfigItemBase" ,
    //         "managerParentClass": "bookrpg.config.ConfigMgrSingleKey",
    //         "fields": {
    //             "id": {"name": "ID", "isPrimaryKey": true, "createQuery": false},
    //             "name": {"name": "name", "isPrimaryKey": false, "createQuery": true},
    //             "color": {"name": "color", "isPrimaryKey": false, "createQuery": false},
    //         }
    //     }
    // }
    //}
    protected $prjInfo;

    public function generate(array $params)
    {

        $this->params = $params;
        // Util::restWarningError();

        $this->updateSheet();
        $this->createClass('class');
        $this->createClass('parentClass');
    }

    protected function createClass($tplName)
    {
        if (($tpl = $this->getTpl($tplName)) == '') {
            return;
        }

        $params = &$this->params;
        $convertParams = &$params['convertParams'];
        $prjInfo = &$this->prjInfo;
        $sheetName = $params['sheetName'];
        $sheet = $prjInfo['sheets'][$sheetName];
        $fileFormat = $convertParams['exportFormat'];
        $package = $sheet['package'];
        $className = ucfirst($sheet['className']) . $sheet['classNameSuffix'];
        $parentClass = $sheet['parentClass'];
        $managerParentClass = $sheet['managerParentClass'];
        $fields = $sheet['fields'];
        $keyTypes = $this->convertTypeAndGetPKey($fields);
        $TItem = $className;
        $TKey1 = count($keyTypes) > 0 ? $keyTypes[0] : '';
        $TKey2 = count($keyTypes) > 1 ? $keyTypes[1] : '';

        ob_start();
        include TplEngine::compileFile($tpl);
        $str = ob_get_contents();
        ob_clean();

        $fileSavePath = $this->getFileSavePath($className, $package, $tpl);
        Util::saveToFile($fileSavePath, $str);
    }

    protected function convertTypeAndGetPKey(&$fields)
    {
        $i = 0;
        $ret = array();
        foreach ($fields as &$field) {
            $i++;
            $field['type'] = $this->convertType($field['type']);
            if ($field['isPrimaryKey'] && ($i == 1 || $i == 2))
            {
                $ret[] = $field['type'];
            }
        }

        return $ret;
    }

    protected function convertType($type)
    {
        return $type;
    }

    protected function getFileSavePath($name, $package, $tpl)
    {
        $params = &$this->params['convertParams'];

        $fileSavePath = $params['codeSavePath'] == '' ?
        Util::getDir($params['inputPath']) : $params['codeSavePath'];

        if ($params['genPackageDir']) {
            $fileSavePath = Util::addDirSeparator($fileSavePath) .
            str_replace('.', '/', $package);
        }

        return Util::addDirSeparator($fileSavePath) . 
        $name . '.' . Util::getExtension($tpl);
    }

    protected function createSheet()
    {
        $params = &$this->params;
        $convertParams = &$params['convertParams'];

        $sheet = array(
            'package' => $convertParams['package'],
            'className' => $params['sheetName'],
            'classNameSuffix' => $convertParams['codeSuffix'],
            'parentClass' => $this->defaultParentClass,
            'managerParentClass' => $this->defaultmanagerParentClass,
        );

        $fields = array();
        for ($i = 0; $i < count($params['nameRow']); $i++) {
            $fields[$params['nameRow'][$i]] = array(
                'name' => $params['nameRow'][$i],
                'type' => $params['typeRow'][$i],
                'desc' => @$params['descRow'][$i],
                'isPrimaryKey' => $i == 0,
                'createQuery' => false,
            );
        }

        $sheet['fields'] = $fields;

        return $sheet;
    }

    protected function updateSheet()
    {
        $sheetName = $this->params['sheetName'];
        $convertParams = &$this->params['convertParams'];
        $prjInfo = &$this->prjInfo;
        $newSheet = $this->createSheet();

        if ($prjInfo) {
            if (isset($prjInfo['sheets'][$sheetName])) {
                $sheet = &$prjInfo['sheets'][$sheetName];

                if ($sheet['package'] != $prjInfo['package']) {
                    $newSheet['package'] = $sheet['package'];
                }

                if ($sheet['classNameSuffix'] != $prjInfo['codeSuffix']) {
                    $newSheet['classNameSuffix'] = $sheet['classNameSuffix'];
                }

                $fields = $sheet['fields'];
                $newFields = &$newSheet['fields'];
                foreach ($fields as $key => $value) {
                    if (isset($newFields[$key])) {
                        $value['type'] = $newFields[$key]['type'];
                        $newFields[$key] = $value;
                    } 
                }
                $sheet = $newSheet;

            } else {
                $prjInfo['sheets'][$sheetName] = $newSheet;
            }
        } else {
            $prjInfo = array(
                "package" => $convertParams['package'],
                "codeSuffix" => $convertParams['codeSuffix'],
                "time" => date("Y-m-d H:i:s"),
                "sheets" => array($sheetName => $newSheet),
            );
        }
    }

    public function start()
    {
        $prjFile = $this->getPrjFile();

        if (file_exists($prjFile)) {
            try {
                $content = file_get_contents($prjFile);
                $this->prjInfo = json_decode($content, true);
            } catch (Exception $e) {
                Util::warning('cannot parse file: ' . $prjFile);
            }
        }
    }

    public function end()
    {
        if ($this->prjInfo && $this->params) {
            $convertParams = &$this->params['convertParams'];
            $this->prjInfo["package"] = $convertParams['package'];
            $this->prjInfo["codeSuffix"] = $convertParams['codeSuffix'];
            $this->prjInfo["time"] = date("Y-m-d H:i:s");

            foreach ($this->prjInfo['sheets'] as &$sheet) {
                foreach ($sheet as &$field) {
                    unset($field['type']);
                    unset($field['desc']);
                }
            }
            Util::saveToFile($this->prjFile, $this->prjInfo);
        }
    }

    protected function getPrjFile()
    {
        $codeType = $this->params['codeType'];
        $input = $this->params['inputPath'];
        $input = rtrim(str_replace('\\', '/', $input), '/');
        if (is_file($input)) {
            $input = basename($input);
        } else {
            if (!is_dir($input)) {
                $input = dirname($input);
            }
            $input = substr($input, strrpos($input, '/') + 1);
        }

        $this->prjFile = HISTORY_DIR . $input . '_' . $codeType . 'json';

        return $this->prjFile;
    }

    protected function getTpl($tplName)
    {
        $tplDir = $this->templateDir;
        $arr = glob(Util::addDirSeparator($tplDir) . $tplName . '.*');
        if ($arr === false || count($arr) == 0) {
            Util::error("can not find template: $tplName in $tplDir");
            return '';
        }
        return $arr[0];
    }
}
