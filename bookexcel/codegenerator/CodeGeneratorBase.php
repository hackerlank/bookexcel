<?php
/**
 * Copyright (c) 2016, bookrpg, All rights reserved.
 * @author llj <wwwllj1985@163.com>
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
    protected $parentClassSuffix = 'CE';

    /**{
     * "package" : "bookrpg.cfg",
     * "codeSuffix" : "Cfg",
     * "time": "",
     * "sheets": {
     * "Sheet": {
     * "package" : "bookrpg.cfg",
     * "codeSuffix" : "Cfg",
     * "className": "Sheet",
     * "parentClass": "bookrpg.config.ConfigItemBase" ,
     * "managerParentClass": "bookrpg.config.ConfigMgrSingleKey",
     * "fields": {
     * "id": {"name": "ID", "isPrimaryKey": true, "createQuery": false},
     * "name": {"name": "name", "isPrimaryKey": false, "createQuery": true},
     * "color": {"name": "color", "isPrimaryKey": false, "createQuery": false},
     * }
     * }
     * }
     * }*/
    protected $prjInfo;

    private $prjFile;

    public function generate(array $params)
    {
        $this->params = $params;
        $this->updateSheet();

        $sheetType = $params['sheetType'];

        $tplDir = $this->templateDir;
        $tpls = glob(Util::addDirSeparator($tplDir) . $sheetType . '*');
        if (empty($tpls)) {
            Util::warning("can not find template for sheetType: $sheetType in $tplDir");
        }

        array_walk($tpls, array($this, 'createClass'));
    }

    protected function createClass($tpl)
    {
        $prjInfo = &$this->prjInfo;
        $params = &$this->params;
        $convertParams = &$params['convertParams'];
        $sheetName = $params['sheetName'];
        $sheet = $prjInfo['sheets'][$sheetName];

        //inject vars into tpl
        $fileFormat = $convertParams['exportFormat'];
        $arrayDelimiter = $convertParams['arrayDelimiter'];
        $innerArrayDelimiter = $convertParams['innerArrayDelimiter'];
        $package = $sheet['package'];
        $nameRow = $params['nameRow'];
        $dataRow = $params['dataRow'];
        $parentSuffix = $this->parentClassSuffix;
        $className = ucfirst($sheet['className']) . $sheet['codeSuffix'];
        $parentClassName = $sheet['parentClass'];
        $managerClassName = $className . 'Mgr';
        $managerParentClass = $sheet['managerParentClass'];
        $fields = $sheet['fields'];
        $keyTypes = $this->convertTypeAndGetPKey($fields);
        $TItem = $className;
        $TKey1 = count($keyTypes) > 0 ? $keyTypes[0] : '';
        $TKey2 = count($keyTypes) > 1 ? $keyTypes[1] : '';

        //tpl may modify these vars for itself
        $filename = $className;
        $override = true;

        ob_start();
        include TplEngine::compileFile($tpl);
        $str = ob_get_contents();
        ob_clean();

        $fileSavePath = $this->getFileSavePath($filename, $package, $tpl);
        if ($override || !file_exists($fileSavePath)) {
            Util::saveToFile($fileSavePath, $str);
        }
    }

    protected function convertTypeAndGetPKey(&$fields)
    {
        $i = 0;
        $ret = array();
        foreach ($fields as &$field) {
            $i++;
            $field['type'] = $this->convertType($field['type']);
            if ($field['isPrimaryKey'] && ($i == 1 || $i == 2)) {
                $ret[] = $field['type'];
            }
        }

        return $ret;
    }

    public function convertType($type)
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
            'codeSuffix' => $convertParams['codeSuffix'],
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

                if ($sheet['package'] == $prjInfo['package']) {
                    $sheet['package'] = $newSheet['package'];
                }

                if ($sheet['codeSuffix'] == $prjInfo['codeSuffix']) {
                    $sheet['codeSuffix'] = $newSheet['codeSuffix'];
                }

                $fields = &$sheet['fields'];
                $newFields = &$newSheet['fields'];
                foreach ($newFields as $key => $value) {
                    if (isset($fields[$key])) {
                        $fields[$key]['type'] = $value['type'];
                        $fields[$key]['desc'] = $value['desc'];
                        $newFields[$key] = $fields[$key];
                    }
                }
                $fields = $newFields;

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

    public function start(array $params)
    {
        $this->params = $params;
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

    public function end(array $params)
    {
        if ($this->prjInfo && $params) {
            $convertParams = &$params['convertParams'];
            $this->prjInfo["package"] = $convertParams['package'];
            $this->prjInfo["codeSuffix"] = $convertParams['codeSuffix'];
            $this->prjInfo["time"] = date("Y-m-d H:i:s");

            foreach ($this->prjInfo['sheets'] as &$sheet) {
                foreach ($sheet['fields'] as &$field) {
                    unset($field['type']);
                    unset($field['desc']);
                }
            }
            Util::saveToFile(
                $this->prjFile,
                json_encode($this->prjInfo,
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES)
            );
        }
    }

    protected function getPrjFile()
    {
        $convertParams = &$this->params['convertParams'];
        $codeType = $convertParams['codeType'];
        $input = $convertParams['inputPath'];
        $input = rtrim(str_replace('\\', '/', $input), '/');
        if (is_file($input)) {
            $input = basename($input);
        } else {
            if (!is_dir($input)) {
                $input = dirname($input);
            }
            if (($n = strrpos($input, '/')) !== false) {
                $input = substr($input, $n + 1);
            }
        }

        $this->prjFile = HISTORY_DIR . $input . '_' . $codeType . '.json';

        return $this->prjFile;
    }

    protected function getTpl($tplName)
    {
        $tplDir = $this->templateDir;
        $arr = glob(Util::addDirSeparator($tplDir) . $tplName . '.*');
        if (empty($arr)) {
            Util::error("can not find template: $tplName in $tplDir");
            return '';
        }
        return $arr[0];
    }

    protected function insertTpl($filename)
    {
        if (($tpl = $this->getTpl($filename)) == '') {
            return '';
        }

        return TplEngine::compileFile($tpl);
    }
}
