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

    /**
     * create two class for one sheet, you cannot modify parent class, because
     * it is updated by CodeGenerator, but you can modify child class.
     */
    protected $createTwoClass = true;
    protected $twoClassSuffix = 'PT';

    private $prjFile;

    /**{
     * "package" : "bookrpg.cfg",
     * "codeSuffix" : "Cfg",
     * "time": "",
     * "sheets": {
     * "Sheet": {
     * "package" : "bookrpg.cfg",
     * "className": "Sheet",
     * "classNameSuffix" : "Cfg",
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

    public function generate(array $params)
    {
        $this->params = $params;
        $this->updateSheet();

        $sheetType = $params['sheetType'];

        if ($this->createTwoClass) {
            $this->createClass($sheetType . 'Parent', true);
            $this->createClass($sheetType . 'Child');
            $this->createClass($sheetType . 'MgrParent', true, true);
            $this->createClass($sheetType . 'MgrChild', false, true);
        } else {
            $this->createClass($sheetType . 'Parent');
            $this->createClass($sheetType . 'MgrParent', false, true);
        }
    }

    protected function createClass($tplName, $isParent = false, $isMgr = false)
    {
        if (($tpl = $this->getTpl($tplName)) == '') {
            return;
        }

        $twoClassSuffix = $isParent ? $this->twoClassSuffix : '';

        $params = &$this->params;
        $convertParams = &$params['convertParams'];
        $prjInfo = &$this->prjInfo;
        $sheetName = $params['sheetName'];
        $sheet = $prjInfo['sheets'][$sheetName];
        $fileFormat = $convertParams['exportFormat'];
        $package = $sheet['package'];

        $baseClassName = ucfirst($sheet['className']) . $sheet['classNameSuffix'];
        $className = $baseClassName . $twoClassSuffix;
        $parentClassName = $sheet['parentClass'];
        $managerClassName = $baseClassName . 'Mgr' . $twoClassSuffix;
        $managerParentClass = $sheet['managerParentClass'];
        $fields = $sheet['fields'];
        $keyTypes = $this->convertTypeAndGetPKey($fields);
        $TItem = $baseClassName;
        $TKey1 = count($keyTypes) > 0 ? $keyTypes[0] : '';
        $TKey2 = count($keyTypes) > 1 ? $keyTypes[1] : '';

        ob_start();
        include TplEngine::compileFile($tpl);
        $str = ob_get_contents();
        ob_clean();

        $fileSavePath = $this->getFileSavePath($isMgr ? $managerClassName : $className, $package, $tpl);
        Util::saveToFile($fileSavePath, $str);
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

                if ($sheet['package'] == $prjInfo['package']) {
                    $sheet['package'] = $newSheet['package'];
                }

                if ($sheet['classNameSuffix'] == $prjInfo['codeSuffix']) {
                    $sheet['classNameSuffix'] = $newSheet['classNameSuffix'];
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
        if ($arr === false || count($arr) == 0) {
            Util::warning("can not find template: $tplName in $tplDir");
            return '';
        }
        return $arr[0];
    }
}
