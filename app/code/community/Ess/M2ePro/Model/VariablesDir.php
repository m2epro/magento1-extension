<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_VariablesDir
{
    const BASE_NAME = 'M2ePro';

    private $_childFolder = NULL;
    private $_pathVariablesDirBase = NULL;
    private $_pathVariablesDirChildFolder = NULL;

    //########################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        !isset($params['child_folder']) && $params['child_folder'] = NULL;
        $params['child_folder'] === '' && $params['child_folder'] = NULL;
        $this->_pathVariablesDirBase = Mage::getBaseDir('var').DS.self::BASE_NAME;

        if (!is_null($params['child_folder'])) {

            if ($params['child_folder']{0} != DS) {
                $params['child_folder'] = DS.$params['child_folder'];
            }
            if ($params['child_folder']{strlen($params['child_folder'])-1} != DS) {
                $params['child_folder'] .= DS;
            }

            $this->_pathVariablesDirChildFolder = $this->_pathVariablesDirBase.$params['child_folder'];
            $this->_pathVariablesDirBase .= DS;
            $this->_childFolder = $params['child_folder'];

        } else {

            $this->_pathVariablesDirBase .= DS;
            $this->_pathVariablesDirChildFolder = $this->_pathVariablesDirBase;
            $this->_childFolder = '';
        }

        $this->_pathVariablesDirBase = str_replace(array('/','\\'),DS,$this->_pathVariablesDirBase);
        $this->_pathVariablesDirChildFolder = str_replace(array('/','\\'),DS,$this->_pathVariablesDirChildFolder);
        $this->_childFolder = str_replace(array('/','\\'),DS,$this->_childFolder);
    }

    //########################################

    public function getBasePath()
    {
        return $this->_pathVariablesDirBase;
    }

    public function getPath()
    {
        return $this->_pathVariablesDirChildFolder;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isBaseExist()
    {
        return @is_dir($this->getBasePath());
    }

    /**
     * @return bool
     */
    public function isExist()
    {
        return @is_dir($this->getPath());
    }

    // ---------------------------------------

    public function createBase()
    {
        if ($this->isBaseExist()) {
            return;
        }

        if (!@mkdir($this->getBasePath(), 0777, true)) {
            throw new Ess_M2ePro_Model_Exception('M2ePro base var dir creation is failed.');
        }
    }

    public function create()
    {
        if ($this->isExist()) {
            return;
        }

        $this->createBase();

        if ($this->_childFolder != '') {

            $tempPath = $this->getBasePath();
            $tempChildFolders = explode(DS,substr($this->_childFolder,1,strlen($this->_childFolder)-2));

            foreach ($tempChildFolders as $key=>$value) {
                if (!is_dir($tempPath.$value.DS)) {
                    if (!@mkdir($tempPath.$value.DS, 0777, true)) {
                        throw new Ess_M2ePro_Model_Exception('Custom var dir creation is failed.');
                    }
                }
                $tempPath = $tempPath.$value.DS;
            }
        } else {
            if (!@mkdir($this->getPath(), 0777, true)) {
                throw new Ess_M2ePro_Model_Exception('Custom var dir creation is failed.');
            }
        }
    }

    // ---------------------------------------

    public function removeBase()
    {
        if (!$this->isBaseExist()) {
            return;
        }

        if (!@rmdir($this->getBasePath())) {
            throw new Ess_M2ePro_Model_Exception('M2ePro base var dir removing is failed.');
        }
    }

    public function removeBaseForce()
    {
        if (!$this->isBaseExist()) {
            return;
        }

        $directoryIterator = new RecursiveDirectoryIterator($this->getBasePath(), FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }

        if (!@rmdir($this->getBasePath())) {
            throw new Ess_M2ePro_Model_Exception('M2ePro base var dir removing is failed.');
        }
    }

    public function remove()
    {
        if (!$this->isExist()) {
            return;
        }

        if (!@rmdir($this->getPath())) {
            throw new Ess_M2ePro_Model_Exception('Custom var dir removing is failed.');
        }
    }

    //########################################
}